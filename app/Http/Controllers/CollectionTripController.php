<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\CollectionTripService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class CollectionTripController extends Controller
{
    public function __construct(private CollectionTripService $collectionTripService)
    {
    }

    /**
     * Display collection trips with date and asset filters.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $assetId = $request->integer('asset_id') ?: null;

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $collectionTrips = $this->collectionTripService->getTrips($dateFrom, $dateTo, $assetId);
        $totalTrips = $collectionTrips->count();
        $uniqueBins = $collectionTrips->unique('asset_id')->count();
        $assets = Asset::where('is_active', 1)->orderBy('asset_name')->get(['id', 'asset_name']);

        return view('collection-trips.index', compact(
            'collectionTrips',
            'dateFrom',
            'dateTo',
            'totalTrips',
            'uniqueBins',
            'assets',
            'assetId'
        ));
    }

    public function summary(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $assetId = $request->integer('asset_id') ?: null;

        [$rangeStart, $rangeEnd, $inputs] = $this->resolveSummaryRange($request, $period);

        $collectionTrips = $this->collectionTripService->getTrips(
            $rangeStart->toDateString(),
            $rangeEnd->toDateString(),
            $assetId
        );

        $bucketFormat = $period === 'daily' ? 'Y-m-d H:00' : 'Y-m-d';
        $bucketLabels = [];
        $bucketKeys = [];

        if ($period === 'daily') {
            foreach (range(0, 23) as $hour) {
                $bucketTime = $rangeStart->copy()->startOfDay()->addHours($hour);
                $bucketKeys[] = $bucketTime->format($bucketFormat);
                $bucketLabels[] = $bucketTime->format('H:i');
            }
        } else {
            foreach (CarbonPeriod::create($rangeStart->copy()->startOfDay(), '1 day', $rangeEnd->copy()->startOfDay()) as $date) {
                $bucketKeys[] = $date->format($bucketFormat);
                $bucketLabels[] = $period === 'weekly'
                    ? $date->format('D')
                    : $date->format('d M');
            }
        }

        $tripsByBucket = $collectionTrips
            ->groupBy(fn ($trip) => $trip['emptied_at']->format($bucketFormat))
            ->map(fn ($trips) => $trips->count());

        $chartData = collect($bucketKeys)
            ->map(fn ($bucketKey) => (int) ($tripsByBucket[$bucketKey] ?? 0))
            ->values();

        $mostUsedBins = $collectionTrips
            ->groupBy('asset_name')
            ->map->count()
            ->sortDesc()
            ->take(8);
        $weekdaySummary = $this->buildWeekdayCollectionSummary($collectionTrips);
        $hourlySummary = $this->buildHourlyCollectionSummary($collectionTrips);

        $assets = Asset::where('is_active', 1)->orderBy('asset_name')->get(['id', 'asset_name']);
        $binKpis = $this->buildBinKpis($rangeStart, $rangeEnd, $assetId);
        $systemKpis = $this->buildSystemKpis($rangeStart, $rangeEnd, $assetId, $collectionTrips);
        $compartmentCapacities = $this->buildCompartmentCapacities($rangeStart, $rangeEnd, $assetId);
        $highestCapacityTile = count($compartmentCapacities['labels']) > 0
            ? [
                'label' => $compartmentCapacities['labels'][0],
                'value' => $compartmentCapacities['data'][0] . '%',
            ]
            : [
                'label' => 'N/A',
                'value' => 'N/A',
            ];
        $insights = $this->buildInsights($collectionTrips, $period, $bucketLabels, $chartData);

        return view('collection-trips.summaryCollectionTrip', [
            'period' => $period,
            'assetId' => $assetId,
            'assets' => $assets,
            'collectionTrips' => $collectionTrips,
            'chartLabels' => $bucketLabels,
            'chartData' => $chartData,
            'totalTrips' => $collectionTrips->count(),
            'activeBins' => $collectionTrips->unique('asset_id')->count(),
            'averageTripsMetric' => $this->resolveAverageTripsMetric($collectionTrips->count(), $rangeStart, $rangeEnd),
            'mostUsedBin' => $mostUsedBins->isNotEmpty()
                ? $mostUsedBins->keys()->first() . ' (' . $mostUsedBins->first() . ' trips)'
                : 'N/A',
            'mostUsedBinLabels' => $mostUsedBins->keys()->values()->all(),
            'mostUsedBinData' => $mostUsedBins->values()->all(),
            'weekdayLabels' => $weekdaySummary['labels'],
            'weekdayData' => $weekdaySummary['data'],
            'weekdayPeakLabel' => $weekdaySummary['peak_label'],
            'hourlyLabels' => $hourlySummary['labels'],
            'hourlyData' => $hourlySummary['data'],
            'rangeLabel' => $this->formatRangeLabel($period, $rangeStart, $rangeEnd),
            'dateInput' => $inputs['date'],
            'weekInput' => $inputs['week'],
            'monthInput' => $inputs['month'],
            'insights' => $insights,
            'fullOver80Labels' => $binKpis['full_over_80_labels'],
            'fullOver80Data' => $binKpis['full_over_80_data'],
            'fullOver80PeakBin' => $binKpis['full_over_80_peak_bin'],
            'compartmentCapacityLabels' => $compartmentCapacities['labels'],
            'compartmentCapacityData' => $compartmentCapacities['data'],
            'highestCapacityTile' => $highestCapacityTile,
            'systemKpis' => $systemKpis,
        ]);
    }

    /**
     * Export collection trips to CSV.
     */
    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $assetId = $request->integer('asset_id') ?: null;

        $collectionTrips = $this->collectionTripService->getTrips($dateFrom, $dateTo, $assetId);

        $filename = "collection_trips_{$dateFrom}_to_{$dateTo}.csv";
        $filepath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($filepath, 'w');

        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, [
            'Asset Name',
            'Date Time',
        ]);

        foreach ($collectionTrips as $trip) {
            fputcsv($file, [
                $trip['asset_name'],
                $trip['datetime_formatted'],
            ]);
        }

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    private function resolveSummaryRange(Request $request, string $period): array
    {
        if ($period === 'daily') {
            $dateInput = $request->input('date', Carbon::now()->toDateString());
            $start = Carbon::parse($dateInput)->startOfDay();

            return [
                $start,
                $start->copy()->endOfDay(),
                [
                    'date' => $start->toDateString(),
                    'week' => $start->format('Y-\WW'),
                    'month' => $start->format('Y-m'),
                ],
            ];
        }

        if ($period === 'weekly') {
            $weekInput = $request->input('week', Carbon::now()->format('Y-\WW'));
            [$year, $weekNumber] = explode('-W', $weekInput);
            $start = Carbon::now()->setISODate((int) $year, (int) $weekNumber)->startOfWeek();

            return [
                $start,
                $start->copy()->endOfWeek(),
                [
                    'date' => $start->toDateString(),
                    'week' => $start->format('Y-\WW'),
                    'month' => $start->format('Y-m'),
                ],
            ];
        }

        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($monthInput . '-01')->startOfMonth();

        return [
            $start,
            $start->copy()->endOfMonth(),
            [
                'date' => $start->toDateString(),
                'week' => $start->format('Y-\WW'),
                'month' => $start->format('Y-m'),
            ],
        ];
    }

    private function formatRangeLabel(string $period, Carbon $rangeStart, Carbon $rangeEnd): string
    {
        if ($period === 'daily') {
            return $rangeStart->format('d M Y');
        }

        if ($period === 'weekly') {
            return $rangeStart->format('d M Y') . ' - ' . $rangeEnd->format('d M Y');
        }

        return $rangeStart->format('F Y');
    }

    private function buildInsights($collectionTrips, string $period, array $bucketLabels, $chartData): array
    {
        $insights = [];
        $periodLabel = $period === 'monthly' ? 'month' : ($period === 'weekly' ? 'week' : 'day');

        if ($collectionTrips->isEmpty()) {
            return ["No collection trips were recorded for this {$periodLabel}."];
        }

        $topAsset = $collectionTrips
            ->groupBy('asset_name')
            ->map->count()
            ->sortDesc();

        if ($topAsset->isNotEmpty()) {
            $assetName = $topAsset->keys()->first();
            $assetCount = $topAsset->first();
            $insights[] = "{$assetName} recorded the highest collection activity with {$assetCount} trips this {$periodLabel}.";
        }

        $peakValue = $chartData->max();
        if ($peakValue > 0) {
            $peakIndex = $chartData->search($peakValue);
            $peakBucket = $peakIndex !== false ? ($bucketLabels[$peakIndex] ?? null) : null;

            if ($peakBucket) {
                $bucketText = $period === 'daily' ? "Peak hour was {$peakBucket}" : "Peak day was {$peakBucket}";
                $insights[] = "{$bucketText} with {$peakValue} collection trips.";
            }
        }

        $avgTrips = round($chartData->avg(), 1);
        $aboveAverageAssets = $collectionTrips
            ->groupBy('asset_name')
            ->map->count()
            ->filter(fn ($count) => $count > $avgTrips)
            ->keys()
            ->values();

        if ($aboveAverageAssets->isNotEmpty()) {
            $insights[] = 'Above-average collection demand detected at ' . $aboveAverageAssets->join(', ') . '.';
        }

        return $insights;
    }

    private function buildBinKpis(Carbon $rangeStart, Carbon $rangeEnd, ?int $assetId = null): array
    {
        $assets = Asset::with([
            'capacitySetting',
            'devices' => fn ($query) => $query->where('is_active', 1)->orderBy('id_device'),
            'devices.sensors' => fn ($query) => $query->orderBy('created_at', 'asc'),
        ])
            ->where('is_active', 1)
            ->when($assetId, fn ($query) => $query->where('id', $assetId))
            ->get();

        $fullOver80Rows = collect();

        foreach ($assets as $asset) {
            $fullOver80Count = 0;

            foreach ($asset->devices as $device) {
                $sensors = $device->sensors
                    ->filter(fn ($sensor) => is_numeric($sensor->capacity))
                    ->map(fn ($sensor) => [
                        'capacity' => (float) $sensor->capacity,
                        'created_at' => Carbon::parse($sensor->created_at)->timezone(config('app.timezone')),
                    ])
                    ->values();

                $awaitingClearAt = null;
                $previousCapacity = null;

                foreach ($sensors as $reading) {
                    $currentCapacity = $reading['capacity'];
                    $readingTime = $reading['created_at'];

                    if ($previousCapacity !== null && $previousCapacity < 80 && $currentCapacity >= 80) {
                        if ($readingTime->betweenIncluded($rangeStart, $rangeEnd)) {
                            $fullOver80Count++;
                            if ($awaitingClearAt === null) {
                                $awaitingClearAt = $readingTime;
                            }
                        } elseif ($readingTime->lt($rangeStart) && $awaitingClearAt === null) {
                            $awaitingClearAt = $readingTime;
                        }
                    }

                    if (
                        $awaitingClearAt !== null &&
                        $previousCapacity !== null &&
                        $previousCapacity > 10 &&
                        $this->isCollectionCapacity($currentCapacity) &&
                        $readingTime->betweenIncluded($rangeStart, $rangeEnd)
                    ) {
                        $awaitingClearAt = null;
                    }
                    $previousCapacity = $currentCapacity;
                }
            }

            $fullOver80Rows->push([
                'asset_name' => $asset->asset_name,
                'count' => $fullOver80Count,
            ]);
        }

        $fullOver80Rows = $fullOver80Rows
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->take(8)
            ->values();

        $fullOver80PeakBin = $fullOver80Rows->first();

        return [
            'full_over_80_labels' => $fullOver80Rows->pluck('asset_name')->all(),
            'full_over_80_data' => $fullOver80Rows->pluck('count')->all(),
            'full_over_80_peak_bin' => $fullOver80PeakBin
                ? $fullOver80PeakBin['asset_name'] . ' (' . $fullOver80PeakBin['count'] . ' events)'
                : 'N/A',
        ];
    }

    private function isCollectionCapacity(float $capacity): bool
    {
        return $capacity <= 0.0 || abs($capacity) < 0.00001;
    }

    private function resolveAverageTripsMetric(int $totalTrips, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $minutes = max(1, $rangeStart->diffInMinutes($rangeEnd) + 1);
        $hours = max(1, $rangeStart->diffInHours($rangeEnd) + 1);
        $days = max(1, $rangeStart->diffInDays($rangeEnd) + 1);

        $rates = [
            ['unit' => 'minute', 'value' => $totalTrips / $minutes],
            ['unit' => 'hour', 'value' => $totalTrips / $hours],
            ['unit' => 'day', 'value' => $totalTrips / $days],
        ];

        $selectedRate = collect($rates)->first(fn ($rate) => $rate['value'] >= 1);

        if (!$selectedRate) {
            $selectedRate = $rates[2];
        }

        return [
            'value' => (int) max(0, round($selectedRate['value'])),
            'unit' => $selectedRate['unit'],
            'subtitle' => 'Per ' . $selectedRate['unit'] . ' across selected range',
        ];
    }

    private function buildWeekdayCollectionSummary($collectionTrips): array
    {
        $weekdayOrder = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            0 => 'Sunday',
        ];

        $weekdayCounts = $collectionTrips
            ->groupBy(fn ($trip) => (int) $trip['emptied_at']->dayOfWeek)
            ->map->count();

        $labels = [];
        $data = [];

        foreach ($weekdayOrder as $dayIndex => $label) {
            $labels[] = $label;
            $data[] = (int) ($weekdayCounts[$dayIndex] ?? 0);
        }

        $peakCount = max($data);
        $peakLabel = 'N/A';

        if ($peakCount > 0) {
            $peakDays = collect($weekdayOrder)
                ->filter(fn ($label, $dayIndex) => (int) ($weekdayCounts[$dayIndex] ?? 0) === $peakCount)
                ->values();

            $peakLabel = $peakDays->join(', ') . ' (' . $peakCount . ' trips)';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'peak_label' => $peakLabel,
        ];
    }

    private function buildHourlyCollectionSummary($collectionTrips): array
    {
        $hourCounts = $collectionTrips
            ->groupBy(fn ($trip) => (int) $trip['emptied_at']->format('G'))
            ->map->count();

        $labels = [];
        $data = [];

        foreach (range(7, 19) as $hour) {
            $labels[] = Carbon::createFromTime($hour, 0)->format('g A');
            $data[] = (int) ($hourCounts[$hour] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function resolveFrequentCollectionTimeMetric($collectionTrips): array
    {
        if ($collectionTrips->isEmpty()) {
            return [
                'value' => 'N/A',
                'detail' => 'No collection trips recorded in the selected period.',
            ];
        }

        $hourCounts = $collectionTrips
            ->groupBy(fn ($trip) => (int) $trip['emptied_at']->format('H'))
            ->map->count()
            ->sortKeys();

        $highestCount = (int) $hourCounts->max();
        $peakHours = $hourCounts
            ->filter(fn ($count) => (int) $count === $highestCount)
            ->keys()
            ->map(fn ($hour) => (int) $hour)
            ->values();

        $averagePeakHour = (int) round($peakHours->avg());
        $averagePeakTime = Carbon::createFromTime($averagePeakHour, 0)->format('h:i A');

        $timeWindow = $peakHours
            ->map(function ($hour) {
                return Carbon::createFromTime($hour, 0)->format('h:i A');
            })
            ->join(', ');

        return [
            'value' => $averagePeakTime,
            'detail' => $highestCount . ' trips most often happened around ' . $timeWindow . '.',
        ];
    }

    private function buildSystemKpis(Carbon $rangeStart, Carbon $rangeEnd, ?int $assetId, $collectionTrips): array
    {
        $frequentCollectionTimeMetric = $this->resolveFrequentCollectionTimeMetric($collectionTrips);
        $assets = Asset::with([
            'capacitySetting',
            'devices' => fn ($query) => $query->where('is_active', 1)->orderBy('id_device'),
            'devices.sensors' => fn ($query) => $query->orderBy('created_at', 'asc'),
            'devices.latestSensor',
        ])
            ->where('is_active', 1)
            ->when($assetId, fn ($query) => $query->where('id', $assetId))
            ->get();

        $fillBeforeCollection = [];
        $responseDurations = [];
        $activeDeviceCount = 0;
        $onlineDeviceCount = 0;

        foreach ($assets as $asset) {
            foreach ($asset->devices as $device) {
                $activeDeviceCount++;

                if ($device->latestSensor && Carbon::parse($device->latestSensor->created_at)->gte(now()->subMinutes(40))) {
                    $onlineDeviceCount++;
                }

                $sensors = $device->sensors
                    ->filter(fn ($sensor) => is_numeric($sensor->capacity))
                    ->map(fn ($sensor) => [
                        'capacity' => (float) $sensor->capacity,
                        'created_at' => Carbon::parse($sensor->created_at)->timezone(config('app.timezone')),
                    ])
                    ->values();

                $previousCapacity = null;
                $fullAt = null;

                foreach ($sensors as $reading) {
                    $currentCapacity = $reading['capacity'];
                    $readingTime = $reading['created_at'];

                    if ($previousCapacity !== null && $previousCapacity < 80 && $currentCapacity >= 80 && $fullAt === null) {
                        $fullAt = $readingTime;
                    }

                    if (
                        $previousCapacity !== null &&
                        $previousCapacity > 10 &&
                        $this->isCollectionCapacity($currentCapacity) &&
                        $readingTime->betweenIncluded($rangeStart, $rangeEnd)
                    ) {
                        $fillBeforeCollection[] = $previousCapacity;

                        if ($fullAt !== null) {
                            $responseDurations[] = round($fullAt->diffInMinutes($readingTime) / 60, 2);
                            $fullAt = null;
                        }
                    }

                    $previousCapacity = $currentCapacity;
                }
            }
        }

        $activeBins = max(1, $collectionTrips->unique('asset_id')->count());
        $usageRanking = $collectionTrips
            ->groupBy('asset_name')
            ->map->count()
            ->sortDesc();
        $avgFillLevel = count($fillBeforeCollection) > 0 ? round(array_sum($fillBeforeCollection) / count($fillBeforeCollection), 1) : null;
        $avgResponse = count($responseDurations) > 0 ? round(array_sum($responseDurations) / count($responseDurations), 2) : null;
        $responseUnder4Hours = count($responseDurations) > 0
            ? round((collect($responseDurations)->filter(fn ($hours) => $hours < 4)->count() / count($responseDurations)) * 100, 1)
            : null;
        $uptime = $activeDeviceCount > 0 ? round(($onlineDeviceCount / $activeDeviceCount) * 100, 1) : null;

        $mostUsedName = $usageRanking->keys()->first();
        $mostUsedCount = $usageRanking->first();

        return [
            [
                'title' => 'Fill Level Efficiency',
                'value' => $avgFillLevel !== null ? $avgFillLevel . '%' : 'N/A',
                'detail' => 'Average bin fill level before collection',
                'status' => $avgFillLevel !== null && $avgFillLevel >= 70 ? 'good' : 'warning',
            ],
            [
                'title' => 'Collection Frequency',
                'value' => $collectionTrips->count(),
                'detail' => 'Total collection trips in selected period',
                'status' => 'good',
            ],
            [
                'title' => 'Estimated Frequent Time',
                'value' => $frequentCollectionTimeMetric['value'],
                'detail' => $frequentCollectionTimeMetric['detail'],
                'status' => $frequentCollectionTimeMetric['value'] !== 'N/A' ? 'good' : 'warning',
            ],
            [
                'title' => 'Collection Response Time',
                'value' => $avgResponse !== null ? $avgResponse . ' hrs' : 'N/A',
                'detail' => $responseUnder4Hours !== null
                    ? 'cleared in under 4 hours'
                    : 'No full-to-clear cycles found',
                'status' => $avgResponse !== null && $avgResponse < 4 ? 'good' : 'warning',
            ],
            [
                'title' => 'Bin Usage Rate',
                'value' => round($collectionTrips->count() / $activeBins, 1),
                'detail' => $mostUsedName
                    ? 'Highest usage: ' . $mostUsedName . ' (' . $mostUsedCount . ' trips)'
                    : 'Average collection trips per active bin',
                'status' => 'good',
            ],
            [
                'title' => 'System Uptime',
                'value' => $uptime !== null ? $uptime . '%' : 'N/A',
                'detail' => $activeDeviceCount . ' active devices, ' . $onlineDeviceCount . ' online in last 40 minutes',
                'status' => $uptime !== null && $uptime >= 95 ? 'good' : 'warning',
            ],
        ];
    }

    private function buildCompartmentCapacities(Carbon $rangeStart, Carbon $rangeEnd, ?int $assetId = null): array
    {
        $rows = Asset::with([
            'devices' => fn ($query) => $query->where('is_active', 1)->orderBy('device_name'),
            'devices.sensors' => fn ($query) => $query
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->orderBy('created_at', 'asc'),
        ])
            ->where('is_active', 1)
            ->when($assetId, fn ($query) => $query->where('id', $assetId))
            ->get()
            ->flatMap(function ($asset) {
                return $asset->devices->map(function ($device) use ($asset) {
                    $latestSensorInRange = $device->sensors->last();

                    return [
                        'label' => $asset->asset_name . ' - ' . ($device->device_name ?? 'N/A'),
                        'capacity' => $latestSensorInRange && is_numeric($latestSensorInRange->capacity)
                            ? round((float) $latestSensorInRange->capacity, 1)
                            : 0,
                    ];
                });
            })
            ->sortByDesc('capacity')
            ->values();

        return [
            'labels' => $rows->pluck('label')->all(),
            'data' => $rows->pluck('capacity')->all(),
        ];
    }
}
