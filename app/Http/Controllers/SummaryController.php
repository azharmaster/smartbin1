<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SummaryController extends Controller
{
    public function index()
    {
        $capacityStats = DB::table('sensors')
            ->selectRaw('
                SUM(CASE WHEN capacity BETWEEN 0 AND 40 THEN 1 ELSE 0 END) as empty,
                SUM(CASE WHEN capacity BETWEEN 41 AND 85 THEN 1 ELSE 0 END) as half,
                SUM(CASE WHEN capacity >= 86 THEN 1 ELSE 0 END) as full
            ')
            ->first();

        /* =========================
         * DEVICES BY FLOOR
         * ========================= */
        $devicesByFloor = DB::table('assets')
            ->join('floors', 'assets.floor_id', '=', 'floors.id')
            ->join('devices', 'devices.asset_id', '=', 'assets.id')
            ->select('floors.floor_name', DB::raw('count(devices.id) as total'))
            ->groupBy('floors.floor_name')
            ->get();

        /* =========================
         * TASK / COMPLAINT STATUS
         * ========================= */
        $taskStats = DB::table('complaints')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        /* =========================
         * DAILY FULL BIN TREND
         * ========================= */
        $fullTrend = DB::table('sensors')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('capacity', '>=', 86)
            ->whereBetween('created_at', [
                Carbon::now()->subDays(14),
                Carbon::now()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.summary.index', compact(
            'capacityStats',
            'devicesByFloor',
            'taskStats',
            'fullTrend'
        ));
    }
}
