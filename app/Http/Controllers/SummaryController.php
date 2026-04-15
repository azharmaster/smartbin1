<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;
use App\Models\CapacitySetting;
use Illuminate\Support\Facades\Mail;
use App\Mail\SummaryReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use QuickChart\Quickchart;
use Throwable;
use App\Services\CollectionTripService;

class SummaryController extends Controller
{
    public function __construct(private CollectionTripService $collectionTripService)
    {
    }

private function _getCapacityStats(Carbon $baseDate, string $period): object
{
    [$start, $end] = $this->resolveDateRange($baseDate, $period);
    
        $assets = Asset::with([
        'capacitySetting',
        'devices' => fn($q) => $q->where('is_active', 1),
        'devices.sensors' => fn($q) => $q->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->limit(1),
    ])->where('is_active', 1)->get();

    $empty = $half = $full = 0;

    foreach ($assets as $asset) {
        $setting = $asset->capacitySetting;
        if (!$setting) continue;

        foreach ($asset->devices as $device) {
            $sensor = $device->sensors->first();
            if (!$sensor || !is_numeric($sensor->capacity)) continue;

            if ($this->isEmptyCapacity((float) $sensor->capacity)) {
                $empty++;
            } elseif ($this->isFullCapacity((float) $sensor->capacity)) {
                $full++;
            } else {
                $half++;
            }
        }
    }

    return (object) [
        'empty_count' => $empty,
        'half_count'  => $half,
        'full_count'  => $full,
    ];
}

    public function getCapacityStats(Carbon $baseDate, string $period): object
    {
        return $this->_getCapacityStats($baseDate, $period);
    }

    private function _getDevicesByFloor()
    {
        return DB::table('assets')
            ->join('floor', 'assets.floor_id', '=', 'floor.id')
            ->join('devices', 'devices.asset_id', '=', 'assets.id')
            ->select('floor.floor_name', DB::raw('COUNT(devices.id_device) as total'))
            ->groupBy('floor.floor_name')
            ->get();
    }

    public function getDevicesByFloor()
    {
        return $this->_getDevicesByFloor();
    }

