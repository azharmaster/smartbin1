@extends('layouts.app')
@section('content_title', 'Summary Collection Trip')

@section('content')
<div class="container-fluid">
    <div class="print-only mb-3">
        <h2 class="mb-1">Summary Collection Trip</h2>
        <div class="text-muted">
            Range: {{ $rangeLabel }}
            @if($assetId)
            | Asset: {{ optional($assets->firstWhere('id', $assetId))->asset_name ?? 'Selected Asset' }}
            @else
            | Asset: All Assets
            @endif
            | Capacity Filter: <span id="printCapacityFilterTitle">{{ $capacityFilterTitle }}</span>
        </div>
    </div>

    <div class="row mb-4 align-items-center no-print">
        <div class="col-lg-8">
            <form method="GET" action="{{ route('collection-trips.summary') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Period</label>
                    <select name="period" class="form-select fw-bold" onchange="this.form.submit()">
                        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Asset</label>
                    <select name="asset_id" class="form-select fw-bold" onchange="this.form.submit()">
                        <option value="">All Assets</option>
                        @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ (string) $assetId === (string) $asset->id ? 'selected' : '' }}>
                            {{ $asset->asset_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @if ($period === 'daily')
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Date</label>
                    <input type="date" name="date" value="{{ $dateInput }}" class="form-control fw-bold" onchange="this.form.submit()">
                </div>
                @endif

                @if ($period === 'weekly')
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Week</label>
                    <input type="week" name="week" value="{{ $weekInput }}" class="form-control fw-bold" onchange="this.form.submit()">
                </div>
                @endif

                @if ($period === 'monthly')
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month</label>
                    <input type="month" name="month" value="{{ $monthInput }}" class="form-control fw-bold" onchange="this.form.submit()">
                </div>
                @endif

            </form>
        </div>

        <div class="col-lg-2 ms-auto text-end">
            <a href="{{ route('collection-trips.index', ['asset_id' => $assetId]) }}" class="btn btn-outline-primary mt-2 w-100">
                <i class="fas fa-list me-1"></i> Trip List
            </a>
        </div>
        <div class="col-lg-2 text-end">
            <button type="button" id="saveSummaryPdfBtn" class="btn btn-danger mt-2 w-100">
                <i class="fas fa-file-pdf me-1"></i> Save PDF
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4 no-print">
        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100 metric-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Collection Trips</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalTrips) }}</h2>
                            <small class="d-block mt-2 text-white-50">Total collection trips recorded for the selected period.</small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-route fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100 metric-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Average Collection Rate</h6>
                            <h2 class="mb-0 fw-bold">{{ $averageTripsMetric['value'] }} <small class="fs-6">/{{ $averageTripsMetric['unit'] }}</small></h2>
                            <small class="d-block mt-2 text-white-50">Average collection frequency using the most suitable time unit.</small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clock fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100 metric-card" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Peak Collection Day</h6>
                            <h2 class="mb-0 fw-bold">{{ $weekdayPeakLabel }}</h2>
                            <small class="d-block mt-2 text-white-50">The most frequent collection day from Monday to Sunday.</small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100 metric-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Highest Collection Volume</h6>
                            <h2 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ $mostUsedBin }}</h2>
                            <small class="d-block mt-2 text-white-50">Bin with the highest number of collection trips in this period.</small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-trophy fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100 metric-card" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Highest Capacity</h6>
                            <h2 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ $highestCapacityTile['value'] }} {{ $highestCapacityTile['label'] }}</h2>
                            <!-- <h2 class="mb-0 fw-bold"></h2>
                            <small class="d-block mt-2 text-white-50"></small> -->
                            <small class="d-block mt-1 text-white-50">Highest recorded asset and device capacity.</small>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-battery-three-quarters fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-chart-line me-2"></i>
                    Collection Trip Trend
                </div>
                <div class="card-body summary-chart-body summary-chart-xl">
                    <canvas id="collectionTripSummaryChart"></canvas>
                </div>
            </div>
        </div>


    </div>

    <div class="row g-3 mt-1 align-items-stretch">
        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-trophy me-2"></i>
                    Collection Frequency by Bin
                </div>
                <div class="card-body p-2 summary-chart-body summary-chart-md">
                    <canvas id="mostUsedBinChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-chart-bar me-2"></i>
                    Compartment Capacity by Asset & Device
                </div>
                <div class="card-body p-2 summary-chart-body summary-chart-md">
                    <canvas id="compartmentCapacityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <i class="fas fa-trash-alt me-2"></i>
                        Capacity Bins
                    </div>
                    <div class="mb-0">
                        <div class="btn-group btn-group-sm capacity-tabs" role="group" aria-label="Capacity Filter">
                            <button type="button" data-capacity-filter="empty" class="btn {{ $capacityFilter === 'empty' ? 'btn-light text-dark' : 'btn-outline-light' }}">
                                Empty (0)
                            </button>
                            <button type="button" data-capacity-filter="half" class="btn {{ $capacityFilter === 'half' ? 'btn-light text-dark' : 'btn-outline-light' }}">
                                Half Full (1-79)
                            </button>
                            <button type="button" data-capacity-filter="full" class="btn {{ $capacityFilter === 'full' ? 'btn-light text-dark' : 'btn-outline-light' }}">
                                Full (80-100)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body summary-chart-body summary-chart-lg">
                    <canvas id="fullOver80Chart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-calendar-week me-2"></i>
                    Collection Frequency by Day
                </div>
                <div class="card-body summary-chart-body summary-chart-lg">
                    <canvas id="weekdayCollectionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-clock me-2"></i>
                    Collection Frequency by Hour (7 AM - 7 PM)
                </div>
                <div class="card-body summary-chart-body summary-chart-lg">
                    <canvas id="hourlyCollectionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-lightbulb me-2"></i>
                    Smart Bin System KPI
                </div>
                <div class="card-body p-0 summary-panel-body summary-panel-lg">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>KPI</th>
                                <th>Value</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemKpis as $kpi)
                            <tr>
                                <td>{{ $kpi['title'] }}</td>
                                <td class="fw-bold">{{ $kpi['value'] }}</td>
                                <td>{{ $kpi['detail'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1 align-items-stretch">
        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-lightbulb me-2"></i>
                    {{ $period === 'monthly' ? 'Monthly' : ($period === 'weekly' ? 'Weekly' : 'Daily') }} Insights
                </div>
                <div class="card-body">
                    @if(count($insights) > 0)
                    <ul class="mb-0">
                        @foreach($insights as $insight)
                        <li class="mb-2">{{ $insight }}</li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-muted mb-0">No insights available for this period.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .summary-gradient {
        background: linear-gradient(270deg, #9457b3, #672d84, #9457b3);
        background-size: 400% 400%;
        animation: smartbinGradient 8s ease infinite;
    }

    @keyframes smartbinGradient {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .summary-chart-body {
        padding: 8px;
    }

    .summary-chart-xl {
        height: 210px;
    }

    .summary-chart-lg {
        height: 180px;
    }

    .summary-chart-md {
        height: 170px;
    }

    .summary-panel-body {
        overflow-y: auto;
    }

    .summary-panel-lg {
        height: 180px;
    }

    .metric-card {
        cursor: default;
    }

    .capacity-tabs .btn {
        font-weight: 700;
        border-width: 1px;
    }

    .print-only {
        display: none;
    }

    @media (min-width: 768px) {
        .col-md-2-4 {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 4mm;
        }

        html,
        body {
            background: #fff !important;
            zoom: 0.60;
            font-size: 8px !important;
        }

        .no-print,
        .main-sidebar,
        .main-header,
        .main-footer,
        .content-header,
        .card-header form,
        .btn,
        .navbar {
            display: none !important;
        }

        .content-wrapper,
        .content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .print-only {
            display: block;
            margin-bottom: 4px !important;
        }

        h2,
        h6,
        .card-header,
        .table th,
        .table td,
        small,
        .text-muted {
            line-height: 1.2 !important;
        }

        .row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-left: -4px !important;
            margin-right: -4px !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .col-lg-12,
        .col-lg-6,
        .col-md-2-4 {
            float: none !important;
            padding-left: 4px !important;
            padding-right: 4px !important;
        }

        .col-lg-12 {
            width: 100% !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        .col-lg-6 {
            width: 50% !important;
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }

        .col-md-2-4 {
            width: 20% !important;
            flex: 0 0 20% !important;
            max-width: 20% !important;
        }

        .card {
            box-shadow: none !important;
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px solid #dee2e6 !important;
            margin-bottom: 4px !important;
        }

        .card-header {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding: 3px 5px !important;
            font-size: 8px !important;
        }

        .card-body {
            padding: 4px !important;
        }

        .table {
            margin-bottom: 0 !important;
        }

        .table th,
        .table td {
            padding: 3px 4px !important;
            font-size: 8px !important;
        }

        .metric-card .card-body {
            padding: 4px !important;
        }

        .metric-card h2 {
            font-size: 0.82rem !important;
        }

        .metric-card .fa-3x {
            font-size: 1rem !important;
        }

        canvas {
            max-width: 100% !important;
        }

        .summary-chart-xl {
            height: 92px !important;
        }

        .summary-chart-lg {
            height: 84px !important;
        }

        .summary-chart-md,
        .summary-panel-lg {
            height: 78px !important;
        }

        .row.g-3,
        .row.g-4 {
            row-gap: 2px !important;
        }

        .card-header i,
        .metric-card small,
        .text-white-50,
        .text-muted {
            font-size: 7px !important;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartInstances = [];
        const capacityFilterDatasets = @json($capacityFilterDatasets);
        const capacityFilterButtons = Array.from(document.querySelectorAll('[data-capacity-filter]'));
        const printCapacityFilterTitle = document.getElementById('printCapacityFilterTitle');
        let currentCapacityFilter = @json($capacityFilter);
        let currentCapacityFilterTitle = @json($capacityFilterTitle);
        let fullOver80Chart;
        const baseChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        };

        chartInstances.push(new Chart(document.getElementById('collectionTripSummaryChart'), {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Collection Trips',
                    data: @json($chartData),
                    backgroundColor: 'rgba(31,100,35,0.75)',
                    borderColor: '#1f6423',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                ...baseChartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }));

        fullOver80Chart = new Chart(document.getElementById('fullOver80Chart'), {
            type: 'bar',
            data: {
                labels: @json($fullOver80Labels),
                datasets: [{
                    label: @json($capacityFilterDatasetLabel),
                    data: @json($fullOver80Data),
                    backgroundColor: 'rgba(220,53,69,0.8)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                ...baseChartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        chartInstances.push(fullOver80Chart);

        function syncCapacityFilterButtons(activeFilter) {
            capacityFilterButtons.forEach(function(button) {
                const isActive = button.dataset.capacityFilter === activeFilter;
                button.classList.toggle('btn-light', isActive);
                button.classList.toggle('text-dark', isActive);
                button.classList.toggle('btn-outline-light', !isActive);
            });
        }

        function applyCapacityFilter(filterKey) {
            const dataset = capacityFilterDatasets[filterKey];

            if (!dataset || !fullOver80Chart) {
                return;
            }

            currentCapacityFilter = filterKey;
            currentCapacityFilterTitle = dataset.filter_title;

            fullOver80Chart.data.labels = dataset.labels;
            fullOver80Chart.data.datasets[0].label = dataset.dataset_label;
            fullOver80Chart.data.datasets[0].data = dataset.data;
            fullOver80Chart.update();

            if (printCapacityFilterTitle) {
                printCapacityFilterTitle.textContent = dataset.filter_title;
            }

            syncCapacityFilterButtons(filterKey);
        }

        syncCapacityFilterButtons(currentCapacityFilter);
        capacityFilterButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                applyCapacityFilter(button.dataset.capacityFilter);
            });
        });

        chartInstances.push(new Chart(document.getElementById('weekdayCollectionChart'), {
            type: 'bar',
            data: {
                labels: @json($weekdayLabels),
                datasets: [{
                    label: 'Collection Trips',
                    data: @json($weekdayData),
                    backgroundColor: 'rgba(111, 66, 193, 0.82)',
                    borderColor: '#6f42c1',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                ...baseChartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }));

        chartInstances.push(new Chart(document.getElementById('hourlyCollectionChart'), {
            type: 'bar',
            data: {
                labels: @json($hourlyLabels),
                datasets: [{
                    label: 'Collection Trips',
                    data: @json($hourlyData),
                    backgroundColor: 'rgba(255, 159, 64, 0.82)',
                    borderColor: '#ff9f40',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                ...baseChartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }));

        chartInstances.push(new Chart(document.getElementById('mostUsedBinChart'), {
            type: 'bar',
            data: {
                labels: @json($mostUsedBinLabels),
                datasets: [{
                    label: 'Collection Trips',
                    data: @json($mostUsedBinData),
                    backgroundColor: 'rgba(15,32,39,0.82)',
                    borderColor: '#0f2027',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                ...baseChartOptions,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }));

        chartInstances.push(new Chart(document.getElementById('compartmentCapacityChart'), {
            type: 'bar',
            data: {
                labels: @json($compartmentCapacityLabels),
                datasets: [{
                    label: 'Compartment Capacity',
                    data: @json($compartmentCapacityData),
                    backgroundColor: 'rgba(13, 110, 253, 0.78)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 8,
                }]
            },
            options: {
                ...baseChartOptions,
                indexAxis: 'y',
                plugins: {
                    ...baseChartOptions.plugins,
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` Capacity: ${context.parsed.x}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        }));

        function buildMetricTable() {
            return {
                table: {
                    widths: ['20%', '20%', '20%', '20%', '20%'],
                    body: [[
                        buildMetricCell('Total Collection Trips', @json(number_format($totalTrips)), 'Total collection trips recorded for the selected period.'),
                        buildMetricCell('Average Collection Rate', @json($averageTripsMetric['value'] . '/' . $averageTripsMetric['unit']), 'Average collection frequency using the most suitable time unit.'),
                        buildMetricCell('Peak Collection Day', @json($weekdayPeakLabel), 'The most frequent collection day from Monday to Sunday.'),
                        buildMetricCell('Highest Collection Volume', @json($mostUsedBin), 'Bin with the highest number of collection trips in this period.'),
                        buildMetricCell('Highest Capacity', @json($highestCapacityTile['value'] . ' ' . $highestCapacityTile['label']), 'Highest recorded asset and device capacity.')
                    ]]
                },
                layout: {
                    hLineWidth: () => 0,
                    vLineWidth: () => 0,
                    paddingLeft: () => 4,
                    paddingRight: () => 4,
                    paddingTop: () => 4,
                    paddingBottom: () => 4
                },
                margin: [0, 0, 0, 8]
            };
        }

        function buildMetricCell(title, value, note) {
            return {
                stack: [
                    { text: title, fontSize: 9, color: '#6b7280', margin: [0, 0, 0, 4] },
                    { text: value, fontSize: 12, bold: true, margin: [0, 0, 0, 4] },
                    { text: note, fontSize: 8, color: '#6b7280' }
                ],
                fillColor: '#ffffff',
                margin: [0, 0, 0, 0]
            };
        }

        function chartImage(id) {
            const canvas = document.getElementById(id);
            return canvas ? canvas.toDataURL('image/png', 1.0) : null;
        }

        function buildChartCard(title, imageData) {
            return {
                stack: [
                    { text: title, fillColor: '#672d84', color: '#ffffff', bold: true, fontSize: 10, margin: [0, 0, 0, 6] },
                    imageData
                        ? { image: imageData, width: 280, height: 88, alignment: 'center' }
                        : { text: 'Chart unavailable', italics: true, color: '#6b7280', margin: [0, 8, 0, 8] }
                ],
                margin: [0, 0, 0, 3]
            };
        }

        function buildKpiTable() {
            const rows = [
                [
                    { text: 'KPI', style: 'tableHeader' },
                    { text: 'Value', style: 'tableHeader' },
                    { text: 'Detail', style: 'tableHeader' }
                ],
                ...@json($systemKpis).map(kpi => [kpi.title, kpi.value, kpi.detail])
            ];

            return {
                stack: [
                    { text: 'Smart Bin System KPI', fillColor: '#212529', color: '#ffffff', bold: true, fontSize: 10, margin: [0, 0, 0, 6] },
                    {
                        table: {
                            headerRows: 1,
                            widths: ['28%', '18%', '54%'],
                            body: rows
                        },
                        layout: 'lightHorizontalLines'
                    }
                ],
                margin: [0, 0, 0, 5]
            };
        }

        function buildInsightsCard() {
            const insights = @json($insights);
            return {
                stack: [
                    { text: @json(($period === 'monthly' ? 'Monthly' : ($period === 'weekly' ? 'Weekly' : 'Daily')) . ' Insights'), fillColor: '#212529', color: '#ffffff', bold: true, fontSize: 10, margin: [0, 0, 0, 6] },
                    insights.length
                        ? { ul: insights, fontSize: 9 }
                        : { text: 'No insights available for this period.', fontSize: 9, color: '#6b7280' }
                ],
                margin: [0, 0, 0, 5]
            };
        }

        document.getElementById('saveSummaryPdfBtn')?.addEventListener('click', function() {
            chartInstances.forEach(function(chart) {
                chart.resize();
                chart.update('none');
            });

            const docDefinition = {
                pageSize: 'A4',
                pageOrientation: 'landscape',
                pageMargins: [8, 6, 8, 6],
                content: [
                    { text: 'Summary Collection Trip', fontSize: 12, bold: true, margin: [0, 0, 0, 2] },
                    {
                        text: `Range: ${@json($rangeLabel)} | Asset: ${@json($assetId ? (optional($assets->firstWhere('id', $assetId))->asset_name ?? 'Selected Asset') : 'All Assets')} | Capacity Filter: ${currentCapacityFilterTitle}`,
                        fontSize: 7,
                        color: '#6b7280',
                        margin: [0, 0, 0, 3]
                    },
                    buildMetricTable(),
                    {
                        columns: [
                            buildChartCard('Collection Trip Trend', chartImage('collectionTripSummaryChart')),
                            buildChartCard('Collection Frequency by Bin', chartImage('mostUsedBinChart'))
                        ],
                        columnGap: 8
                    },
                    {
                        columns: [
                            buildChartCard('Capacity Bins', chartImage('fullOver80Chart')),
                            buildChartCard('Collection Frequency by Day', chartImage('weekdayCollectionChart'))
                        ],
                        columnGap: 8
                    },
                    {
                        columns: [
                            buildChartCard('Collection Frequency by Hour (7 AM - 7 PM)', chartImage('hourlyCollectionChart')),
                            buildChartCard('Compartment Capacity by Asset & Device', chartImage('compartmentCapacityChart'))
                        ],
                        columnGap: 8
                    },
                    {
                        columns: [
                            buildKpiTable(),
                            buildInsightsCard()
                        ],
                        columnGap: 8
                    }
                ],
                styles: {
                    tableHeader: {
                        bold: true,
                        fillColor: '#f3f4f6',
                        color: '#111827',
                        fontSize: 8
                    }
                },
                defaultStyle: {
                    fontSize: 8
                }
            };

            pdfMake.createPdf(docDefinition).download('collection-trip-summary.pdf');
        });

        window.addEventListener('afterprint', function() {
            chartInstances.forEach(function(chart) {
                chart.resize();
            });
        });
    });
</script>
@endpush
