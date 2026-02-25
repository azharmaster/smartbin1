<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;
use App\Models\CapacitySetting;
use Illuminate\Support\Facades\Mail;
use App\Mail\SummaryReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use QuickChart\Quickchart;

class SummaryController extends Controller
{
private function getCapacityStats(Carbon $baseDate, string $period)
{
    $assets = Asset::with([
        'capacitySetting',
        'devices.latestSensor' // ❗ NO whereBetween here
    ])->get();

    $empty = $half = $full = 0;

    foreach ($assets as $asset) {

        $setting = $asset->capacitySetting;
        if (!$setting) continue;

        foreach ($asset->devices as $device) {

            $sensor = $device->latestSensor;
            if (!$sensor || !is_numeric($sensor->capacity)) continue;

            if ($sensor->capacity <= $setting->empty_to) {
                $empty++;
            } elseif ($sensor->capacity <= $setting->half_to) {
                $half++;
            } else {
                $full++;
            }
        }
    }

    return (object) [
        'empty_count' => $empty,
        'half_count'  => $half,
        'full_count'  => $full,
    ];
}

    private function getDevicesByFloor()
    {
        return DB::table('assets')
            ->join('floor', 'assets.floor_id', '=', 'floor.id')
            ->join('devices', 'devices.asset_id', '=', 'assets.id')
            ->select('floor.floor_name', DB::raw('COUNT(devices.id_device) as total'))
            ->groupBy('floor.floor_name')
            ->get();
    }

    private function computeBinAnalyticsPerAsset(Carbon $baseDate, string $period)
    {
        [$start, $end] = $this->resolveDateRange($baseDate, $period);

        $assets = Asset::with([
            'capacitySetting',
            'devices' => function ($q) {
                $q->orderBy('id_device');
            }
        ])->get();

        $sensorData = DB::table('sensors')
            ->whereBetween('time', [$start, $end])
            ->orderBy('time')
            ->get()
            ->groupBy('device_id');

        $results = [];

        foreach ($assets as $asset) {

            $setting = $asset->capacitySetting;

            if (!$setting) {
                $results[] = (object) [
                    'asset_name'     => $asset->asset_name,
                    'times_full'     => 0,
                    'avg_fill_time'  => 0,
                    'avg_clear_time' => 0,
                ];
                continue;
            }

            $timesFull = 0;
            $fillDurations = [];
            $clearDurations = [];

            foreach ($asset->devices as $device) {

                $deviceSensors = $sensorData->get($device->id_device, collect());

                $prevCapacity = null;
                $lastFullAt = null;
                $lastEmptyAt = null;

                foreach ($deviceSensors as $sensor) {

                    if (!is_numeric($sensor->capacity)) continue;

                    $capacity = (float) $sensor->capacity;
                    $time = Carbon::parse($sensor->time);

                    if ($prevCapacity === null) {
                        $prevCapacity = $capacity;

                        if ($capacity <= $setting->empty_to) {
                            $lastEmptyAt = $time;
                        } elseif ($capacity > $setting->half_to) {
                            $lastFullAt = $time;
                        }
                        continue;
                    }

                    // EMPTY → FULL
                    if (
                        $prevCapacity <= $setting->half_to &&
                        $capacity >  $setting->half_to
                    ) {
                        $timesFull++;

                        if ($lastEmptyAt) {
                            $fillDurations[] =
                                $lastEmptyAt->diffInMinutes($time) / 60;
                        }

                        $lastFullAt = $time;
                    }

                    // FULL → EMPTY
                    if (
                        $prevCapacity >  $setting->half_to &&
                        $capacity <= $setting->empty_to
                    ) {
                        if ($lastFullAt) {
                            $clearDurations[] =
                                $lastFullAt->diffInMinutes($time) / 60;
                        }

                        $lastEmptyAt = $time;
                    }

                    $prevCapacity = $capacity;
                }

                // Still full at period end
                if ($lastFullAt && $lastFullAt < $end) {
                    $clearDurations[] =
                        $lastFullAt->diffInMinutes($end) / 60;
                }
            }

            $results[] = (object) [
                'asset_name'     => $asset->asset_name,
                'times_full'     => $timesFull,
                'avg_fill_time'  => count($fillDurations)
                    ? round(array_sum($fillDurations) / count($fillDurations), 2)
                    : 0,
                'avg_clear_time' => count($clearDurations)
                    ? round(array_sum($clearDurations) / count($clearDurations), 2)
                    : 0,
            ];
        }

        return collect($results);
    }

    private function getCleaningLogs(Carbon $baseDate, string $period)
    {
        [$start, $end] = $this->resolveDateRange($baseDate, $period);

        $assets = Asset::with([
            'capacitySetting',
            'devices'
        ])->get();

        $sensorData = DB::table('sensors')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get()
            ->groupBy('device_id');

        $logs = [];

        foreach ($assets as $asset) {

            $setting = $asset->capacitySetting;
            if (!$setting) continue;

            foreach ($asset->devices as $device) {

                $deviceSensors = $sensorData->get($device->id_device, collect());

                $prevCapacity = null;

                foreach ($deviceSensors as $sensor) {

                    if (!is_numeric($sensor->capacity)) continue;

                    $capacity = (float) $sensor->capacity;
                    $time     = Carbon::parse($sensor->created_at);

                    if ($prevCapacity === null) {
                        $prevCapacity = $capacity;
                        continue;
                    }

                    // ✅ CLEANED EVENT: FULL → EMPTY
                    if (
                        $prevCapacity >  $setting->half_to &&
                        $capacity <= $setting->empty_to
                    ) {
                        $logs[] = (object) [
                            'asset_name'  => $asset->asset_name,
                            'device_name' => $device->device_name ?? $device->id_device,
                            'cleaned_at'  => $time,
                        ];
                    }

                    $prevCapacity = $capacity;
                }
            }
        }

        return collect($logs)->sortByDesc('cleaned_at');
    }

