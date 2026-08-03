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
use Illuminate\Support\Facades\Cache;
use Closure;
use Throwable;

class SupervisorDashboardController extends Controller
{
    private const LIVE_CACHE_SECONDS = 10;
    private const DASHBOARD_CACHE_SECONDS = 60;
    private const STATIC_CACHE_SECONDS = 300;

    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \Illuminate\Support\Collection<int, Device> $devices */
        $devices = $this->rememberDashboard('devices-with-latest-sensor', self::LIVE_CACHE_SECONDS, fn () => $this->loadDevicesWithLatestSensor());

        $totalDevices = $devices->count();
        $fullDevicesCollection = $this->countFullDevices($devices);
        $fullDevices = $fullDevicesCollection->count();
        $halfDevicesCollection = $this->countHalfDevices($devices);
        $halfDevices = $halfDevicesCollection->count();
        $emptyDevices = $this->countEmptyDevices($devices);
        $undetectedDevices = $this->countUndetectedDevices($devices);

        $floors = $this->rememberDashboard('floors:all', self::STATIC_CACHE_SECONDS, fn () => Floor::all());
        $assetsWithCoords = $this->rememberDashboard('assets-with-coords', self::DASHBOARD_CACHE_SECONDS, fn () => Asset::whereNotNull('x')
                                 ->whereNotNull('y')
                                 ->get());
        $todos = $this->rememberDashboard('todos:user:' . Auth::id(), self::LIVE_CACHE_SECONDS, fn () => $this->loadTodosForUser(Auth::id()));
        $latestComplaints = $this->rememberDashboard('latest-complaints', self::DASHBOARD_CACHE_SECONDS, fn () => $this->loadLatestComplaints());
        $users = $this->rememberDashboard('staff-users', self::STATIC_CACHE_SECONDS, fn () => User::where('role', 2)->get());
        $assignedTasks = $this->rememberDashboard('assigned-tasks', self::DASHBOARD_CACHE_SECONDS, fn () => $this->loadAssignedTasks());
        $tasksCompletedPerStaff = $this->rememberDashboard('tasks-completed-per-staff:' . now()->format('Y-m'), self::DASHBOARD_CACHE_SECONDS, fn () => $this->loadTasksCompletedPerStaff());

        $smartBinClearTimes = $this->rememberDashboard('smart-bin-clear-times', self::DASHBOARD_CACHE_SECONDS, fn () => $this->calculateSmartBinClearTimes());

        /* =======================
           📅 CALENDAR EVENTS (ADDED)
        ======================= */
$calendarEvents = collect();

foreach ($assignedTasks as $task) {
    $calendarEvents->push([
        'id'    => $task->id,
        'title' => 'Task: ' . ($task->asset->asset_name ?? 'Asset'),
        'start' => $task->created_at->toDateString(),

        'color' => match ($task->status) {
            'completed'   => '#28a745',
            'in_progress' => '#17a2b8',
            'pending'     => '#ffc107',
            'reject'      => '#dc3545',
            default       => '#6c757d',
        },

        // Extra data for modal
        'extendedProps' => [
            'user'   => $task->user->name ?? 'N/A',
            'asset'  => $task->asset->asset_name ?? 'N/A',
            'floor'  => $task->floor->floor_name ?? 'N/A',
            'status' => $task->status,
            'notes'  => $task->notes ?? '-',
        ],
    ]);
}

        foreach ($smartBinClearTimes as $bin) {
            $calendarEvents->push([
                'title' => 'Bin Cleared: ' . $bin['device_name'],
                'start' => Carbon::parse($bin['cleared_at'])->toDateString(),
                'color' => '#0d6efd',
            ]);
        }
        /* ======================= */

        return view('dashboard.supervisorindex', compact(
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
            'tasksCompletedPerStaff',
            'smartBinClearTimes',
            'calendarEvents' // ✅ ADDED
        ));
    }

    /** Load devices with latest sensor and asset-floor relationship */
    private function loadDevicesWithLatestSensor()
    {
        return Device::with('latestSensor', 'asset.floor')->get();
    }

    private function rememberDashboard(string $key, int $seconds, Closure $callback): mixed
    {
        try {
            return Cache::remember("supervisor-dashboard:{$key}", now()->addSeconds($seconds), $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    private function countFullDevices($devices)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > 85
        );
    }

    private function countHalfDevices($devices)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > 40 &&
            $d->latestSensor->capacity <= 85
        );
    }

    private function countEmptyDevices($devices)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity <= 40
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
    private function calculateSmartBinClearTimes()
    {
        $smartBinClearTimes = [];

        /** @var \Illuminate\Support\Collection<int, Device> $devices */
        $devices = Device::with([
                'asset',
                'sensors' => fn($q) => $q->orderBy('time', 'asc')
            ])
            ->whereHas('asset', fn($q) => $q->where('category', 'SmartBin'))
            ->get();

        foreach ($devices as $device) {
            if (!$device->asset) continue;

            $fullTimestamp = null;

            foreach ($device->sensors as $sensor) {
                if ($sensor->capacity > 85 && $fullTimestamp === null) {
                    $fullTimestamp = $sensor->time;
                    continue;
                }

                if ($fullTimestamp && $sensor->capacity <= 40) {
                    $minutes = Carbon::parse($fullTimestamp)
                                     ->diffInMinutes(Carbon::parse($sensor->time));

                    $smartBinClearTimes[] = [
                        'asset_name'  => $device->asset->asset_name,
                        'device_name' => $device->device_name,
                        'minutes'     => $minutes,
                        'hours'       => round($minutes / 60, 2),
                        'cleared_at'  => $sensor->time,
                    ];

                    $fullTimestamp = null;
                }
            }
        }

        return collect($smartBinClearTimes);
    }
}
