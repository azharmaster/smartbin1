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
use App\Models\Sensor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\NotificationLog;
use App\Models\WhatsAppNotification;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Load devices and capacity settings
        $devices = $this->loadDevicesWithLatestSensor();
        $capacity = CapacitySetting::first();
        $emptyMax = $capacity->empty_to;
        $halfMax  = $capacity->half_to;

        // Device statistics
        $deviceStats = $this->getDeviceStats($devices, $emptyMax, $halfMax);

        // Trend calculation
        //$trendStats = $this->getTrendStats($devices, $emptyMax, $halfMax);

        // Floors and assets
        $floors = Floor::all();
        $assetsWithCoords = Asset::whereNotNull('x')
                                ->whereNotNull('y')
                                ->get();
        $assetsWithDevices = Asset::with(['devices.sensors'])
            ->whereHas('devices')
            ->orderBy('asset_name')
            ->get();

        // Todos and tasks
        $todos = $this->loadTodosForUser(Auth::id());
        $latestComplaints = $this->loadLatestComplaints();
        $users = User::all();
        $assignedTasks = $this->loadAssignedTasks();
        $tasksCompletedPerStaff = $this->loadTasksCompletedPerStaff();

        // Smart bin clear times
        $smartBinClearTimes = $this->calculateSmartBinClearTimes($emptyMax, $halfMax);

        // Calendar
        $calendarCombined = $this->getCalendarEvents();

        // Today's notifications
        $todayNotifications = $this->getTodayNotifications();

        $deviceStats = $this->getDeviceStats($devices, $emptyMax, $halfMax);
        //$trendStats  = $this->getTrendStats($devices, $emptyMax, $halfMax);
        $whatsappNotificationActive = $this->getWhatsappNotificationStatus();

        $abnormalBins = $this->getAbnormalBins();
