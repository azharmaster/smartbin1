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
    $devices = $this->loadDevicesWithLatestSensor();

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
    $smartBinClearTimes = $this->calculateSmartBinClearTimes();

    // Calendar
    $calendarCombined = $this->getCalendarEvents();

    // Today's notifications
    $todayNotifications = $this->getTodayNotifications();

    $deviceStats = $this->getDeviceStats($devices);
    $whatsappNotificationActive = $this->getWhatsappNotificationStatus();

    $abnormalBins = $this->getAbnormalBins();

    // ✅ FULL ASSETS USING ADMIN CAPACITY SETTING & LATEST SENSOR
    $fullAssets = DB::table('devices')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->join('capacity_settings', 'assets.id', '=', 'capacity_settings.asset_id')
        ->join('sensors as s1', 'devices.id_device', '=', 's1.device_id')
        ->whereRaw('s1.created_at = (
            SELECT MAX(s2.created_at)
            FROM sensors s2
            WHERE s2.device_id = s1.device_id
        )')
        ->whereColumn('s1.capacity', '>', 'capacity_settings.half_to')
        ->distinct('assets.id')
        ->count('assets.id');

    return view('dashboard.index', array_merge($deviceStats, [
        
        'todos' => $this->loadTodosForUser(Auth::id()),
        'floors' => Floor::all(),
        'assetsWithCoords' => Asset::whereNotNull('x')->whereNotNull('y')->get(),
        'devices' => $devices,
        'users' => User::all(),
        'assignedTasks' => $this->loadAssignedTasks(),
        'latestComplaints' => $this->loadLatestComplaints(),
        'tasksCompletedPerStaff' => $this->loadTasksCompletedPerStaff(),
        'smartBinClearTimes' => $this->calculateSmartBinClearTimes(),
        'assetsWithDevices' => Asset::with(['devices.sensors'])->whereHas('devices')->orderBy('asset_name')->get(),
        'calendarCombined' => $this->getCalendarEvents(),
        'todayNotifications' => $this->getTodayNotifications(),
        'whatsappNotificationActive'=> $whatsappNotificationActive,
        'abnormalBins' => $abnormalBins,
        'abnormalBinsTrend' => $this->getAbnormalBinsTrend(),

        // ✅ PASS FULL ASSETS TO VIEW
        'fullAssets' => $fullAssets,
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

                if (Carbon::parse($sensor->created_at)->lt(
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
                    $device->last_seen = $sensor->created_at;
                    return true;
                }

                // 🚫 Undetected (no update > threshold)
                if (Carbon::parse($sensor->created_at)->lt($threshold)) {
                    $device->type = 'undetected';
                    $device->last_seen = $sensor->created_at;
                    return true;
                }

                return false;
            })
            ->values();
    }

    /** Device statistics */
private function getDeviceStats($devices): array
{
    $fullDevices  = $this->countFullDevices($devices);
    $halfDevices  = $this->countHalfDevices($devices);
    $emptyDevices = $this->countEmptyDevices($devices);

    // Count undetected separately (no latest sensor or too old)
    $undetectedDevices = $this->countUndetectedDevicesFromAbnormalBins();

    return [
        'totalDevices' => $devices->count(),

        'fullDevicesCollection' => $fullDevices,
        'fullDevices' => $fullDevices->count(),

        'halfDevicesCollection' => $halfDevices,
        'halfDevices' => $halfDevices->count(),

        'emptyDevicesCollection' => $emptyDevices,
        'emptyDevices' => $emptyDevices->count(),

        'undetectedDevices' => $undetectedDevices,
    ];
}

// ------------------- UPDATED CALENDAR METHOD -------------------
/** Combine holidays, events, and notifications for calendar */
private function getCalendarEvents()
{
    $holidays = Holiday::where('is_active', true)->get();
    $events = Event::all();
    $notifications = NotificationLog::with('asset')->get(); // eager load asset

    

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

    // Group notifications by date
$groupedNotifications = $notifications->groupBy(function($n) {
    return Carbon::parse($n->sent_at)->toDateString();
});

// Map grouped notifications into one calendar event per day
$calendarNotifications = $groupedNotifications->map(function($items, $date) {
    // Make notifications unique by message_preview
    $uniqueItems = $items->unique('message_preview')->values();

    return [
        'id' => 'notifications-' . $date,
        'title' => '🔔 ' . $uniqueItems->count() . ' Notifications',
        'start' => $date,
        'allDay' => true,
        'color' => '#ffc107',
        'type' => 'notification_group',
        'notifications' => $uniqueItems->map(function($n) {
            return [
                'message_preview' => $n->message_preview,
            ];
        }),
    ];
})->values();

        return $calendarEvents
            ->toBase()
            ->merge($calendarHolidays)
            ->merge($calendarNotifications)
            ->values();
    }

/** Today's notifications grouped by date */
private function getTodayNotifications()
{
    return NotificationLog::whereDate('sent_at', now()->toDateString())
        ->orderBy('sent_at', 'desc')
        ->get()
        ->groupBy(function($n) {
            return Carbon::parse($n->sent_at)->format('Y-m-d');
        });
}

    /** Load devices with latest sensor and optional before date */
private function loadDevicesWithLatestSensor($before = null)
{
    return Device::with([
        'asset.floor',
        'asset.capacitySetting', 
        'latestSensor' => function ($q) use ($before) {
            if ($before) {
                $q->where('created_at', '<=', $before);
            }
        }
    ])->get();
}


private function countFullDevices($devices)
{
    return $devices->filter(function ($device) {
        $sensor = $device->latestSensor;
        $capacitySetting = $device->asset->capacitySetting ?? null;

        if (!$sensor || !is_numeric($sensor->capacity) || !$capacitySetting) {
            return false; // cannot categorize without reading or capacity settings
        }

        return $sensor->capacity > $capacitySetting->half_to;
    });
}

private function countHalfDevices($devices)
{
    return $devices->filter(function ($device) {
        $sensor = $device->latestSensor;
        $capacitySetting = $device->asset->capacitySetting ?? null;

        if (!$sensor || !is_numeric($sensor->capacity) || !$capacitySetting) {
            return false;
        }

        return $sensor->capacity > $capacitySetting->empty_to
            && $sensor->capacity <= $capacitySetting->half_to;
    });
}

private function countEmptyDevices($devices)
{
    return $devices->filter(function ($device) {
        $sensor = $device->latestSensor;
        $capacitySetting = $device->asset->capacitySetting ?? null;

        if (!$sensor || !is_numeric($sensor->capacity) || !$capacitySetting) {
            return false;
        }

        return $sensor->capacity <= $capacitySetting->empty_to;
    });
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

private function calculateSmartBinClearTimes()
{
    $result = [];

    $startOfWeek = Carbon::now()->startOfWeek(); // Monday 00:00
    $endOfWeek   = Carbon::now()->endOfWeek();   // Sunday 23:59

    $devices = Device::with([
        'asset.capacitySetting', // <-- corrected
        'sensors' => fn ($q) => $q->orderBy('created_at', 'asc')
    ])->get();

    foreach ($devices as $device) {
        if (!$device->asset || !$device->asset->capacitySetting) continue;

        $capacity = $device->asset->capacitySetting;
        $fullTimestamp = null;
        $clears = [];

        foreach ($device->sensors as $sensor) {
            if (!is_numeric($sensor->capacity)) continue;

            // Bin reaches full threshold
            if ($fullTimestamp === null && $sensor->capacity > $capacity->half_to) {
                $fullTimestamp = Carbon::parse($sensor->created_at);
            }

            // Bin clears (drops below empty threshold)
            if ($fullTimestamp && $sensor->capacity <= $capacity->empty_to) {
                $clearTime = Carbon::parse($sensor->created_at);

                if ($clearTime->between($startOfWeek, $endOfWeek)) {
                    $minutes = $fullTimestamp->diffInMinutes($clearTime);
                    $clears[] = [
                        'date'  => $clearTime->format('Y-m-d H:i'),
                        'hours' => round($minutes / 60, 2),
                    ];
                }

                $fullTimestamp = null; // reset for next cycle
            }
        }

        if (!empty($clears)) {
            $result[$device->asset->asset_name][$device->device_name] = $clears;
        }
    }

    return collect($result);
}
}
