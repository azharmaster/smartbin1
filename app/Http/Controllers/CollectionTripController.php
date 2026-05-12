<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\CollectionTripService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class CollectionTripController extends Controller
{
    private const CLEAR_HOLD_MINUTES = 20;

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
        return view('collection-trips.summaryCollectionTrip', $this->getSummaryViewData($request->all()));
    }

    public function pdf(Request $request)
    {
        return response()->streamDownload(function () use ($request) {
            echo $this->generateSummaryPdf($request->all());
        }, 'collection-trip-summary.pdf');
    }

    public function getSummaryViewData(array $filters = []): array
    {
        $request = Request::create('/collection-trips/summary', 'GET', $filters);

        return $this->buildSummaryViewData($request);
    }

    public function generateSummaryPdf(array $filters = []): string
    {
        $data = $this->getSummaryViewData($filters);
        $data['pdfCharts'] = $this->buildPdfChartUrls($data);

        return Pdf::loadView('collection-trips.summaryCollectionTripPdf', $data)
            ->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')
            ->output();
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

    private function buildSummaryViewData(Request $request): array
    {
        $period = $request->input('period', 'monthly');
        $assetId = $request->integer('asset_id') ?: null;
        $capacityFilter = $request->input('capacity_filter', 'empty');

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
                $bucketLabels[] = $period === 'weekly' ? $date->format('D') : $date->format('d M');
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
        $binKpis = $this->buildBinKpis($rangeStart, $rangeEnd, $assetId, $capacityFilter);
        $systemKpis = $this->buildSystemKpis($rangeStart, $rangeEnd, $assetId, $collectionTrips);
        $compartmentCapacities = $this->buildCompartmentCapacities($rangeStart, $rangeEnd, $assetId);
        $highestCapacityTile = count($compartmentCapacities['labels']) > 0
            ? ['label' => $compartmentCapacities['labels'][0], 'value' => $compartmentCapacities['data'][0] . '%']
            : ['label' => 'N/A', 'value' => 'N/A'];

        return [
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
            'insights' => $this->buildInsights($collectionTrips, $period, $bucketLabels, $chartData),
            'fullOver80Labels' => $binKpis['full_over_80_labels'],
            'fullOver80Data' => $binKpis['full_over_80_data'],
            'fullOver80PeakBin' => $binKpis['full_over_80_peak_bin'],
            'capacityFilter' => $capacityFilter,
            'capacityFilterTitle' => $binKpis['filter_title'],
            'capacityFilterDatasetLabel' => $binKpis['dataset_label'],
            'capacityFilterDatasets' => $binKpis['datasets_by_filter'],
            'compartmentCapacityLabels' => $compartmentCapacities['labels'],
            'compartmentCapacityData' => $compartmentCapacities['data'],
            'highestCapacityTile' => $highestCapacityTile,
            'systemKpis' => $systemKpis,
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

    private function buildBinKpis(Carbon $rangeStart, Carbon $rangeEnd, ?int $assetId = null, string $capacityFilter = 'empty'): array
    {
        $selectedFilterMeta = $this->resolveCapacityFilterMeta($capacityFilter);
        $assets = Asset::with([
            'capacitySetting',
            'devices' => fn ($query) => $query->where('is_active', 1)->orderBy('id_device'),
            'devices.sensors' => fn ($query) => $query->orderBy('created_at', 'asc'),
        ])
            ->where('is_active', 1)
            ->when($assetId, fn ($query) => $query->where('id', $assetId))
            ->get();

        $filterKeys = ['empty', 'half', 'full'];
        $rowsByFilter = [
            'empty' => collect(),
            'half' => collect(),
            'full' => collect(),
        ];

        foreach ($assets as $asset) {
            $countsByFilter = [
                'empty' => 0,
                'half' => 0,
                'full' => 0,
            ];

            foreach ($asset->devices as $device) {
                $sensors = $device->sensors
                    ->filter(fn ($sensor) => is_numeric($sensor->capacity))
                    ->map(fn ($sensor) => [
                        'capacity' => (float) $sensor->capacity,
                        'created_at' => Carbon::parse($sensor->created_at)->timezone(config('app.timezone')),
                    ])
                    ->values();

                foreach ($sensors as $reading) {
                    $currentCapacity = $reading['capacity'];
                    $readingTime = $reading['created_at'];

                    if (! $readingTime->betweenIncluded($rangeStart, $rangeEnd)) {
                        continue;
                    }

                    foreach ($filterKeys as $filterKey) {
                        if ($this->matchesCapacityFilter($currentCapacity, $filterKey)) {
                            $countsByFilter[$filterKey]++;
                        }
                    }
                }
            }

            foreach ($filterKeys as $filterKey) {
                $rowsByFilter[$filterKey]->push([
                    'asset_name' => $asset->asset_name,
                    'count' => $countsByFilter[$filterKey],
                ]);
            }
        }

        $datasetsByFilter = [];

        foreach ($filterKeys as $filterKey) {
            $filterMeta = $this->resolveCapacityFilterMeta($filterKey);
            $rows = $rowsByFilter[$filterKey]
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->take(8)
            ->values();

            $peakBin = $rows->first();

            $datasetsByFilter[$filterKey] = [
                'labels' => $rows->pluck('asset_name')->all(),
                'data' => $rows->pluck('count')->all(),
                'peak_bin' => $peakBin
                    ? $peakBin['asset_name'] . ' (' . $peakBin['count'] . ' events)'
                    : 'N/A',
                'filter_title' => $filterMeta['title'],
                'dataset_label' => $filterMeta['dataset_label'],
            ];
        }

        $selectedDataset = $datasetsByFilter[$selectedFilterMeta['key']];

        return [
            'full_over_80_labels' => $selectedDataset['labels'],
            'full_over_80_data' => $selectedDataset['data'],
            'full_over_80_peak_bin' => $selectedDataset['peak_bin'],
            'filter_title' => $selectedDataset['filter_title'],
            'dataset_label' => $selectedDataset['dataset_label'],
            'datasets_by_filter' => $datasetsByFilter,
        ];
    }

    private function resolveCapacityFilterMeta(string $capacityFilter): array
    {
        return match ($capacityFilter) {
            'empty' => [
                'key' => 'empty',
                'title' => 'Empty Capacity Bins',
                'dataset_label' => 'Empty (0%) Readings',
            ],
            'half' => [
                'key' => 'half',
                'title' => 'Half Full Capacity Bins',
                'dataset_label' => 'Half Full (1%-79%) Readings',
            ],
            default => [
                'key' => 'full',
                'title' => 'Full Capacity Bins',
                'dataset_label' => 'Full (80%-100%) Readings',
            ],
        };
    }

    private function matchesCapacityFilter(float $capacity, string $capacityFilter): bool
    {
        return match ($capacityFilter) {
            'empty' => $capacity <= 0.0 || abs($capacity) < 0.00001,
            'half' => $capacity >= 1.0 && $capacity <= 79.0,
            default => $capacity >= 80.0 && $capacity <= 100.0,
        };
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

    private function buildPdfChartUrls(array $data): array
    {
        return [
            'trend' => $this->buildQuickChartUrl('bar', $this->normalizeChartArray($data['chartLabels']), $this->normalizeChartArray($data['chartData']), 'Collection Trips', '#1f6423'),
            'bin_frequency' => $this->buildQuickChartUrl('bar', $this->normalizeChartArray($data['mostUsedBinLabels']), $this->normalizeChartArray($data['mostUsedBinData']), 'Collection Trips', '#0f2027', true),
            'capacity_bins' => $this->buildQuickChartUrl('bar', $this->normalizeChartArray($data['fullOver80Labels']), $this->normalizeChartArray($data['fullOver80Data']), $data['capacityFilterDatasetLabel'], '#dc3545'),
            'weekday' => $this->buildQuickChartUrl('bar', $this->normalizeChartArray($data['weekdayLabels']), $this->normalizeChartArray($data['weekdayData']), 'Collection Trips', '#6f42c1'),
            'hourly' => $this->buildQuickChartUrl('bar', $this->normalizeChartArray($data['hourlyLabels']), $this->normalizeChartArray($data['hourlyData']), 'Collection Trips', '#ff9f40'),
            'compartment' => $this->buildQuickChartUrl('bar', $this->normalizeChartArray($data['compartmentCapacityLabels']), $this->normalizeChartArray($data['compartmentCapacityData']), 'Compartment Capacity', '#0d6efd', true, true),
        ];
    }

    private function normalizeChartArray($value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->values()->all();
        }

        return is_array($value) ? array_values($value) : [];
    }

    private function buildQuickChartUrl(
        string $type,
        array $labels,
        array $data,
        string $datasetLabel,
        string $borderColor,
        bool $horizontal = false,
        bool $percentageAxis = false
    ): string {
        $config = [
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $datasetLabel,
                    'data' => $data,
                    'backgroundColor' => $this->hexToRgba($borderColor, 0.82),
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                ]],
            ],
            'options' => [
                'indexAxis' => $horizontal ? 'y' : 'x',
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    $horizontal ? 'x' : 'y' => [
                        'beginAtZero' => true,
                        'ticks' => $percentageAxis ? ['callback' => '(val) => val + "%"'] : ['precision' => 0],
                    ],
                ],
            ],
        ];

        return 'https://quickchart.io/chart?width=900&height=320&devicePixelRatio=2&format=png&backgroundColor=white&c='
            . urlencode(json_encode($config));
    }

    private function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        $rgb = sscanf($hex, '%02x%02x%02x');

        return sprintf('rgba(%d,%d,%d,%.2f)', $rgb[0], $rgb[1], $rgb[2], $alpha);
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
            $lastClearedAt = null;

            $capacitySetting = $asset->capacitySetting;
            $emptyTo = (float) ($capacitySetting?->empty_to ?? 0);
            $halfTo = (float) ($capacitySetting?->half_to ?? 79);

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

                    if ($previousCapacity !== null && $previousCapacity <= $halfTo && $currentCapacity > $halfTo && $fullAt === null) {
                        $fullAt = $readingTime;
                    }

                    if (
                        $previousCapacity !== null &&
                        $previousCapacity > $emptyTo &&
                        $this->isCollectionCapacity($currentCapacity, $emptyTo) &&
                        !$this->isClearHoldActive($lastClearedAt, $readingTime) &&
                        $readingTime->betweenIncluded($rangeStart, $rangeEnd)
                    ) {
                        $lastClearedAt = $readingTime;
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