//'totalTrend' => $trendStats['totalTrend'],
        return view('dashboard.index', array_merge($deviceStats, [
            
            'todos' => $this->loadTodosForUser(Auth::id()),
            'floors' => Floor::all(),
            'assetsWithCoords' => Asset::whereNotNull('x')->whereNotNull('y')->get(),
            'devices' => $devices,
            'users' => User::all(),
            'assignedTasks' => $this->loadAssignedTasks(),
            'latestComplaints' => $this->loadLatestComplaints(),
            'tasksCompletedPerStaff' => $this->loadTasksCompletedPerStaff(),
            'smartBinClearTimes' => $this->calculateSmartBinClearTimes($emptyMax, $halfMax),
            'assetsWithDevices' => Asset::with(['devices.sensors'])->whereHas('devices')->orderBy('asset_name')->get(),
            'calendarCombined' => $this->getCalendarEvents(),
            'todayNotifications' => $this->getTodayNotifications(),
            'whatsappNotificationActive'=> $whatsappNotificationActive,
            'abnormalBins' => $abnormalBins,
            'emptyMax' => $emptyMax,
            'halfMax'  => $halfMax,
            'abnormalBinsTrend' => $this->getAbnormalBinsTrend(),
        ]));
    }

    private function getAbnormalBinsTrend($days = 7, $minutesThreshold = 40)
    {
        $trend = collect();
        $startDate = Carbon::today()->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {

            $date = $startDate->copy()->addDays($i)->toDateString();
            $endOfDay = Carbon::parse($date)->endOfDay();

            $devices = Device::with([
                'asset',
                'latestSensor' => function ($q) use ($endOfDay) {
                    $q->where('time', '<=', $endOfDay);
                }
            ])
            ->where('is_active', 1)
            ->whereHas('asset', fn ($q) => $q->where('is_active', 1))
            ->get();

            $abnormal = 0;
            $undetected = 0;

            foreach ($devices as $device) {

                $sensor = $device->latestSensor;

                if (!$sensor) {
                    $undetected++;
                    continue;
                }

                if (is_numeric($sensor->capacity) && $sensor->capacity < 0) {
                    $abnormal++;
                    continue;
                }

                if (Carbon::parse($sensor->time)->lt(
                    Carbon::parse($endOfDay)->subMinutes($minutesThreshold)
                )) {
                    $undetected++;
                }
            }

            $trend->push([
                'date' => $date,
                'abnormal' => $abnormal,
                'undetected' => $undetected,
            ]);
        }

        return $trend;
    }

    private function getAbnormalBins($minutesThreshold = 40)
    {
        $threshold = Carbon::now()->subMinutes($minutesThreshold);

        return Device::with(['asset', 'latestSensor'])
            ->where('is_active', 1)
            ->whereHas('asset', fn ($q) => $q->where('is_active', 1))
            ->get()
            ->filter(function ($device) use ($threshold) {

                $sensor = $device->latestSensor;

                // ❌ No sensor at all → undetected
                if (!$sensor) {
                    $device->type = 'undetected';
                    $device->last_seen = null;
                    return true;
                }

                // ⚠️ Abnormal
                if (is_numeric($sensor->capacity) && $sensor->capacity < 0) {
                    $device->type = 'abnormal';
                    $device->last_seen = $sensor->time;
                    return true;
                }

                // 🚫 Undetected (no update > threshold)
                if (Carbon::parse($sensor->time)->lt($threshold)) {
                    $device->type = 'undetected';
                    $device->last_seen = $sensor->time;
                    return true;
                }

                return false;
            })
            ->values();
    }

    /** Device statistics */
    private function getDeviceStats($devices, $emptyMax, $halfMax): array
    {
        return [
            'totalDevices' => $devices->count(),
            'fullDevicesCollection' => $this->countFullDevices($devices, $halfMax),
            'fullDevices' => $this->countFullDevices($devices, $halfMax)->count(),
            'halfDevicesCollection' => $this->countHalfDevices($devices, $emptyMax, $halfMax),
            'halfDevices' => $this->countHalfDevices($devices, $emptyMax, $halfMax)->count(),
            'emptyDevices' => $this->countEmptyDevices($devices, $emptyMax),
            'undetectedDevices' => $this->countUndetectedDevicesFromAbnormalBins(),
        ];
    }

    /** Trend calculation */
    // private function getTrendStats($currentDevices, $emptyMax, $halfMax): array
    // {
    //     $lastMonth = Carbon::now()->subMonth();
    //     $previousDevices = $this->loadDevicesWithLatestSensor($lastMonth);

    //     $trend = fn($current, $previous) => [
    //         'icon'  => $current > $previous ? '▲' : ($current < $previous ? '▼' : '—'),
    //         'value' => abs($current - $previous),
    //         'class' => $current > $previous ? 'text-success' : ($current < $previous ? 'text-danger' : 'text-muted'),
    //     ];

    //     $totalTrend = $trend($currentDevices->count(), $previousDevices->count());

    //     return [
    //         'totalTrend' => $totalTrend,
    //         'currentTotal' => $currentDevices->count(),
    //         'previousTotal' => $previousDevices->count(),
    //         'currentFull' => $this->countFullDevices($currentDevices, $halfMax)->count(),
    //         'previousFull' => $this->countFullDevices($previousDevices, $halfMax)->count(),
    //         'currentHalf' => $this->countHalfDevices($currentDevices, $emptyMax, $halfMax)->count(),
    //         'previousHalf' => $this->countHalfDevices($previousDevices, $emptyMax, $halfMax)->count(),
    //         'currentEmpty' => $this->countEmptyDevices($currentDevices, $emptyMax),
    //         'previousEmpty' => $this->countEmptyDevices($previousDevices, $emptyMax),
    //         'currentUndetected' => $this->countUndetectedDevices($currentDevices),
    //         'previousUndetected' => $this->countUndetectedDevices($previousDevices),
    //     ];
    // }

    /** Combine holidays and events for calendar */
    private function getCalendarEvents()
    {
        $holidays = Holiday::where('is_active', true)->get();
        $events = Event::all();

        $calendarHolidays = $holidays->map(function ($holiday) {
            $start = Carbon::parse($holiday->start_date)->format('Y-m-d');
            $end = $holiday->end_date
                ? Carbon::parse($holiday->end_date)->addDay()->format('Y-m-d')
                : $start;

            return [
                'title' => '🎉 ' . $holiday->name,
                'start' => $start,
                'end'   => $end,
                'allDay' => true,
                'color' => '#dc3545',
                'type' => 'holiday',
            ];
        });

        $calendarEvents = $events->map(function ($e) {
            return [
                'id' => $e->id,
                'title' => $e->event_name,
                'start' => $e->start_date,
                'end' => $e->end_date ?? $e->start_date,
                'allDay' => true,
                'color' => '#28a745',
                'type' => 'event',
                'pic_phone' => $e->pic_phone,
                'location' => $e->location,
            ];
        });

        return $calendarEvents
            ->toBase()
            ->merge($calendarHolidays)
            ->values();
    }

    /** Today's notifications */
    private function getTodayNotifications()
    {
        return NotificationLog::whereDate('sent_at', now()->toDateString())
            ->orderBy('sent_at', 'desc')
            ->get();
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

    // private function countUndetectedDevices($devices)
    // {
    //     return $devices->filter(function($d) {
    //         if (!$d->latestSensor) return true;
    //         $network = $d->latestSensor->network;
    //         return is_null($network)
    //             || $network === ''
    //             || (string)$network === '0'
    //             || strtolower((string)$network) === 'unavailable';
    //     })->count();
    // }

    private function countUndetectedDevicesFromAbnormalBins($minutesThreshold = 40)
    {
        return $this->getAbnormalBins($minutesThreshold)
            ->where('type', 'undetected')
            ->count();
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

    // Get the current notification status
    private function getWhatsappNotificationStatus()
    {
        $notif = WhatsappNotification::first(); // assuming only one row
        return $notif ? $notif->is_active : false;
    }

    // Toggle the notification status
    public function toggleWhatsappNotification()
    {
        $notif = WhatsappNotification::first();

        if (!$notif) {
            // If the row doesn't exist, create it with is_active = true
            $notif = WhatsappNotification::create(['is_active' => true]);
        } else {
            // Toggle the value
            $notif->is_active = !$notif->is_active;
            $notif->save();
        }

        return response()->json([
            'success' => true,
            'is_active' => $notif->is_active,
        ]);
    }

private function calculateSmartBinClearTimes($emptyMax, $halfMax)
{
    $result = [];

    $startOfWeek = Carbon::now()->startOfWeek(); // Monday 00:00
    $endOfWeek   = Carbon::now()->endOfWeek();   // Sunday 23:59

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

                $clearTime = Carbon::parse($sensor->time);

                // Only include clears in the current week
                if ($clearTime->between($startOfWeek, $endOfWeek)) {
                    $minutes = Carbon::parse($fullTimestamp)
                        ->diffInMinutes($clearTime);

                    $clears[] = [
                        'date'  => $clearTime->format('Y-m-d H:i'),
                        'hours' => round($minutes / 60, 2),
                    ];
                }

                $fullTimestamp = null;
            }
        }

        if (!empty($clears)) {
            $result[$device->asset->asset_name][$device->device_name] = $clears;
        }
    }

    return collect($result);
}
}
