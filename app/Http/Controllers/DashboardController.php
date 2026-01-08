<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Floor;
use App\Models\Asset;
use App\Models\Todo;
use App\Models\Complaint;
use App\Models\User; 
use App\Models\Task; 
use App\Models\CapacitySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \Illuminate\Support\Collection<int, Device> $devices */
        $devices = $this->loadDevicesWithLatestSensor();

        // ✅ Load capacity settings from DB
        $capacity = CapacitySetting::first();
        $emptyMax = $capacity->empty_to;
        $halfMax  = $capacity->half_to;

        $totalDevices = $devices->count();
        $fullDevicesCollection = $this->countFullDevices($devices, $halfMax);
        $fullDevices = $fullDevicesCollection->count();
        $halfDevicesCollection = $this->countHalfDevices($devices, $emptyMax, $halfMax);
        $halfDevices = $halfDevicesCollection->count();
        $emptyDevices = $this->countEmptyDevices($devices, $emptyMax);
        $undetectedDevices = $this->countUndetectedDevices($devices);

        /* ------------------------------
         | TREND CALCULATION
         |------------------------------*/
        $lastMonth = Carbon::now()->subMonth();

        $previousDevices = $this->loadDevicesWithLatestSensor($lastMonth);

        $previousTotal = $previousDevices->count();
        $previousFull = $this->countFullDevices($previousDevices, $halfMax)->count();
        $previousHalf = $this->countHalfDevices($previousDevices, $emptyMax, $halfMax)->count();
        $previousEmpty = $this->countEmptyDevices($previousDevices, $emptyMax);
        $previousUndetected = $this->countUndetectedDevices($previousDevices);

        // Helper function
        $trend = fn($current, $previous) => [
            'icon'  => $current > $previous ? '▲' : ($current < $previous ? '▼' : '—'),
            'value' => abs($current - $previous),
            'class' => $current > $previous ? 'text-success' : ($current < $previous ? 'text-danger' : 'text-muted'),
        ];

        $totalTrend = $trend($totalDevices, $previousTotal);

        $floors = Floor::all();
        $assetsWithCoords = Asset::whereNotNull('x')
                                 ->whereNotNull('y')
                                 ->get();
        $todos = $this->loadTodosForUser(Auth::id());
        $latestComplaints = $this->loadLatestComplaints();
        $users = User::all();
        $assignedTasks = $this->loadAssignedTasks();
        $tasksCompletedPerStaff = $this->loadTasksCompletedPerStaff();

        $smartBinClearTimes = $this->calculateSmartBinClearTimes($emptyMax, $halfMax);


        return view('dashboard.index', compact(
            'totalDevices',
            'fullDevices',
            'fullDevicesCollection',
            'halfDevices',
            'halfDevicesCollection',
            'emptyDevices',
            'undetectedDevices',
            'todos',
            'floors',
            'assetsWithCoords',
            'devices',
            'users',
            'assignedTasks',
            'latestComplaints',
            'smartBinClearTimes',
            'totalTrend',      // <-- added
        ));
    }

    /** Load devices with latest sensor and optional before date */
    private function loadDevicesWithLatestSensor($before = null)
    {
        return Device::with([
            'asset.floor',
            'latestSensor' => function ($q) use ($before) {
                if ($before) {
                    $q->where('time', '<=', $before);
                }
            }
        ])->get();
    }

    //to set the capasity of the bin
    private function countFullDevices($devices, $halfMax)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > $halfMax
        );
    }

    private function countHalfDevices($devices, $emptyMax, $halfMax)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > $emptyMax &&
            $d->latestSensor->capacity <= $halfMax
        );
    }

    private function countEmptyDevices($devices, $emptyMax)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity <= $emptyMax
        )->count();
    }

    private function countUndetectedDevices($devices)
    {
        return $devices->filter(function($d) {
            if (!$d->latestSensor) return true;
            $network = $d->latestSensor->network;
            return is_null($network)
                || $network === ''
                || (string)$network === '0'
                || strtolower((string)$network) === 'unavailable';
        })->count();
    }

    private function loadTodosForUser($userId)
    {
        return Todo::where('userID', $userId)
                    ->where('status', 'pending')
                    ->orderByDesc('id')
                    ->get();
    }

    private function loadLatestComplaints()
    {
        return Complaint::with('asset')
                        ->orderByDesc('created_at')
                        ->take(10)
                        ->get();
    }

    private function loadAssignedTasks()
    {
        return Task::with('user', 'asset', 'floor')
                   ->orderByDesc('id')
                   ->get();
    }

    private function loadTasksCompletedPerStaff()
    {
        return Task::select('user_id', DB::raw('COUNT(*) as completed_count'))
                   ->where('status', 'completed')
                   ->whereMonth('updated_at', Carbon::now()->month)
                   ->whereYear('updated_at', Carbon::now()->year)
                   ->whereHas('user', fn($q) => $q->where('role', 2))
                   ->groupBy('user_id')
                   ->with('user:id,name')
                   ->get();
    }

    /** Calculate SmartBin clear times in hours */
private function calculateSmartBinClearTimes($emptyMax, $halfMax)
{
    $smartBinClearTimes = [];

    $devices = Device::with([
        'asset',
        'sensors' => fn($q) => $q->orderBy('time', 'asc')
    ])->get();

    foreach ($devices as $device) {
        if (!$device->asset) continue;

        $fullTimestamp = null;
        $latestClear   = null; // ✅ store only the latest

        foreach ($device->sensors as $sensor) {

            // Bin becomes FULL
            if ($sensor->capacity > $halfMax && $fullTimestamp === null) {
                $fullTimestamp = $sensor->time;
                continue;
            }

            // Bin is CLEARED
            if ($fullTimestamp && $sensor->capacity <= $emptyMax) {

                $minutes = Carbon::parse($fullTimestamp)
                    ->diffInMinutes(Carbon::parse($sensor->time));

                // 🔁 overwrite → keeps ONLY latest clear
                $latestClear = [
                    'asset_name'  => $device->asset->asset_name,
                    'device_name' => $device->device_name,
                    'minutes'     => $minutes,
                    'hours'       => round($minutes / 60, 2),
                    'cleared_at'  => $sensor->time,
                ];

                $fullTimestamp = null;
            }
        }

        // ✅ push only ONE record per device
        if ($latestClear) {
            $smartBinClearTimes[] = $latestClear;
        }
    }

    return collect($smartBinClearTimes);
}

}