    private function _computeBinAnalyticsPerAsset(Carbon $baseDate, string $period)
    {
        [$start, $end] = $this->resolveDateRange($baseDate, $period);

        $assets = Asset::with([
            'capacitySetting',
            'devices' => function ($q) {
                $q->orderBy('id_device');
            }
        ])->where('is_active', 1)->get();

        $results = [];

        foreach ($assets as $asset) {

            $setting = $asset->capacitySetting;

            if (!$setting) {
                $results[] = (object) [
                    'asset_name'     => $asset->asset_name,
                    'times_full'     => 0,
                    'times_empty'    => 0,
                    'avg_fill_time'  => 0,
                    'avg_clear_time' => 0,
                    'avg_empty_time' => 0,
                ];
                continue;
            }

            // Gabungkan semua sensor readings dari semua devices, sort by time ASC
            $allReadings = collect();
            foreach ($asset->devices as $device) {
                $sensors = DB::table('sensors')
                    ->select('capacity', 'created_at')
                    ->where('device_id', $device->id_device)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($sensors as $s) {
                    if (!is_numeric($s->capacity)) continue;
                    $allReadings->push([
                        'device_id'  => $device->id,
                        'capacity'   => (float) $s->capacity,
                        'created_at' => Carbon::parse($s->created_at),
                    ]);
                }
            }
            $allReadings = $allReadings->sortBy('created_at')->values();

            $previousCapacities = [];
            $binCleared         = false;
            $triggeredDeviceId  = null;
            $currentDay         = null;

            $timesFull      = 0;
            $timesEmpty     = 0;
            $fillDurations  = [];
            $clearDurations = [];
            $lastClearAt    = null;
            $lastFullAt     = null;

            // Track full per asset (any device goes full)
            $assetWasFull = false;

            foreach ($allReadings as $reading) {
                $deviceId    = $reading['device_id'];
                $currentCap  = $reading['capacity'];
                $previousCap = $previousCapacities[$deviceId] ?? null;
                $readingTime = $reading['created_at'];
                $readingDay  = $readingTime->format('Y-m-d');

                if ($currentDay !== null && $currentDay !== $readingDay) {
                    $binCleared = false;
                    $triggeredDeviceId = null;
                    $previousCapacities = [];
                }
                $currentDay = $readingDay;

                // Full detection (per asset): transition from 0-79 to 80+
                if (
                    $previousCap !== null &&
                    !$this->isFullCapacity($previousCap) &&
                    $this->isFullCapacity($currentCap)
                ) {
                    if (!$assetWasFull) {
                        $assetWasFull = true;
                        $timesFull++;
                        $lastFullAt = $readingTime;

                        // Fill time = from last clear to now full
                        if ($lastClearAt) {
                            $fillDurations[] = $lastClearAt->diffInMinutes($readingTime) / 60;
                        }
                    }
                }

                // Clear Bin detection (per asset, new logic)
                if (!$binCleared) {
                    if (
                        $previousCap !== null &&
                        !$this->isEmptyCapacity($previousCap) &&
                        $this->isCollectionCapacity($currentCap)
                    ) {
                        $binCleared        = true;
                        $triggeredDeviceId = $deviceId;
                        $assetWasFull      = false; // reset full flag after clear

                        // Only count clear time if within period
                        if ($this->isWithinCollectionWindow($readingTime) && $readingTime->between($start, $end)) {
                            $timesEmpty++;
                            $lastClearAt = $readingTime;

                            // Clear time = from last full to now cleared
                            if ($lastFullAt) {
                                $clearDurations[] = $lastFullAt->diffInMinutes($readingTime) / 60;
                                $lastFullAt = null;
                            }
                        }
                    }
                } else {
                    // Tunggu triggered compartment naik balik >10%
                    if ($deviceId === $triggeredDeviceId && !$this->isEmptyCapacity($currentCap)) {
                        $binCleared        = false;
                        $triggeredDeviceId = null;
                    }
                }

                $previousCapacities[$deviceId] = $currentCap;
            }

            $results[] = (object) [
                'asset_name'     => $asset->asset_name,
                'times_full'     => $timesFull,
                'times_empty'    => $timesEmpty,
                'avg_fill_time'  => count($fillDurations)
                    ? round(array_sum($fillDurations) / count($fillDurations), 2)
                    : 0,
                'avg_clear_time' => count($clearDurations)
                    ? round(array_sum($clearDurations) / count($clearDurations), 2)
                    : 0,
                'avg_empty_time' => count($clearDurations)
                    ? round(array_sum($clearDurations) / count($clearDurations), 2)
                    : 0,
            ];
        }

        return collect($results);
    }

    public function computeBinAnalyticsPerAsset(Carbon $baseDate, string $period)
    {
        return $this->_computeBinAnalyticsPerAsset($baseDate, $period);
    }

    private function _getCleaningLogs(Carbon $baseDate, string $period)
    {
        [$start, $end] = $this->resolveDateRange($baseDate, $period);
        return $this->getCleaningLogsForRange($start, $end);
    }

    public function getCleaningLogs(Carbon $baseDate, string $period)
    {
        return $this->_getCleaningLogs($baseDate, $period);
    }

    private function getCollectionTripsForRange(Carbon $startDate, Carbon $endDate)
    {
        return $this->collectionTripService
            ->getTrips($startDate->toDateString(), $endDate->toDateString())
            ->filter(fn ($trip) => $trip['emptied_at']->betweenIncluded($startDate, $endDate))
            ->values();
    }

    private function getAssets()
    {
        return Asset::with('floor')
            ->where('is_active', 1)
            ->get();
    }

    public function getAssetsPublic()
    {
        return $this->getAssets();
    }

