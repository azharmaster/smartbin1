<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;
use Illuminate\Support\Facades\Mail;
use App\Mail\SummaryReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use QuickChart\Quickchart;

class SummaryController extends Controller
{
    private function getCapacityStats(Carbon $baseDate, string $period)
    {
        [$start, $end] = $this->resolveDateRange($baseDate, $period);

        return DB::table('sensors')
            ->whereBetween('sensors.time', [$start, $end])
            ->selectRaw("
                SUM(CASE WHEN capacity BETWEEN 0 AND 40 THEN 1 ELSE 0 END) as empty_count,
                SUM(CASE WHEN capacity BETWEEN 41 AND 85 THEN 1 ELSE 0 END) as half_count,
                SUM(CASE WHEN capacity >= 86 THEN 1 ELSE 0 END) as full_count
            ")
            ->first();
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

    private function computeBinAnalyticsPerDevice(Carbon $baseDate, string $period)
{
    [$start, $end] = $this->resolveDateRange($baseDate, $period);

    // 1️⃣ Load ALL devices first (this stabilizes the count)
    $devices = DB::table('devices')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->select(
            'devices.id_device as device_id',
            'devices.device_name',
            'assets.asset_name'
        )
        ->orderBy('devices.id_device')
        ->get()
        ->keyBy('device_id');

    // 2️⃣ Load sensor data ONLY for the selected period
    $sensorData = DB::table('sensors')
        ->whereBetween('time', [$start, $end])
        ->orderBy('time')
        ->get()
        ->groupBy('device_id');

    $results = [];

    // 3️⃣ Loop over DEVICES, not sensors
    foreach ($devices as $deviceId => $device) {

        $deviceSensors = $sensorData->get($deviceId, collect());

        $timesFull = 0;
        $fillDurations = [];
        $clearDurations = [];

        $prevCapacity = null;
        $lastFullAt = null;
        $lastEmptyAt = null;

        foreach ($deviceSensors as $sensor) {
            $capacity = (float) $sensor->capacity;
            $time = Carbon::parse($sensor->time);

            if ($prevCapacity === null) {
                $prevCapacity = $capacity;
                if ($capacity <= 40) $lastEmptyAt = $time;
                if ($capacity >= 86) $lastFullAt = $time;
                continue;
            }

            // EMPTY → FULL
            if ($prevCapacity < 86 && $capacity >= 86) {
                $timesFull++;
                if ($lastEmptyAt) {
                    $fillDurations[] = $lastEmptyAt->diffInMinutes($time) / 60;
                }
                $lastFullAt = $time;
            }

            // FULL → EMPTY
            if ($prevCapacity >= 86 && $capacity <= 40) {
                if ($lastFullAt) {
                    $clearDurations[] = $lastFullAt->diffInMinutes($time) / 60;
                }
                $lastEmptyAt = $time;
            }

            $prevCapacity = $capacity;
        }

        // Edge case: bin stayed full until period end
        if ($lastFullAt && $lastFullAt < $end) {
            $clearDurations[] = $lastFullAt->diffInMinutes($end) / 60;
        }

        // 4️⃣ Always return device, even with no data
        $results[] = (object) [
            'asset_name'     => $device->asset_name,
            'device_name'    => $device->device_name,
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

    private function getAssets()
    {
        return Asset::with('floor')->get();
    }

    private function resolveDateRange(Carbon $baseDate, string $period)
    {
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

    // $monthInput = $request->input('month') ?? now()->format('Y-m');
    // $baseDate = Carbon::parse($monthInput . '-01');

    $period = $request->input('period', 'month');

    if ($period === 'week' && $request->filled('week')) {

        // example: 2026-W03
        [$year, $weekNumber] = explode('-W', $request->week);

        $baseDate = Carbon::now()
            ->setISODate($year, $weekNumber)
            ->startOfWeek(); // Monday

    } else {

        // month fallback
        $monthInput = $request->input('month', now()->format('Y-m'));
        $baseDate = Carbon::parse($monthInput . '-01');
    }


    $capacityStats  = $this->getCapacityStats($baseDate, $period);
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerDevice($baseDate, $period);
    $assets         = $this->getAssets();

    return view('admin.summary.index', compact(
        'monthInput',
        'period',
        'capacityStats',
        'devicesByFloor',
        'binAnalytics',
        'assets'
    ));
}

public function sendEmail(Request $request)
{
    $period   = $request->input('period', 'month');
    $month    = $request->input('month', now()->format('Y-m'));
    $baseDate = Carbon::parse($month . '-01');
    $user     = auth()->user();

    $capacityStats  = $this->getCapacityStats($baseDate, $period);
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerDevice($baseDate, $period);
    $assets         = $this->getAssets();

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

    $labels = $binAnalytics->pluck('device_name');

    $timesFullChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl('line', $labels, 'Times Became Full', $binAnalytics->pluck('times_full'), '#8e44ad', 'rgba(142,68,173,0.2)')
        )
    );

    $avgFillChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl('line', $labels, 'Average Fill Time (Hours)', $binAnalytics->pluck('avg_fill_time'), '#2ecc71', 'rgba(46,204,113,0.2)')
        )
    );

    $avgClearChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl('line', $labels, 'Average Clear Time (Hours)', $binAnalytics->pluck('avg_clear_time'), '#e74c3c', 'rgba(231,76,60,0.2)')
        )
    );

    $pdf = Pdf::loadView('emails.summary_report', [
        'month'              => $month,
        'period'             => $period,
        'capacityStats'      => $capacityStats,
        'devicesByFloor'     => $devicesByFloor,
        'binAnalytics'       => $binAnalytics,
        'assets'             => $assets,
        'timesFullChartData' => $timesFullChartData,
        'avgFillChartData'   => $avgFillChartData,
        'avgClearChartData'  => $avgClearChartData,
    ])->setPaper('a4', 'portrait');

    Mail::to($user->email)->send(
        new SummaryReportMail(['month' => $month], $pdf->output())
    );

    return back()->with('success', 'Summary report sent to your email!');
}

}
