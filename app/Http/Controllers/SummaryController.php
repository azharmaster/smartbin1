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
    private function getCapacityStats(Carbon $carbonMonth)
    {
        return DB::table('sensors')
            ->whereBetween('sensors.time', [
                $carbonMonth->copy()->startOfMonth(),
                $carbonMonth->copy()->endOfMonth()
            ])
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

    private function computeBinAnalyticsPerDevice($month)
    {
        // Parse the selected month
        $carbonMonth = Carbon::parse($month . '-01');

        // Fetch all sensors for the month, joined with devices & assets
        $sensors = DB::table('sensors')
            ->join('devices', 'sensors.device_id', '=', 'devices.id_device')
            ->join('assets', 'devices.asset_id', '=', 'assets.id')
            ->whereBetween('sensors.time', [
                $carbonMonth->copy()->startOfMonth(),
                $carbonMonth->copy()->endOfMonth()
            ])
            ->select(
                'sensors.id as sensor_id',
                'sensors.capacity',
                'sensors.time',
                'devices.id_device as device_id',
                'devices.device_name',
                'assets.id as asset_id',
                'assets.asset_name'
            )
            ->orderBy('devices.id_device')
            ->orderBy('sensors.time')
            ->get()
            ->groupBy('device_id'); // GROUP BY device for per-device calculations

        $results = [];

        foreach ($sensors as $deviceId => $deviceSensors) {

            $timesFull   = 0;
            $fillDurations  = [];
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
                        $fillDurations[] = max($lastEmptyAt->diffInMinutes($time) / 60, 0);
                    }
                    $lastFullAt = $time;
                }

                // FULL → EMPTY
                if ($prevCapacity >= 86 && $capacity <= 40) {
                    if ($lastFullAt) {
                        $clearDurations[] = max($lastFullAt->diffInMinutes($time) / 60, 0);
                    }
                    $lastEmptyAt = $time;
                }

                $prevCapacity = $capacity;
            }

            // Edge case: last full reading never emptied
            if ($lastFullAt && (!$lastEmptyAt || $lastEmptyAt < $lastFullAt)) {
                $clearDurations[] = max($carbonMonth->copy()->endOfMonth()->diffInMinutes($lastFullAt) / 60, 0);
            }

            $firstSensor = $deviceSensors->first();

            $results[] = (object)[
                'asset_name'     => $firstSensor->asset_name,
                'device_name'    => $firstSensor->device_name,
                'times_full'     => $timesFull,
                'avg_fill_time'  => count($fillDurations) ? round(array_sum($fillDurations) / count($fillDurations), 2) : 0,
                'avg_clear_time' => count($clearDurations) ? round(array_sum($clearDurations) / count($clearDurations), 2) : 0,
            ];
        }

        return collect($results);
    }

    private function getAssets()
    {
        return Asset::with('floor')->get();
    }

    public function index(Request $request)
    {
        // sanitize month input and fallback
        $monthInput = preg_replace('/[^0-9\-]/', '', $request->input('month') ?? '');
        if (!$monthInput) {
            $lastSensor = DB::table('sensors')->max('time');
            $monthInput = Carbon::parse($lastSensor)->format('Y-m');
        }

        try {
            $carbonMonth = Carbon::parse($monthInput . '-01');
        } catch (\Exception $e) {
            $carbonMonth = Carbon::now();
            $monthInput  = $carbonMonth->format('Y-m');
        }
        
        $capacityStats   = $this->getCapacityStats($carbonMonth);
        $devicesByFloor  = $this->getDevicesByFloor();
        $binAnalytics    = $this->computeBinAnalyticsPerDevice($carbonMonth);
        $assets          = $this->getAssets();

        return view('admin.summary.index', compact(
            'monthInput',
            'capacityStats',
            'devicesByFloor',
            'binAnalytics',
            'assets'
        ));
    }

public function sendEmail(Request $request)
{
    $month = $request->month ?? now()->format('Y-m');
    $user  = auth()->user();

    // Get analytics
    $capacityStats  = $this->getCapacityStats(Carbon::parse($month . '-01'));
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerDevice($month);
    $assets         = $this->getAssets();

    // Helper to generate QuickChart URL
    $generateChartUrl = function($type, $labels, $label, $data, $borderColor, $bgColor) {
        $config = [
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $borderColor,
                    'backgroundColor' => $bgColor,
                    'fill' => true,
                    'tension' => 0.3
                ]]
            ]
        ];
        return "https://quickchart.io/chart?c=" . urlencode(json_encode($config));
    };

    // Generate URLs
    $labels = $binAnalytics->pluck('device_name');
    $timesFullUrl = $generateChartUrl('line', $labels, 'Times Became Full', $binAnalytics->pluck('times_full'), '#8e44ad', 'rgba(142,68,173,0.2)');
    $avgFillUrl   = $generateChartUrl('line', $labels, 'Average Fill Time (Hours)', $binAnalytics->pluck('avg_fill_time'), '#2ecc71', 'rgba(46,204,113,0.2)');
    $avgClearUrl  = $generateChartUrl('line', $labels, 'Average Clear Time (Hours)', $binAnalytics->pluck('avg_clear_time'), '#e74c3c', 'rgba(231,76,60,0.2)');

    // Convert charts to base64 for DomPDF
    $timesFullChartData = 'data:image/png;base64,' . base64_encode(file_get_contents($timesFullUrl));
    $avgFillChartData   = 'data:image/png;base64,' . base64_encode(file_get_contents($avgFillUrl));
    $avgClearChartData  = 'data:image/png;base64,' . base64_encode(file_get_contents($avgClearUrl));

    // Load PDF view with base64 images
    $pdf = Pdf::loadView('emails.summary_report', [
        'month'               => $month,
        'capacityStats'       => $capacityStats,
        'devicesByFloor'      => $devicesByFloor,
        'binAnalytics'        => $binAnalytics,
        'assets'              => $assets,
        'timesFullChartData'  => $timesFullChartData,
        'avgFillChartData'    => $avgFillChartData,
        'avgClearChartData'   => $avgClearChartData,
    ])->setPaper('a4', 'portrait');

    // Send email with PDF attachment
    Mail::to($user->email)->send(
        new SummaryReportMail(
            [
                'month' => $month, // pass any additional data for the email view
            ],
            $pdf->output() // pass the actual PDF bytes
        )
    );

    return back()->with('success', 'Summary report sent to your email!');
}

}
