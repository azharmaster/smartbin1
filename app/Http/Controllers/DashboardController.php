<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Floor;
use App\Models\Asset;
use App\Models\Todo;
use App\Models\Complaint;
use App\Models\User; 
use App\Models\Task; 
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        // Load devices with their latest sensor & asset-floor relationship
        $devices = Device::with('latestSensor', 'asset.floor')->get();

        // Total devices
        $totalDevices = $devices->count();

        // FULL > 85%
        $fullDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 85;
        });
        $fullDevices = $fullDevicesCollection->count();

        // HALF 40–85%
        $halfDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 40 &&
                   $d->latestSensor->capacity <= 85;
        });
        $halfDevices = $halfDevicesCollection->count();

        // EMPTY <= 40%
        $emptyDevices = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity <= 40;
        })->count();

        // Undetected: no sensor or bad network
        $undetectedDevices = $devices->filter(function ($d) {
            if (!$d->latestSensor) return true;
            $network = $d->latestSensor->network;
            return is_null($network)
                || $network === ''
                || (string)$network === '0'
                || strtolower((string)$network) === 'unavailable';
        })->count();

        // Load floors for the map dropdown
        $floors = Floor::all();

        $assetsWithCoords = Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();

        // Load To-Do items for the current user
        $todos = Todo::where('userID', Auth::id())
                     ->where('status', 'pending')
                     ->orderBy('id', 'desc')
                     ->get();

        // Load latest complaints
        $latestComplaints = Complaint::with('asset')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Load all users for the simple user list
        $users = User::all(); // <-- Added this line

        // Load assigned tasks (optional: latest 10 or all)
        $assignedTasks = Task::with('user', 'asset', 'floor')
                             ->orderBy('id', 'desc')
                             ->get();

        // TASKS COMPLETED PER STAFF (CURRENT MONTH)
        $tasksCompletedPerStaff = Task::select(
                'user_id',
                DB::raw('COUNT(*) as completed_count')
            )
            ->where('status', 'completed')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereHas('user', function ($q) {
                $q->where('role', 2); // staff
            })
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get();

$smartBinClearTimes = [];

$devices = Device::with([
        'asset',
        'sensors' => function ($q) {
            $q->orderBy('time', 'asc'); // ✅ IMPORTANT
        }
    ])
    ->whereHas('asset', function ($q) {
        $q->where('category', 'SmartBin');
    })
    ->get();

foreach ($devices as $device) {

    // Skip if no asset (extra safety)
    if (!$device->asset) {
        continue;
    }

    $fullTimestamp = null;

    foreach ($device->sensors as $sensor) {

        // Detect FULL (>85%)
        if ($sensor->capacity > 85 && $fullTimestamp === null) {
            $fullTimestamp = $sensor->time;
            continue;
        }

        // Detect EMPTY (<=40%) AFTER FULL
        if ($fullTimestamp && $sensor->capacity <= 40) {

            $minutes = Carbon::parse($fullTimestamp)
                ->diffInMinutes(Carbon::parse($sensor->time));

            $smartBinClearTimes[] = [
                'asset_name'  => $device->asset->asset_name,
                'device_name' => $device->device_name,
                'minutes'     => $minutes,
                'cleared_at'  => $sensor->time,
            ];

            // Reset → ready for next FULL → EMPTY cycle
            $fullTimestamp = null;
        }
    }
}

$smartBinClearTimes = collect($smartBinClearTimes)->map(function($item) {
    $item['hours'] = round($item['minutes'] / 60, 2); // convert to hours, 2 decimals
    return $item;
});

$smartBinClearTimes = collect($smartBinClearTimes);

        // Pass all data to the dashboard view
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
            'users',          // <-- Added this here
            'assignedTasks',   // <-- Added this here
            'latestComplaints',
            'tasksCompletedPerStaff',
            'smartBinClearTimes',
        ));
    }
}