    private function resolveDateRange(Carbon $baseDate, string $period)
    {
        if ($period === 'today') {
            return [
                $baseDate->copy()->startOfDay(),
                $baseDate->copy()->endOfDay(),
            ];
        }

        if ($period === 'week') {
            return [
                $baseDate->copy()->startOfWeek(),
                $baseDate->copy()->endOfWeek(),
            ];
        }

        return [
            $baseDate->copy()->startOfMonth(),
            $baseDate->copy()->endOfMonth(),
        ];
    }

private function _computeSummaryMetrics(Carbon $baseDate, string $period)
{
    $binAnalytics = $this->_computeBinAnalyticsPerAsset($baseDate, $period);
    $cleaningLogs = $this->_getCleaningLogs($baseDate, $period);

    // Total full events across all bins
    $totalFullEvents = $binAnalytics->sum('times_full');

    // Average fill time across all bins (only bins with fill time > 0)
    $fillTimes = $binAnalytics->filter(fn($item) => $item->avg_fill_time > 0);
    $avgFillTime = $fillTimes->count() > 0
        ? round($fillTimes->avg('avg_fill_time'), 2)
        : 0;

    // Average clear time across all bins (only bins with clear time > 0)
    $clearTimes = $binAnalytics->filter(fn($item) => $item->avg_clear_time > 0);
    $avgClearTime = $clearTimes->count() > 0
        ? round($clearTimes->avg('avg_clear_time'), 2)
        : 0;

    // Total cleaning events
    $totalCleaning = $cleaningLogs->count();

    // Total active bins (is_active = 1, with at least one sensor reading in the period)
    [$start, $end] = $this->resolveDateRange($baseDate, $period);
    $activeBins = DB::table('devices')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->join('sensors', 'sensors.device_id', '=', 'devices.id_device')
        ->where('assets.is_active', 1)
        ->where('devices.is_active', 1)
        ->whereBetween('sensors.created_at', [$start, $end])
        ->distinct('assets.id')
        ->count('assets.id');

    return (object) [
        'total_full_events' => $totalFullEvents,
        'avg_fill_time'     => $avgFillTime,
        'avg_clear_time'    => $avgClearTime,
        'total_cleaning'    => $totalCleaning,
        'total_active_bins' => $activeBins,
    ];
}

public function computeSummaryMetrics(Carbon $baseDate, string $period)
{
    return $this->_computeSummaryMetrics($baseDate, $period);
}

public function computeBinAnalyticsForRange(Carbon $startDate, Carbon $endDate)
{
    $assets = Asset::with([
        'capacitySetting',
        'devices' => function ($q) {
            $q->orderBy('id_device');
        }
    ])->where('is_active', 1)->get();

    $results = [];

    foreach ($assets as $asset) {
            $setting = $asset->capacitySetting;

        if (!$setting) {
            $results[] = (object) [
                'asset_name'     => $asset->asset_name,
                'times_full'     => 0,
                'times_empty'    => 0,
                'avg_fill_time'  => 0,
                'avg_clear_time' => 0,
                'avg_empty_time' => 0,
            ];
            continue;
        }

        $allReadings = collect();
        foreach ($asset->devices as $device) {
            $sensors = DB::table('sensors')
                ->select('capacity', 'created_at')
                ->where('device_id', $device->id_device)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($sensors as $sensor) {
                if (!is_numeric($sensor->capacity)) {
                    continue;
                }

                $allReadings->push([
                    'device_id'  => $device->id,
                    'capacity'   => (float) $sensor->capacity,
                    'created_at' => Carbon::parse($sensor->created_at),
                ]);
            }
        }

        $allReadings = $allReadings->sortBy('created_at')->values();

        $previousCapacities = [];
        $binCleared = false;
        $triggeredDeviceId = null;
        $currentDay = null;

        $timesFull = 0;
        $timesEmpty = 0;
        $fillDurations = [];
        $clearDurations = [];
        $lastClearAt = null;
        $lastFullAt = null;
        $assetWasFull = false;

        foreach ($allReadings as $reading) {
            $deviceId = $reading['device_id'];
            $currentCap = $reading['capacity'];
            $previousCap = $previousCapacities[$deviceId] ?? null;
            $readingTime = $reading['created_at'];
            $readingDay = $readingTime->format('Y-m-d');

            if ($currentDay !== null && $currentDay !== $readingDay) {
                $binCleared = false;
                $triggeredDeviceId = null;
                $previousCapacities = [];
            }
            $currentDay = $readingDay;

            if (
                $previousCap !== null &&
                !$this->isFullCapacity($previousCap) &&
                $this->isFullCapacity($currentCap)
            ) {
                if (!$assetWasFull) {
                    $assetWasFull = true;

                    if ($readingTime->between($startDate, $endDate)) {
                        $timesFull++;
                        $lastFullAt = $readingTime;

                        if ($lastClearAt) {
                            $fillDurations[] = $lastClearAt->diffInMinutes($readingTime) / 60;
                        }
                    }
                }
            }

            if (!$binCleared) {
                if (
                    $previousCap !== null &&
                    !$this->isEmptyCapacity($previousCap) &&
                    $this->isCollectionCapacity($currentCap)
                ) {
                    $binCleared = true;
                    $triggeredDeviceId = $deviceId;
                    $assetWasFull = false;

                    if ($this->isWithinCollectionWindow($readingTime) && $readingTime->between($startDate, $endDate)) {
                        $timesEmpty++;
                        $lastClearAt = $readingTime;

                        if ($lastFullAt) {
                            $clearDurations[] = $lastFullAt->diffInMinutes($readingTime) / 60;
                            $lastFullAt = null;
                        }
                    }
                }
            } else {
                if ($deviceId === $triggeredDeviceId && !$this->isEmptyCapacity($currentCap)) {
                    $binCleared = false;
                    $triggeredDeviceId = null;
                }
            }

            $previousCapacities[$deviceId] = $currentCap;
        }

        $results[] = (object) [
            'asset_name'     => $asset->asset_name,
            'times_full'     => $timesFull,
            'times_empty'    => $timesEmpty,
            'avg_fill_time'  => count($fillDurations) ? round(array_sum($fillDurations) / count($fillDurations), 2) : 0,
            'avg_clear_time' => count($clearDurations) ? round(array_sum($clearDurations) / count($clearDurations), 2) : 0,
            'avg_empty_time' => count($clearDurations) ? round(array_sum($clearDurations) / count($clearDurations), 2) : 0,
        ];
    }

    return collect($results);
}

public function getCleaningLogsForRange(Carbon $startDate, Carbon $endDate)
{
    return $this->getCollectionTripsForRange($startDate, $endDate)
        ->map(fn ($trip) => (object) [
            'asset_name' => $trip['asset_name'],
            'device_name' => $trip['device_name'],
            'cleaned_at' => $trip['emptied_at'],
        ])
        ->values();
}

public function getSummaryForRange(Carbon $startDate, Carbon $endDate): object
{
    $binAnalytics = $this->computeBinAnalyticsForRange($startDate, $endDate);
    $cleaningLogs = $this->getCleaningLogsForRange($startDate, $endDate);

    $fillTimes = $binAnalytics->filter(fn($item) => $item->avg_fill_time > 0);
    $clearTimes = $binAnalytics->filter(fn($item) => $item->avg_clear_time > 0);
    $topBins = $binAnalytics
        ->filter(fn($item) => $item->times_full > 0)
        ->sortByDesc('times_full')
        ->take(5)
        ->values();

    $activeBins = DB::table('devices')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->join('sensors', 'sensors.device_id', '=', 'devices.id_device')
        ->where('assets.is_active', 1)
        ->where('devices.is_active', 1)
        ->whereBetween('sensors.created_at', [$startDate, $endDate])
        ->distinct('assets.id')
        ->count('assets.id');

    return (object) [
        'summary_metrics' => (object) [
            'total_full_events' => $binAnalytics->sum('times_full'),
            'avg_fill_time' => $fillTimes->count() > 0 ? round($fillTimes->avg('avg_fill_time'), 2) : 0,
            'avg_clear_time' => $clearTimes->count() > 0 ? round($clearTimes->avg('avg_clear_time'), 2) : 0,
            'total_cleaning' => $cleaningLogs->count(),
            'total_active_bins' => $activeBins,
        ],
        'bin_analytics' => $binAnalytics->values(),
        'cleaning_logs' => $cleaningLogs,
        'top_bins' => $topBins,
    ];
}

private function _computeMonthInsights(Carbon $baseDate, string $period)
{
    $binAnalytics = $this->_computeBinAnalyticsPerAsset($baseDate, $period);
    $cleaningLogs = $this->_getCleaningLogs($baseDate, $period);
    [$start, $end] = $this->resolveDateRange($baseDate, $period);

    $insights = [];

    // Find bins that reached full capacity most often
    $maxFullEvents = $binAnalytics->max('times_full');
    if ($maxFullEvents > 0) {
        $topBins = $binAnalytics
            ->filter(fn($item) => $item->times_full === $maxFullEvents)
            ->pluck('asset_name');

        if ($topBins->count() === 1) {
            $insights[] = $topBins->first() . " reached full capacity " . $maxFullEvents . " time" . ($maxFullEvents > 1 ? 's' : '') . " this " . ($period === 'month' ? 'month' : ($period === 'week' ? 'week' : 'day')) . ".";
        } else {
            $insights[] = $topBins->join(', ') . " each reached full capacity " . $maxFullEvents . " times this " . ($period === 'month' ? 'month' : ($period === 'week' ? 'week' : 'day')) . ".";
        }
    }

    // Higher fill frequency detection
    $avgFullEvents = $binAnalytics->avg('times_full') ?? 0;
    $highFrequencyBins = $binAnalytics
        ->filter(fn($item) => $item->times_full > $avgFullEvents && $item->times_full > 0)
        ->pluck('asset_name');

    if ($highFrequencyBins->count() > 0) {
        $insights[] = "Higher fill frequency detected at " . $highFrequencyBins->join(', ') . ". Consider increasing collection frequency in " . ($highFrequencyBins->count() === 1 ? 'this' : 'these') . " area" . ($highFrequencyBins->count() > 1 ? 's' : '') . ".";
    }

    // Calculate average clear time improvement (compare with previous period)
    $previousBaseDate = $baseDate->copy();
    if ($period === 'month') {
        $previousBaseDate->subMonth();
    } elseif ($period === 'week') {
        $previousBaseDate->subWeek();
    } else {
        $previousBaseDate->subDay();
    }

    $currentAvgClear = $binAnalytics
        ->filter(fn($item) => $item->avg_clear_time > 0)
        ->avg('avg_clear_time') ?? 0;

    $previousBinAnalytics = $this->_computeBinAnalyticsPerAsset($previousBaseDate, $period);
    $previousAvgClear = $previousBinAnalytics
        ->filter(fn($item) => $item->avg_clear_time > 0)
        ->avg('avg_clear_time') ?? 0;

    if ($currentAvgClear > 0 && $previousAvgClear > 0) {
        $percentageChange = round((($previousAvgClear - $currentAvgClear) / $previousAvgClear) * 100);
        $improvementWord = $percentageChange >= 0 ? 'faster' : 'slower';
        $absPercentage = abs($percentageChange);

        if ($percentageChange > 0) {
            $insights[] = "Average clear time improved to " . $currentAvgClear . " hours, " . $absPercentage . "% " . $improvementWord . " than last " . ($period === 'month' ? 'month' : ($period === 'week' ? 'week' : 'day')) . ".";
        } elseif ($percentageChange < 0) {
            $insights[] = "Average clear time is " . $currentAvgClear . " hours, " . $absPercentage . "% " . $improvementWord . " than last " . ($period === 'month' ? 'month' : ($period === 'week' ? 'week' : 'day')) . ".";
        }
    } elseif ($currentAvgClear > 0) {
        $insights[] = "Average clear time is " . $currentAvgClear . " hours this " . ($period === 'month' ? 'month' : ($period === 'week' ? 'week' : 'day')) . ".";
    }

    return $insights;
}

private function buildMetricModalData($binAnalytics, $cleaningLogs, Carbon $startDate, Carbon $endDate): array
{
    $fullRows = $binAnalytics
        ->filter(fn ($item) => $item->times_full > 0)
        ->sortByDesc('times_full')
        ->map(fn ($item) => [
            'asset' => $item->asset_name,
            'total_full_events' => $item->times_full,
        ])
        ->values()
        ->all();

    $fillRows = $binAnalytics
        ->filter(fn ($item) => $item->avg_fill_time > 0)
        ->sortByDesc('avg_fill_time')
        ->map(fn ($item) => [
            'asset' => $item->asset_name,
            'avg_fill_time_hours' => number_format($item->avg_fill_time, 2),
        ])
        ->values()
        ->all();

    $emptyRows = $binAnalytics
        ->filter(fn ($item) => $item->times_empty > 0)
        ->sortByDesc('times_empty')
        ->map(fn ($item) => [
            'asset' => $item->asset_name,
            'total_empty_events' => $item->times_empty,
        ])
        ->values()
        ->all();

    $clearRows = $binAnalytics
        ->filter(fn ($item) => $item->avg_clear_time > 0)
        ->sortByDesc('avg_clear_time')
        ->map(fn ($item) => [
            'asset' => $item->asset_name,
            'avg_clear_time_hours' => number_format($item->avg_clear_time, 2),
        ])
        ->values()
        ->all();

    $avgEmptyRows = $binAnalytics
        ->filter(fn ($item) => $item->avg_empty_time > 0)
        ->sortByDesc('avg_empty_time')
        ->map(fn ($item) => [
            'asset' => $item->asset_name,
            'avg_empty_time_hours' => number_format($item->avg_empty_time, 2),
        ])
        ->values()
        ->all();

    $collectionRows = $cleaningLogs
        ->map(fn ($log) => [
            'asset' => $log->asset_name,
            'collected_at' => $log->cleaned_at->format('d M Y, h:i A'),
        ])
        ->values()
        ->all();

    $activeBinRows = DB::table('devices')
        ->join('assets', 'devices.asset_id', '=', 'assets.id')
        ->join('sensors', 'sensors.device_id', '=', 'devices.id_device')
        ->leftJoin('floor', 'assets.floor_id', '=', 'floor.id')
        ->where('assets.is_active', 1)
        ->where('devices.is_active', 1)
        ->whereBetween('sensors.created_at', [$startDate, $endDate])
        ->select(
            'assets.asset_name',
            'assets.location',
            'floor.floor_name',
            DB::raw('COUNT(DISTINCT devices.id_device) as active_devices'),
            DB::raw('COUNT(sensors.id) as total_readings')
        )
        ->groupBy('assets.id', 'assets.asset_name', 'assets.location', 'floor.floor_name')
        ->orderBy('assets.asset_name')
        ->get()
        ->map(fn ($row) => [
            'asset' => $row->asset_name,
            'location' => $row->location,
            'floor' => $row->floor_name ?? 'N/A',
            'active_devices' => (int) $row->active_devices,
            'sensor_readings' => (int) $row->total_readings,
        ])
        ->values()
        ->all();

    return [
        'total_full_events' => [
            'columns' => ['Asset', 'Total Full Events'],
            'rows' => $fullRows,
            'empty' => 'No full events found for this period.',
        ],
        'avg_fill_time' => [
            'columns' => ['Asset', 'Avg Fill Time (Hours)'],
            'rows' => $fillRows,
            'empty' => 'No fill time records found for this period.',
        ],
        'times_empty' => [
            'columns' => ['Asset', 'Total Empty Events'],
            'rows' => $emptyRows,
            'empty' => 'No empty events found for this period.',
        ],
        'avg_clear_time' => [
            'columns' => ['Asset', 'Avg Clear Time (Hours)'],
            'rows' => $clearRows,
            'empty' => 'No clear time records found for this period.',
        ],
        'avg_empty_time' => [
            'columns' => ['Asset', 'Avg Empty Time (Hours)'],
            'rows' => $avgEmptyRows,
            'empty' => 'No empty time records found for this period.',
        ],
        'total_cleaning' => [
            'columns' => ['Asset', 'Collected At'],
            'rows' => $collectionRows,
            'empty' => 'No collection trip records found for this period.',
        ],
        'total_active_bins' => [
            'columns' => ['Asset', 'Location', 'Floor', 'Active Devices', 'Sensor Readings'],
            'rows' => $activeBinRows,
            'empty' => 'No active bin records found for this period.',
        ],
    ];
}

public function computeMonthInsights(Carbon $baseDate, string $period)
{
    return $this->_computeMonthInsights($baseDate, $period);
}

public function index(Request $request)
{
    $period = $request->input('period', 'month');

    if ($period === 'today') {
        $baseDate = now();
        $monthInput = now()->format('Y-m'); // keep view happy
    }
    elseif ($period === 'week' && $request->filled('week')) {

        [$year, $weekNumber] = explode('-W', $request->week);

        $baseDate = Carbon::now()
            ->setISODate($year, $weekNumber)
            ->startOfWeek();

        // Define monthInput for view (e.g., first day of week)
        $monthInput = $baseDate->format('Y-m');

    }
    else {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $baseDate   = Carbon::parse($monthInput . '-01');
    }

    $capacityStats  = $this->getCapacityStats($baseDate, $period);
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerAsset($baseDate, $period);
    $assets         = $this->getAssets();
    $cleaningLogs   = $this->getCleaningLogs($baseDate, $period);
    $summaryMetrics = $this->computeSummaryMetrics($baseDate, $period);
    $monthInsights  = $this->computeMonthInsights($baseDate, $period);
    [$startDate, $endDate] = $this->resolveDateRange($baseDate, $period);
    $metricModalData = $this->buildMetricModalData($binAnalytics, $cleaningLogs, $startDate, $endDate);

    return view('admin.summary.index', compact(
        'monthInput',
        'period',
        'capacityStats',
        'devicesByFloor',
        'binAnalytics',
        'assets',
        'cleaningLogs',
        'summaryMetrics',
        'monthInsights',
        'metricModalData'
    ));
}

public function sendEmail(Request $request)
{
    $period = $request->input('period', 'month');

    if ($period === 'today') {
        $baseDate = now();
        $monthInput = now()->format('Y-m');
    }
    elseif ($period === 'week' && $request->filled('week')) {

        [$year, $weekNumber] = explode('-W', $request->week);

        $baseDate = Carbon::now()
            ->setISODate($year, $weekNumber)
            ->startOfWeek();

        $monthInput = $baseDate->format('Y-m');
    }
    else {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $baseDate   = Carbon::parse($monthInput . '-01');
    }

    if ($period === 'today') {
    $reportTitle = 'Daily Report – ' . $baseDate->format('d M Y');
    }
    elseif ($period === 'week') {
        $start = $baseDate->copy()->startOfWeek();
        $end   = $baseDate->copy()->endOfWeek();

        $weekNumber = $baseDate->weekOfYear;

        $reportTitle = 'Weekly Report – Week ' . $weekNumber .
            ' (' . $start->format('d M Y') .
            ' – ' . $end->format('d M Y') . ')';
    }
    else {
        $start = $baseDate->copy()->startOfMonth();
        $end   = $baseDate->copy()->endOfMonth();

        $reportTitle = 'Monthly Report – ' .
            $baseDate->format('F Y') .
            ' (' . $start->format('d M') .
            ' – ' . $end->format('d M Y') . ')';
    }

    $user     = auth()->user();

    $capacityStats  = $this->getCapacityStats($baseDate, $period);
    $devicesByFloor = $this->getDevicesByFloor();
    $binAnalytics   = $this->computeBinAnalyticsPerAsset($baseDate, $period);
    $assets         = $this->getAssets();
    $cleaningLogs   = $this->getCleaningLogs($baseDate, $period);
    $summaryMetrics = $this->computeSummaryMetrics($baseDate, $period);

    // Helper to generate QuickChart URL
    $generateChartUrl = function ($type, $labels, $label, $data, $borderColor, $bgColor) {
        return "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $borderColor,
                    'backgroundColor' => $bgColor,
                    'fill' => true,
                    'tension' => 0.3,
                ]],
            ],
        ]));
    };

    $labels = $binAnalytics->pluck('asset_name')->values();

    $generateChartUrl = function ($type, $label, $data, $border, $bg) use ($labels) {
        return "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $border,
                    'backgroundColor' => $bg,
                    'fill' => true,
                    'tension' => 0.3,
                ]],
            ],
        ]));
    };

    $timesFullChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl(
                'bar',
                'Times Became Full',
                $binAnalytics->pluck('times_full')->values(),
                '#8e44ad',
                'rgba(142,68,173,0.8)'
            )
        )
    );

    $avgFillChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl(
                'bar',
                'Average Fill Time (Hours)',
                $binAnalytics->pluck('avg_fill_time')->values(),
                '#2ecc71',
                'rgba(46,204,113,0.8)'
            )
        )
    );

    $avgClearChartData = 'data:image/png;base64,' . base64_encode(
        file_get_contents(
            $generateChartUrl(
                'bar',
                'Average Clear Time (Hours)',
                $binAnalytics->pluck('avg_clear_time')->values(),
                '#e74c3c',
                'rgba(231,76,60,0.8)'
            )
        )
    );

    $pdf = Pdf::loadView('emails.summary_report', [
        'reportTitle'        => $reportTitle,
        'period'             => $period,
        'baseDate'           => $baseDate,
        'capacityStats'      => $capacityStats,
        'devicesByFloor'     => $devicesByFloor,
        'binAnalytics'       => $binAnalytics,
        'assets'             => $assets,
        'cleaningLogs'       => $cleaningLogs,
        'monthInput'         => $monthInput,
        'timesFullChartData' => $timesFullChartData,
        'avgFillChartData'   => $avgFillChartData,
        'avgClearChartData'  => $avgClearChartData,
        'summaryMetrics'     => $summaryMetrics,
    ])->setPaper('a4', 'portrait');

    try {
        Mail::to($user->email)->send(
            new SummaryReportMail([
                'reportTitle' => $reportTitle
            ], $pdf->output())
        );

        toast()->success('Summary report sent successfully to ' . $user->email);

        return back()->with('success', 'Summary report sent successfully to ' . $user->email);
    } catch (Throwable $e) {
        report($e);
        toast()->error('Failed to send summary report. Please try again.');

        return back()->with('error', 'Failed to send summary report. Please try again.');
    }
}

private function isWithinCollectionWindow(Carbon $timestamp): bool
{
    $minutes = ($timestamp->hour * 60) + $timestamp->minute;

    return $minutes >= 420 && $minutes <= 1140;
}

private function isCollectionCapacity(float $capacity): bool
{
    return $this->isEmptyCapacity($capacity);
}

private function isEmptyCapacity(float $capacity): bool
{
    return $capacity <= 0.0 || abs($capacity) < 0.00001;
}

private function isFullCapacity(float $capacity): bool
{
    return $capacity >= 80.0;
}

}
