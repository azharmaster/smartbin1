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
use App\Models\Holiday;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\NotificationLog;

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

        $assetsWithDevices = Asset::with(['devices.sensors'])
            ->whereHas('devices')
            ->orderBy('asset_name')
            ->get();

        //Holidays
        $holidays = Holiday::where('is_active', true)->get();

        $calendarHolidays = $holidays->map(function ($holiday) {
            return [
                'title'  => '🎉 ' . $holiday->name,
                'start'  => $holiday->start_date,
                'end'    => $holiday->end_date
                    ? Carbon::parse($holiday->end_date)->addDay()->toDateString()
                    : null,
                'allDay' => true,
                'color'  => '#dc3545',
                'type'   => 'holiday',
            ];
        });

        // 📅 Events
        $events = Event::all();

        $calendarEvents = $events->map(function ($e) {
            return [
                'id'     => $e->id,
                'title'  => $e->event_name,
                'start'  => $e->start_date . 'T' . $e->start_time,
                'end'    => $e->end_date . 'T' . $e->end_time,
                'color'  => '#28a745',
                'allDay' => false,
                'type'   => 'event',
            ];
        });

        // 🔥 Combine both
        $calendarCombined = $calendarEvents
            ->merge($calendarHolidays)
            ->values();

        $todayNotifications = NotificationLog::whereDate('sent_at', now()->toDateString())
            ->orderBy('sent_at', 'desc')
            ->get();

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
            'totalTrend',
            'todayNotifications',
            'assetsWithDevices',
            'calendarCombined',
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

    private function calculateSmartBinClearTimes($emptyMax, $halfMax)
    {
        $result = [];

        $devices = Device::with([
            'asset',
            'sensors' => fn ($q) => $q->orderBy('time', 'asc')
        ])->get();

        foreach ($devices as $device) {
            if (!$device->asset) continue;

            $fullTimestamp = null;
            $clears = [];

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

                    $clears[] = [
                        'date'  => Carbon::parse($sensor->time)->format('Y-m-d H:i'),
                        'hours' => round($minutes / 60, 2),
                    ];

                    $fullTimestamp = null;
                }
            }

            $clears = collect($clears)->take(-10)->values();

            if ($clears->isNotEmpty()) {
                $result[$device->asset->asset_name][$device->device_name] = $clears;
            }
        }

        return collect($result);
    }
}
