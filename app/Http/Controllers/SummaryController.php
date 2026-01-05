<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;
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
        ->whereBetween('created_at', [$start, $end])
        ->selectRaw('
            SUM(CASE WHEN capacity BETWEEN 0 AND 40 THEN 1 ELSE 0 END) as empty_count,
            SUM(CASE WHEN capacity BETWEEN 41 AND 85 THEN 1 ELSE 0 END) as half_count,
            SUM(CASE WHEN capacity >= 86 THEN 1 ELSE 0 END) as full_count
        ')
        ->first();
}

private function getDevicesByFloor($month)
{
    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end = Carbon::parse($month . '-01')->endOfMonth();

    return DB::table('assets')
        ->join('floor', 'assets.floor_id', '=', 'floor.id')
        ->join('devices', 'devices.asset_id', '=', 'assets.id')
        ->select('floor.floor_name', DB::raw('count(devices.id) as total'))
        ->groupBy('floor.floor_name')
        ->get();
}

private function getFullTrend($month)
{
    $start = Carbon::parse($month . '-01')->startOfMonth();
    $end = Carbon::parse($month . '-01')->endOfMonth();

    return DB::table('sensors')
        ->whereBetween('created_at', [$start, $end])
        ->where('capacity', '>=', 86)
        ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
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
        ->whereBetween('sensors.created_at', [$start, $end])
        ->where('sensors.capacity', '>=', 86)
        ->select('assets.asset_name', DB::raw('COUNT(*) as total_full'))
        ->groupBy('assets.asset_name')
        ->orderBy('assets.asset_name')
        ->get();
}

private function getAssets($month)
{
    return Asset::with('floor')->get();
}
    public function index(Request $request)
    {
        // Determine selected month (default: current month)
        $month = $request->input('month') ?? Carbon::now()->format('Y-m');
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = Carbon::parse($month . '-01')->endOfMonth();

        // ========================
        // CAPACITY STATS
        // ========================
        $capacityStats = DB::table('sensors')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                SUM(CASE WHEN capacity BETWEEN 0 AND 40 THEN 1 ELSE 0 END) as empty_count,
                SUM(CASE WHEN capacity BETWEEN 41 AND 85 THEN 1 ELSE 0 END) as half_count,
                SUM(CASE WHEN capacity >= 86 THEN 1 ELSE 0 END) as full_count
            ')
            ->first();

        // ========================
        // DEVICES BY FLOOR
        // ========================
        $devicesByFloor = DB::table('assets')
            ->join('floor', 'assets.floor_id', '=', 'floor.id')
            ->join('devices', 'devices.asset_id', '=', 'assets.id')
            ->select('floor.floor_name', DB::raw('count(devices.id) as total'))
            ->groupBy('floor.floor_name')
            ->get();

        // ========================
        // FULL BIN TREND (daily)
        // ========================
        $fullTrend = DB::table('sensors')
            ->whereBetween('created_at', [$start, $end])
            ->where('capacity', '>=', 86)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ========================
        // FULL COUNTS PER BIN
        // ========================
        $fullCounts = DB::table('sensors')
            ->join('devices', 'sensors.device_id', '=', 'devices.id')
            ->join('assets', 'devices.asset_id', '=', 'assets.id')
            ->whereBetween('sensors.created_at', [$start, $end])
            ->where('sensors.capacity', '>=', 86)
            ->select('assets.asset_name', DB::raw('COUNT(*) as total_full'))
            ->groupBy('assets.asset_name')
            ->orderBy('assets.asset_name')
            ->get();

        // All assets (for images)
        $assets = Asset::with('floor')->get();

        return view('admin.summary.index', compact(
            'month',
            'capacityStats',
            'devicesByFloor',
            'fullTrend',
            'fullCounts',
            'assets'
        ));
    }

public function sendEmail(Request $request)
{
    $month = $request->month ?? now()->format('Y-m');
    $user = auth()->user();

    // --- Prepare report data ---
    $capacityStats = $this->getCapacityStats($month);
    $devicesByFloor = $this->getDevicesByFloor($month);
    $fullTrend = $this->getFullTrend($month);
    $fullCounts = $this->getFullCounts($month);
    $assets = $this->getAssets($month);

    // --- Generate chart images using QuickChart ---
    $charts = [];

    // Helper to get chart binary for PDF embedding
    $getChartImage = function ($config) {
        $chart = new QuickChart();
        $chart->setConfig(json_encode($config));
        return file_get_contents($chart->getUrl());
    };

    // 1. Capacity Distribution
    $charts['capacityChart'] = $getChartImage([
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Empty', 'Half Full', 'Full'],
            'datasets' => [[
                'data' => [
                    $capacityStats->empty_count,
                    $capacityStats->half_count,
                    $capacityStats->full_count
                ],
                'backgroundColor' => ['#2ecc71', '#f1c40f', '#e74c3c']
            ]]
        ],
        'options' => ['plugins' => ['legend' => ['position' => 'bottom']]]
    ]);

    // 2. Devices by Floor
    $charts['floorChart'] = $getChartImage([
        'type' => 'bar',
        'data' => [
            'labels' => $devicesByFloor->pluck('floor_name'),
            'datasets' => [[
                'label' => 'Total Devices',
                'data' => $devicesByFloor->pluck('total'),
                'backgroundColor' => '#3498db',
                'borderRadius' => 5
            ]]
        ]
    ]);

    // 3. Full Bin Trend
    $charts['trendChart'] = $getChartImage([
        'type' => 'line',
        'data' => [
            'labels' => $fullTrend->pluck('date'),
            'datasets' => [[
                'label' => 'Full Bins',
                'data' => $fullTrend->pluck('total'),
                'borderColor' => '#e74c3c',
                'backgroundColor' => 'rgba(231,76,60,0.2)',
                'fill' => true,
                'tension' => 0.3
            ]]
        ]
    ]);

    // 4. Full Counts per Bin
    $charts['fullCountsChart'] = $getChartImage([
        'type' => 'bar',
        'data' => [
            'labels' => $fullCounts->pluck('asset_name'),
            'datasets' => [[
                'label' => 'Times Full',
                'data' => $fullCounts->pluck('total_full'),
                'backgroundColor' => '#8e44ad',
                'borderRadius' => 5
            ]]
        ]
    ]);

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
