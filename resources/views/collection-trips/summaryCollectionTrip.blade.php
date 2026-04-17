@extends('layouts.app')
@section('content_title', 'Summary Collection Trip')

@section('content')
<div class="container-fluid">
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

    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-chart-line me-2"></i>
                    Collection Trip Trend
                </div>
                <div class="card-body" style="height: 380px;">
                    <canvas id="collectionTripSummaryChart"></canvas>
                </div>
            </div>
        </div>


    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-clock me-2"></i>
                    Collection Frequency by Hour (7 AM - 7 PM)
                </div>
                <div class="card-body" style="height: 340px;">
                    <canvas id="hourlyCollectionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1 align-items-stretch">
        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-trophy me-2"></i>
                    Collection Frequency by Bin
                </div>
                <div class="card-body p-2" style="height: 320px;">
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
                <div class="card-body p-2" style="height: 320px;">
                    <canvas id="compartmentCapacityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-trash-alt me-2"></i>
                    Bins Exceeding Full Capacity
                </div>
                <div class="card-body" style="height: 340px;">
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
                <div class="card-body" style="height: 340px;">
                    <canvas id="weekdayCollectionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-lightbulb me-2"></i>
                    Smart Bin System KPI
                </div>
                <div class="card-body p-0" style="height: 340px; overflow-y: auto;">
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

    .metric-card {
        cursor: default;
    }

    @media (min-width: 768px) {
        .col-md-2-4 {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const baseChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        };

        new Chart(document.getElementById('collectionTripSummaryChart'), {
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
        });

        new Chart(document.getElementById('fullOver80Chart'), {
            type: 'bar',
            data: {
                labels: @json($fullOver80Labels),
                datasets: [{
                    label: 'Full 80%+ Events',
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

        new Chart(document.getElementById('weekdayCollectionChart'), {
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
        });

        new Chart(document.getElementById('hourlyCollectionChart'), {
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
        });

        new Chart(document.getElementById('mostUsedBinChart'), {
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
        });

        new Chart(document.getElementById('compartmentCapacityChart'), {
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
        });
    });
</script>
@endpush
