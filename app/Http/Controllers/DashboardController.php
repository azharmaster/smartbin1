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
use App\Services\CollectionTripService;

class DashboardController extends Controller
{
    private const CLEAR_HOLD_MINUTES = 60;
    private const DASHBOARD_HISTORY_DAYS = 14;

    public function __construct(private CollectionTripService $collectionTripService)
    {
    }

    public function calendarSummary(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $selectedDate = Carbon::parse($validated['date'])->startOfDay();
        $dateString = $selectedDate->toDateString();
        $collectionTrips = $this->collectionTripService->getTrips($dateString, $dateString);
        $hourlySummary = $this->buildCalendarHourlySummary($collectionTrips);

        return response()->json([
            'date' => $dateString,
            'date_label' => $selectedDate->format('d M Y'),
            'collection_trip_count' => $collectionTrips->count(),
            'full_bin_count' => $this->countFullBinEventsForDate($selectedDate),
            'hourly_labels' => $hourlySummary['labels'],
            'hourly_data' => $hourlySummary['data'],
        ]);
    }

    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
{
    $historyStart = Carbon::now()->subDays(self::DASHBOARD_HISTORY_DAYS);

    // Assets
    $assetsWithDevices = Asset::with([
            'floor',
            'capacitySetting',
            'devices' => function ($q) use ($historyStart) {
                $q->where('is_active', 1)
                    ->with([
                        'sensors' => fn ($query) => $query->where('created_at', '>=', $historyStart)
                            ->orderBy('created_at', 'asc'),
                    ]);
            },
        ])
        ->whereHas('devices', fn ($q) => $q->where('is_active', 1))
        ->where('is_active', 1)
        ->orderBy('asset_name')
        ->get();
    $assetsWithDevices->each(function ($asset) {
        $asset->devices->each(function ($device) use ($asset) {
            $device->setRelation('asset', $asset);
            $device->setRelation('latestSensor', $device->sensors->last());
        });
    });
    $devices = $assetsWithDevices->flatMap->devices->values();
    $assetsWithCoords = $assetsWithDevices
        ->filter(fn ($asset) => $asset->x !== null && $asset->y !== null)
        ->values();

    // Legacy dashboard data kept as empty collections because this view does not render it.
    $todos = collect();
    $floors = collect();
    $latestComplaints = collect();
    $users = collect();
    $assignedTasks = collect();
    $tasksCompletedPerStaff = collect();

    // Smart bin clear times
    $smartBinClearTimes = collect();

    $holidays = Holiday::where('is_active', true)->get();
    $events = Event::all();
    $notificationLogs = NotificationLog::get();

    // Calendar
    $calendarCombined = $this->getCalendarEvents($holidays, $events, $notificationLogs);

    // Today's notifications
    $todayNotifications = $this->getTodayNotifications($notificationLogs);

    // Upcoming holidays and events
    $upcomingHolidaysAndEvents = $this->getUpcomingHolidaysAndEvents($holidays, $events);

    $whatsappNotificationActive = $this->getWhatsappNotificationStatus();

    $abnormalBins = $this->getAbnormalBins(devices: $devices);
    $deviceStats = $this->getDeviceStats($devices, $abnormalBins);

    // Get bin statistics
    $collectionTripToday = $this->collectionTripService
        ->getTripsFromAssets($assetsWithDevices, today()->toDateString(), today()->toDateString())
        ->count();

    $binStatistics = $this->getBinStatistics($devices, $assetsWithDevices, $collectionTripToday);

    // Last emptied times for each asset
    $lastEmptiedTimes = $this->getLastEmptiedTimes($assetsWithDevices);

    // Predicted full times for each asset
    $predictedFullTimes = $this->getPredictedFullTimes($assetsWithDevices);

    $abnormalBinsTrend = $this->getAbnormalBinsTrend(devices: $devices);
    $fullAssets = $binStatistics['fullBins'];

    return view('dashboard.index', array_merge($deviceStats, $binStatistics, [

        'todos' => $todos,
        'floors' => $floors,
        'assetsWithCoords' => $assetsWithCoords,
        'devices' => $devices,
        'users' => $users,
        'assignedTasks' => $assignedTasks,
        'latestComplaints' => $latestComplaints,
        'tasksCompletedPerStaff' => $tasksCompletedPerStaff,
        'smartBinClearTimes' => $smartBinClearTimes,
        'assetsWithDevices' => $assetsWithDevices,
        'calendarCombined' => $calendarCombined,
        'todayNotifications' => $todayNotifications,
        'upcomingHolidaysAndEvents' => $upcomingHolidaysAndEvents,
        'whatsappNotificationActive'=> $whatsappNotificationActive,
        'abnormalBins' => $abnormalBins,
        'abnormalBinsTrend' => $abnormalBinsTrend,

        // ✅ PASS FULL ASSETS TO VIEW
        'fullAssets' => $fullAssets,
        'lastEmptiedTimes' => $lastEmptiedTimes,
        'predictedFullTimes' => $predictedFullTimes,
    ]));
}
    private function getAbnormalBinsTrend($days = 7, $minutesThreshold = 40, ?Collection $devices = null)
    {
        $trend = collect();
        $startDate = Carbon::today()->subDays($days - 1);
        $historyStart = $startDate->copy()->subDay();
        $endDate = Carbon::today()->endOfDay();

        $deviceIds = ($devices ?? Device::with('asset')->get())
            ->filter(fn ($device) =>
                $device->is_active &&
                $device->asset &&
                $device->asset->is_active
            )
            ->pluck('id_device')
            ->values();

        $readingsByDevice = Sensor::select('device_id', 'capacity', 'created_at')
            ->whereIn('device_id', $deviceIds)
            ->whereBetween('created_at', [$historyStart, $endDate])
            ->orderBy('device_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('device_id');

        for ($i = 0; $i < $days; $i++) {

            $date = $startDate->copy()->addDays($i)->toDateString();
            $endOfDay = Carbon::parse($date)->endOfDay();

            $abnormal = 0;
            $undetected = 0;

            foreach ($deviceIds as $deviceId) {

                $sensor = ($readingsByDevice[$deviceId] ?? collect())
                    ->last(fn ($reading) => $reading->created_at->lessThanOrEqualTo($endOfDay));

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

    private function getAbnormalBins($minutesThreshold = 40, ?Collection $devices = null)
    {
        $threshold = Carbon::now()->subMinutes($minutesThreshold);

        return ($devices ?? Device::with(['asset', 'latestSensor'])->get())
            ->filter(fn ($device) =>
                $device->is_active &&
                $device->asset &&
                $device->asset->is_active
            )
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
private function getDeviceStats($devices, ?Collection $abnormalBins = null): array
{
    $fullDevices  = $this->countFullDevices($devices);
    $halfDevices  = $this->countHalfDevices($devices);
    $emptyDevices = $this->countEmptyDevices($devices);

    // Count undetected separately (no latest sensor or too old)
    $undetectedDevices = $this->countUndetectedDevicesFromAbnormalBins(abnormalBins: $abnormalBins);

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

/**
 * Get bin statistics for the dashboard.
 *
 * @return array
 */
private function getBinStatistics(Collection $devices, Collection $assetsWithDevices, int $collectionTripToday): array
{
    $activeDevices = $devices->filter(fn ($device) =>
        $device->is_active &&
        $device->asset &&
        $device->asset->is_active
    );

    $totalBinsInstalled = $assetsWithDevices->count();

    $activeBins = $activeDevices
        ->filter(fn ($device) => $device->latestSensor)
        ->pluck('asset_id')
        ->unique()
        ->count();

    $fullBins = $activeDevices
        ->filter(function ($device) {
            $sensor = $device->latestSensor;
            $setting = $device->asset->capacitySetting ?? null;

            return $sensor &&
                $setting &&
                is_numeric($sensor->capacity) &&
                $sensor->capacity > $setting->half_to;
        })
        ->pluck('asset_id')
        ->unique()
        ->count();

    $undetectBins = $activeDevices
        ->filter(fn ($device) =>
            !$device->latestSensor ||
            !Carbon::parse($device->latestSensor->created_at)->isToday()
        )
        ->pluck('id_device')
        ->unique()
        ->count();

    return [
        'totalBinsInstalled' => $totalBinsInstalled,
        'activeBins' => $activeBins,
        'fullBins' => $fullBins,
        'collectionTripToday' => $collectionTripToday,
        'undetectBins' => $undetectBins,
    ];
}

/**
 * Count bins that were emptied today (same logic as Last Emptied).
 *
 * @return int
 */
    private function getCollectionTripsToday(): int
    {
        return $this->collectionTripService
            ->getTrips(today()->toDateString(), today()->toDateString())
            ->count();
    }

    private function buildCalendarHourlySummary(Collection $collectionTrips): array
    {
        $hourCounts = $collectionTrips
            ->groupBy(fn ($trip) => (int) $trip['emptied_at']->format('G'))
            ->map->count();

        $labels = [];
        $data = [];

        foreach (range(7, 22) as $hour) {
            $labels[] = Carbon::createFromTime($hour, 0)->format('g A');
            $data[] = (int) ($hourCounts[$hour] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function countFullBinEventsForDate(Carbon $selectedDate): int
    {
        $startOfDay = $selectedDate->copy()->startOfDay();
        $endOfDay = $selectedDate->copy()->endOfDay();
        $assets = Asset::with([
            'capacitySetting',
            'devices' => fn ($query) => $query->where('is_active', 1)->orderBy('id_device'),
            'devices.sensors' => fn ($query) => $query->orderBy('created_at', 'asc'),
        ])
            ->where('is_active', 1)
            ->get();

        $fullBinEvents = 0;

        foreach ($assets as $asset) {
            $capacitySetting = $asset->capacitySetting;

            if (!$capacitySetting) {
                continue;
            }

            $allReadings = collect();

            foreach ($asset->devices as $device) {
                foreach ($device->sensors as $sensor) {
                    if (!is_numeric($sensor->capacity)) {
                        continue;
                    }

                    $allReadings->push([
                        'device_id' => $device->id,
                        'capacity' => (float) $sensor->capacity,
                        'created_at' => Carbon::parse($sensor->created_at)->timezone(config('app.timezone')),
                    ]);
                }
            }

            $allReadings = $allReadings->sortBy('created_at')->values();
            $previousCapacities = [];
            $lastClearedAt = null;
            $assetWasFull = false;

            foreach ($allReadings as $reading) {
                $deviceId = $reading['device_id'];
                $currentCapacity = $reading['capacity'];
                $previousCapacity = $previousCapacities[$deviceId] ?? null;
                $readingTime = $reading['created_at'];

                if (
                    $previousCapacity !== null &&
                    $previousCapacity <= $capacitySetting->half_to &&
                    $currentCapacity > $capacitySetting->half_to &&
                    !$assetWasFull
                ) {
                    $assetWasFull = true;

                    if (
                        $readingTime->greaterThanOrEqualTo($startOfDay) &&
                        $readingTime->lessThanOrEqualTo($endOfDay)
                    ) {
                        $fullBinEvents++;
                    }
                }

                if (
                    $previousCapacity !== null &&
                    $previousCapacity > $capacitySetting->empty_to &&
                    $this->isCollectionCapacity($currentCapacity, (float) $capacitySetting->empty_to) &&
                    !$this->isClearHoldActive($lastClearedAt, $readingTime)
                ) {
                    $lastClearedAt = $readingTime;
                    $assetWasFull = false;
                }

                $previousCapacities[$deviceId] = $currentCapacity;
            }
        }

        return $fullBinEvents;
    }

// ------------------- UPDATED CALENDAR METHOD -------------------
/** Combine holidays, events, and notifications for calendar */
private function getCalendarEvents(?Collection $holidays = null, ?Collection $events = null, ?Collection $notifications = null)
{
    $holidays ??= Holiday::where('is_active', true)->get();
    $events ??= Event::all();
    $notifications ??= NotificationLog::get();

    

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
private function getTodayNotifications(?Collection $notifications = null)
{
    return ($notifications ?? NotificationLog::whereDate('sent_at', now()->toDateString())->get())
        ->filter(fn ($notification) => Carbon::parse($notification->sent_at)->isToday())
        ->sortByDesc('sent_at')
        ->groupBy(function($n) {
            return Carbon::parse($n->sent_at)->format('Y-m-d');
        });
}

/** Get upcoming holidays and events (starting within next 7 days) */
private function getUpcomingHolidaysAndEvents(?Collection $holidays = null, ?Collection $events = null)
{
    $today = Carbon::today();
    $nextWeek = Carbon::today()->addDays(7);

    $upcomingItems = collect();

    // Upcoming holidays
    $holidays = ($holidays ?? Holiday::where('is_active', true)->get())
        ->filter(fn ($holiday) => Carbon::parse($holiday->start_date)->betweenIncluded($today, $nextWeek))
        ->sortBy('start_date');

    foreach ($holidays as $holiday) {
        $upcomingItems->push([
            'type' => 'holiday',
            'name' => $holiday->name,
            'start_date' => $holiday->start_date,
            'end_date' => $holiday->end_date,
        ]);
    }

    // Upcoming events
    $events = ($events ?? Event::all())
        ->filter(fn ($event) => Carbon::parse($event->start_date)->betweenIncluded($today, $nextWeek))
        ->sortBy('start_date');

    foreach ($events as $event) {
        $upcomingItems->push([
            'type' => 'event',
            'name' => $event->event_name,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'pic_phone' => $event->pic_phone,
        ]);
    }

    // Sort by start date
    return $upcomingItems->sortBy('start_date')->values();
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

    private function countUndetectedDevicesFromAbnormalBins($minutesThreshold = 40, ?Collection $abnormalBins = null)
    {
        return ($abnormalBins ?? $this->getAbnormalBins($minutesThreshold))
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
    $historyStart = $startOfWeek->copy()->subDay();

    $devices = Device::with([
        'asset.capacitySetting', // <-- corrected
        'sensors' => fn ($q) => $q->where('created_at', '>=', $historyStart)
            ->where('created_at', '<=', $endOfWeek)
            ->orderBy('created_at', 'asc')
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

/**
 * Get the last emptied time for each asset.
 *
 * @return \Illuminate\Support\Collection
 */
private function getLastEmptiedTimes(Collection $assets)
{
    $result = [];

    foreach ($assets as $asset) {
        if (!$asset->capacitySetting) continue;

        $assetId = $asset->id;

        // Gabungkan semua sensor readings dari semua compartments, sort by time ASC
        $allSensorReadings = collect();
        foreach ($asset->devices as $device) {
            foreach ($device->sensors as $sensor) {
                if (!is_numeric($sensor->capacity)) continue;
                $allSensorReadings->push([
                    'device_id'  => $device->id,
                    'capacity'   => (float) $sensor->capacity,
                    'created_at' => Carbon::parse($sensor->created_at),
                ]);
            }
        }

        $allSensorReadings = $allSensorReadings->sortBy('created_at')->values();

        $previousCapacities = [];
        $lastClearedAt = null;
        $lastClearTime      = null;
        $currentDay         = null;
        $emptyTo            = (float) $asset->capacitySetting->empty_to;

        foreach ($allSensorReadings as $reading) {
            $deviceId    = $reading['device_id'];
            $currentCap  = $reading['capacity'];
            $previousCap = $previousCapacities[$deviceId] ?? null;
            $readingTime = $reading['created_at'];
            $readingDay  = $readingTime->format('Y-m-d');

            if ($currentDay !== null && $currentDay !== $readingDay) {
                $previousCapacities = [];
                $lastClearedAt = null;
            }
            $currentDay = $readingDay;

            // Check ada compartment yang turun dari half/full ke empty.
            if (
                $previousCap !== null &&
                $previousCap > $emptyTo &&
                $this->isCollectionCapacity($currentCap, $emptyTo) &&
                !$this->isClearHoldActive($lastClearedAt, $readingTime)
            ) {
                $lastClearedAt = $readingTime;

                if ($this->isWithinCollectionWindow($readingTime)) {
                    $lastClearTime = $readingTime;
                }
            }

            $previousCapacities[$deviceId] = $currentCap;
        }

        // Last Emptied = timestamp clear event paling recent
        $result[$assetId] = $lastClearTime;
    }

    return collect($result);
}

private function isWithinCollectionWindow(Carbon $timestamp): bool
{
    $minutes = ($timestamp->hour * 60) + $timestamp->minute;

    return $minutes >= 420 && $minutes <= 1320;
}

private function isCollectionCapacity(float $capacity, float $emptyTo): bool
{
    return $capacity <= $emptyTo;
}

private function isClearHoldActive(?Carbon $lastClearedAt, Carbon $readingTime): bool
{
    return $lastClearedAt !== null &&
        $lastClearedAt->copy()->addMinutes(self::CLEAR_HOLD_MINUTES)->greaterThan($readingTime);
}

/**
 * Get predicted full time for each asset based on fill rate.
 *
 * @return \Illuminate\Support\Collection
 */
private function getPredictedFullTimes(Collection $assets)
{
    $result = [];

    foreach ($assets as $asset) {
        if (!$asset->capacitySetting) continue;

        $assetId = $asset->id;
        $capacity = $asset->capacitySetting;

        foreach ($asset->devices as $device) {
            $sensors = $device->sensors
                ->filter(fn($s) => is_numeric($s->capacity))
                ->sortByDesc('created_at')
                ->take(10)
                ->values();

            // Need at least 2 sensor readings to calculate fill rate
            if ($sensors->count() < 2) continue;

            // Get the latest sensor reading
            $latestSensor = $sensors->first();
            $currentCapacity = $latestSensor->capacity;

            // Skip if already full
            if ($currentCapacity > $capacity->half_to) continue;

            // Calculate fill rate (capacity change per hour)
            $oldestSensor = $sensors->last();
            $timeDiffHours = max(1, Carbon::parse($oldestSensor->created_at)->diffInMinutes($latestSensor->created_at) / 60);
            $capacityChange = $currentCapacity - $oldestSensor->capacity;
            $fillRatePerHour = $capacityChange / $timeDiffHours;

            // Skip if not filling (negative or zero rate)
            if ($fillRatePerHour <= 0) continue;

            // Calculate hours until full
            $remainingCapacity = $capacity->half_to - $currentCapacity;
            $hoursUntilFull = $remainingCapacity / $fillRatePerHour;

            // Predicted full time
            $predictedFullTime = Carbon::now()->addHours($hoursUntilFull);

            // Store if this is the earliest predicted full time for this asset
            if (!isset($result[$assetId]) || $predictedFullTime < $result[$assetId]) {
                $result[$assetId] = $predictedFullTime;
            }
        }
    }

    return collect($result);
}
}
