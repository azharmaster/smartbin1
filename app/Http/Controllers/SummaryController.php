<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;

class SummaryController extends Controller
{
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
}
