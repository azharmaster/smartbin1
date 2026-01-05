<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Sensor;
use Illuminate\Support\Facades\Mail;
use App\Mail\SummaryReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use QuickChart;

class SummaryController extends Controller
{
    private function getCapacityStats($month)
{
    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end = Carbon::parse($month . '-01')->endOfMonth();

    return DB::table('sensors')
        ->whereBetween('time', [$start, $end])
        ->selectRaw('
            SUM(CASE WHEN capacity BETWEEN 0 AND 40 THEN 1 ELSE 0 END) as empty_count,
            SUM(CASE WHEN capacity BETWEEN 41 AND 85 THEN 1 ELSE 0 END) as half_count,
            SUM(CASE WHEN capacity >= 86 THEN 1 ELSE 0 END) as full_count
        ')
        ->first();
}

private function getDevicesByFloor()
{
    return DB::table('assets')
        ->join('floor', 'assets.floor_id', '=', 'floor.id')
        ->join('devices', 'devices.asset_id', '=', 'assets.id')
        ->select('floor.floor_name', DB::raw('COUNT(devices.id) as total'))
        ->groupBy('floor.floor_name')
        ->get();
}

private function getFullTrend($month)
{
    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end = Carbon::parse($month . '-01')->endOfMonth();

    return DB::table('sensors')
        ->whereBetween('sensors.time', [$start, $end])
        ->where('capacity', '>=', 86)
        ->selectRaw('DATE(time) as date, COUNT(*) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->get();
}

private function getFullCounts($month)
{
    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end = Carbon::parse($month . '-01')->endOfMonth();

    return DB::table('sensors')
        ->join('devices', 'sensors.device_id', '=', 'devices.id')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->whereBetween('sensors.time', [$start, $end])
        ->where('sensors.capacity', '>=', 86)
        ->select('assets.asset_name', DB::raw('COUNT(*) as total_full'))
        ->groupBy('assets.asset_name')
        ->orderBy('assets.asset_name')
        ->get();
}

private function getAssets()
{
    return Asset::with('floor')->get();
}

private function getMonthlySensorData($month)
{
    $start = Carbon::parse("$month-01")->startOfMonth();
    $end = Carbon::parse("$month-01")->endOfMonth();

    return DB::table('sensors')
        ->join('devices', 'sensors.device_id', '=', 'devices.id')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->whereBetween('sensors.time', [$start, $end])
        ->orderBy('assets.id')
        ->orderBy('sensors.time')
        ->select(
            'assets.id as asset_id',
            'assets.asset_name',
            'sensors.capacity',
            'sensors.time'
        )
        ->get()
        ->groupBy('asset_id');
}

private function computeBinAnalytics($month)
{
    $start = Carbon::parse("$month-01")->startOfMonth();
    $end   = Carbon::parse("$month-01")->endOfMonth();

    // Get all sensors for the month, ordered by asset and time
    $sensors = DB::table('sensors')
        ->join('devices', 'sensors.device_id', '=', 'devices.id')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->whereBetween('sensors.time', [$start, $end])
        ->orderBy('assets.id')
        ->orderBy('sensors.time')
        ->select('assets.id as asset_id', 'assets.asset_name', 'sensors.capacity', 'sensors.time')
        ->get()
        ->groupBy('asset_id'); // group by asset

    $results = [];

    foreach ($sensors as $rows) {
        $timesFull = 0;
        $fillDurations = [];
        $clearDurations = [];

        $prevCapacity = null;
        $lastEmptyAt = null;
        $lastFullAt = null;

        foreach ($rows as $row) {
            $currentCapacity = $row->capacity;
            $currentTime = Carbon::parse($row->time);

            // If first reading of the month is already full, count it
            if ($prevCapacity === null && $currentCapacity >= 86) {
                $timesFull++;
                $lastFullAt = $currentTime;
            }

            // EMPTY → FULL
            if ($prevCapacity !== null && $prevCapacity < 86 && $currentCapacity >= 86) {
                $timesFull++;
                $lastFullAt = $currentTime;

                if ($lastEmptyAt) {
                    $fillDurations[] = $lastFullAt->diffInMinutes($lastEmptyAt);
                }
            }

            // FULL → EMPTY
            if ($prevCapacity !== null && $prevCapacity >= 86 && $currentCapacity < 41) {
                $lastEmptyAt = $currentTime;

                if ($lastFullAt) {
                    $clearDurations[] = $lastEmptyAt->diffInMinutes($lastFullAt);
                }
            }

            $prevCapacity = $currentCapacity;
        }

        $results[] = [
            'asset_name'     => $rows->first()->asset_name,
            'times_full'     => $timesFull,
            'avg_fill_time'  => count($fillDurations)
                ? round(array_sum($fillDurations) / count($fillDurations))
                : 0,
            'avg_clear_time' => count($clearDurations)
                ? round(array_sum($clearDurations) / count($clearDurations))
                : 0,
        ];
    }

    return collect($results);
}

    public function index(Request $request)
    {
        $month = $request->input('month') ?? now()->format('Y-m');

        $capacityStats  = $this->getCapacityStats($month);
        $devicesByFloor = $this->getDevicesByFloor();
        $fullTrend      = $this->getFullTrend($month);
        $fullCounts     = $this->getFullCounts($month);

        $groupedSensors = $this->computeBinAnalytics($month);
        $binAnalytics   = $groupedSensors;

        // All assets (for images)
        $assets = Asset::with('floor')->get();

        return view('admin.summary.index', compact(
            'month',
            'capacityStats',
            'devicesByFloor',
            'fullTrend',
            'fullCounts',
            'binAnalytics',
            'assets'
        ));
    }

public function sendEmail(Request $request)
{
    $month = $request->month ?? now()->format('Y-m');
    $user = auth()->user();

    // --- Prepare report data ---
    $capacityStats = $this->getCapacityStats($month);
    $devicesByFloor = $this->getDevicesByFloor();
    $fullTrend = $this->getFullTrend($month);
    $fullCounts = $this->getFullCounts($month);
    $assets = $this->getAssets();

    // --- Generate chart images using QuickChart ---
    $charts = [];

    // Helper to get chart binary for PDF embedding
    $getChartImage = function ($config) {
        $chart = new QuickChart();
        $chart->setConfig(json_encode($config));
        return file_get_contents($chart->getUrl());
    };

    // --- Generate PDF ---
    $pdf = Pdf::loadView('emails.summary_report', [
        'month' => $month,
        'capacityStats' => $capacityStats,
        'devicesByFloor' => $devicesByFloor,
        'fullTrend' => $fullTrend,
        'fullCounts' => $fullCounts,
        'assets' => $assets,
        'charts' => $charts
    ])->setPaper('a4', 'portrait');

    // --- Send email with PDF attachment ---
    Mail::to($user->email)->send(new SummaryReportMail($pdf));

    return back()->with('success', 'Summary report sent to your email!');
}


}