    private function getAssets()
    {
        return Asset::with('floor')->get();
    }

    private function resolveDateRange(Carbon $baseDate, string $period)
    {
        if ($period === 'today') {
            return [
                $baseDate->copy()->startOfDay(),
                $baseDate->copy()->endOfDay(),
            ];
        }

        if ($period === 'week') {
            return [
                $baseDate->copy()->startOfWeek(),
                $baseDate->copy()->endOfWeek(),
            ];
        }

        return [
            $baseDate->copy()->startOfMonth(),
            $baseDate->copy()->endOfMonth(),
        ];
    }

public function index(Request $request)
{
    $period = $request->input('period', 'month');

    if ($period === 'today') {
        $baseDate = now();
        $monthInput = now()->format('Y-m'); // keep view happy
    }
    elseif ($period === 'week' && $request->filled('week')) {

        [$year, $weekNumber] = explode('-W', $request->week);

        $baseDate = Carbon::now()
            ->setISODate($year, $weekNumber)
            ->startOfWeek();

        // Define monthInput for view (e.g., first day of week)
        $monthInput = $baseDate->format('Y-m');

    }
    else {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $baseDate   = Carbon::parse($monthInput . '-01');
    }

    $capacityStats  = $this->getCapacityStats($baseDate, $period);
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerAsset($baseDate, $period);
    $assets         = $this->getAssets();
    $cleaningLogs   = $this->getCleaningLogs($baseDate, $period);

    return view('admin.summary.index', compact(
        'monthInput',
        'period',
        'capacityStats',
        'devicesByFloor',
        'binAnalytics',
        'assets',
        'cleaningLogs'
    ));
}

public function sendEmail(Request $request)
{
    $period = $request->input('period', 'month');

    if ($period === 'today') {
        $baseDate = now();
        $monthInput = now()->format('Y-m');
    }
    elseif ($period === 'week' && $request->filled('week')) {

        [$year, $weekNumber] = explode('-W', $request->week);

        $baseDate = Carbon::now()
            ->setISODate($year, $weekNumber)
            ->startOfWeek();

        $monthInput = $baseDate->format('Y-m');
    }
    else {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $baseDate   = Carbon::parse($monthInput . '-01');
    }

    if ($period === 'today') {
    $reportTitle = 'Daily Report – ' . $baseDate->format('d M Y');
    }
    elseif ($period === 'week') {
        $start = $baseDate->copy()->startOfWeek();
        $end   = $baseDate->copy()->endOfWeek();

        $weekNumber = $baseDate->weekOfYear;

        $reportTitle = 'Weekly Report – Week ' . $weekNumber .
            ' (' . $start->format('d M Y') .
            ' – ' . $end->format('d M Y') . ')';
    }
    else {
        $start = $baseDate->copy()->startOfMonth();
        $end   = $baseDate->copy()->endOfMonth();

        $reportTitle = 'Monthly Report – ' .
            $baseDate->format('F Y') .
            ' (' . $start->format('d M') .
            ' – ' . $end->format('d M Y') . ')';
    }

    $user     = auth()->user();

    $capacityStats  = $this->getCapacityStats($baseDate, $period);
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerAsset($baseDate, $period);
    $assets         = $this->getAssets();
    $cleaningLogs   = $this->getCleaningLogs($baseDate, $period);

    // Helper to generate QuickChart URL
    $generateChartUrl = function ($type, $labels, $label, $data, $borderColor, $bgColor) {
        return "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $borderColor,
                    'backgroundColor' => $bgColor,
                    'fill' => true,
                    'tension' => 0.3,
                ]],
            ],
        ]));
    };

    $labels = $binAnalytics->pluck('asset_name')->values();

    $generateChartUrl = function ($type, $label, $data, $border, $bg) use ($labels) {
        return "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $border,
                    'backgroundColor' => $bg,
                    'fill' => true,
                    'tension' => 0.3,
                ]],
            ],
        ]));
    };

    $timesFullChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl(
                'line',
                'Times Became Full',
                $binAnalytics->pluck('times_full')->values(),
                '#8e44ad',
                'rgba(142,68,173,0.2)'
            )
        )
    );

    $avgFillChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl(
                'line',
                'Average Fill Time (Hours)',
                $binAnalytics->pluck('avg_fill_time')->values(),
                '#2ecc71',
                'rgba(46,204,113,0.2)'
            )
        )
    );

    $avgClearChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl(
                'line',
                'Average Clear Time (Hours)',
                $binAnalytics->pluck('avg_clear_time')->values(),
                '#e74c3c',
                'rgba(231,76,60,0.2)'
            )
        )
    );

    $pdf = Pdf::loadView('emails.summary_report', [
        'reportTitle'        => $reportTitle,
        'period'             => $period,
        'baseDate'           => $baseDate,
        'capacityStats'      => $capacityStats,
        'devicesByFloor'     => $devicesByFloor,
        'binAnalytics'       => $binAnalytics,
        'assets'             => $assets,
        'cleaningLogs'       => $cleaningLogs,
        'monthInput'         => $monthInput,
        'timesFullChartData' => $timesFullChartData,
        'avgFillChartData'   => $avgFillChartData,
        'avgClearChartData'  => $avgClearChartData,
    ])->setPaper('a4', 'portrait');

    Mail::to($user->email)->send(
            new SummaryReportMail([
        'reportTitle' => $reportTitle
        ], $pdf->output())
        );

    return back()->with('success', 'Summary report sent to your email!');
}

}
