@extends($layout)
@section('content_title', 'Sensor Details')
@section('content')

<style>
/* ===== Mobile adjustments ===== */
@media (max-width: 576px) {
    .kpi-controls {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px;
    }

    .kpi-controls form {
        width: 100%;
    }

    .kpi-controls input,
    .kpi-controls select,
    .kpi-controls button {
        width: 100%;
    }

    .table-responsive {
        font-size: 0.8rem;
    }

    .chart-container {
        min-height: 250px !important;
    }
}

.chart-container {
    position: relative;
    width: 100%;
    min-height: 300px;
}

.table-responsive {
    overflow-x: auto;
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.badge-weak {
    background-color: #dc3545;
}

.badge-normal-signal {
    background-color: #17a2b8;
}

.badge-strong {
    background-color: #28a745;
}

.badge-abnormal {
    background-color: #6f42c1;
}

.rsrp-good {
    color: #28a745;
    font-weight: 600;
}

.rsrp-fair {
    color: #ffc107;
    font-weight: 600;
}

.rsrp-poor {
    color: #dc3545;
    font-weight: 600;
}
</style>

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <p class="mb-0"><i class="fas fa-chart-line"></i> Sensor Details - {{ $sensors->first()->device->device_name ?? 'Unknown' }}</p>
        <div class="ms-auto">
            <a href="{{ route('kpi.sensor.index', ['period' => $period]) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
                <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <!-- Device Info -->
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Device ID:</strong><br>
                <span class="text-muted">{{ $sensors->first()->device_id ?? 'N/A' }}</span>
            </div>
            <div class="col-md-3">
                <strong>Device Name:</strong><br>
                <span class="text-muted">{{ $sensors->first()->device->device_name ?? 'Unknown' }}</span>
            </div>
            <div class="col-md-3">
                <strong>Asset Name:</strong><br>
                <span class="text-muted">{{ $sensors->first()->device->asset->asset_name ?? 'Unknown' }}</span>
            </div>
            <div class="col-md-3">
                <strong>Period:</strong><br>
                <span class="text-muted">{{ $period }} day(s)</span>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-signal"></i> RSRP Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="rsrpChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-wave-square"></i> NSR Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="nsrChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-battery-full"></i> Battery Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="batteryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> Capacity Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="capacityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="d-flex justify-content-between align-items-center mb-3 kpi-controls">
            <form method="GET" class="d-flex">
                <label class="me-2">Period:</label>
                <select name="period" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                    <option value="1" {{ $period == 1 ? 'selected' : '' }}>Last 24 Hours</option>
                    <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="14" {{ $period == 14 ? 'selected' : '' }}>Last 14 Days</option>
                    <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 Days</option>
                </select>
            </form>

            <div class="text-muted">
                <small><i class="fas fa-info-circle"></i> Total Readings: {{ $sensors->count() }}</small>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Time</th>
                        <th>Battery (V)</th>
                        <th>Capacity (%)</th>
                        <th>RSRP (dBm)</th>
                        <th>NSR (dB)</th>
                        <th>Network Strength</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sensors as $index => $sensor)
                    <tr class="text-center">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sensor->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $sensor->battery }}</td>
                        <td>
                            <span class="{{ $sensor->capacity >= 85 ? 'text-danger fw-bold' : '' }}">
                                {{ $sensor->capacity }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $sensor->rsrp > -80 ? 'rsrp-good' : ($sensor->rsrp > -100 ? 'rsrp-fair' : 'rsrp-poor') }}">
                                {{ $sensor->rsrp }}
                            </span>
                        </td>
                        <td>{{ $sensor->nsr }}</td>
                        <td>
                            @if($sensor->network_strength === 'Strong')
                                <span class="badge badge-strong">{{ $sensor->network_strength }}</span>
                            @elseif($sensor->network_strength === 'Normal')
                                <span class="badge badge-normal-signal">{{ $sensor->network_strength }}</span>
                            @elseif($sensor->network_strength === 'Week')
                                <span class="badge badge-weak">{{ $sensor->network_strength }}</span>
                            @elseif($sensor->network_strength === 'Very Week')
                                <span class="badge badge-weak">{{ $sensor->network_strength }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $sensor->network_strength }}</span>
                            @endif
                        </td>
                        <td>
                            @if($sensor->capacity < 0 || $sensor->battery < 0)
                                <span class="badge badge-abnormal">Abnormal</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($sensors->count() === 0)
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i> No sensor data available for the selected period.
        </div>
        @endif

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const sensorData = @json($chartData);

    const labels = sensorData.map(d => d.time);
    const batteryData = sensorData.map(d => d.battery);
    const capacityData = sensorData.map(d => d.capacity);
    const rsrpData = sensorData.map(d => d.rsrp);
    const nsrData = sensorData.map(d => d.nsr);

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        scales: {
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    };

    // RSRP Chart
    new Chart(document.getElementById('rsrpChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'RSRP (dBm)',
                data: rsrpData,
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: {
                    title: {
                        display: true,
                        text: 'RSRP (dBm)'
                    }
                }
            }
        }
    });

    // NSR Chart
    new Chart(document.getElementById('nsrChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'NSR (dB)',
                data: nsrData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: {
                    title: {
                        display: true,
                        text: 'NSR (dB)'
                    }
                }
            }
        }
    });

    // Battery Chart
    new Chart(document.getElementById('batteryChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Battery (V)',
                data: batteryData,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: {
                    title: {
                        display: true,
                        text: 'Battery (V)'
                    }
                }
            }
        }
    });

    // Capacity Chart
    new Chart(document.getElementById('capacityChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Capacity (%)',
                data: capacityData,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Capacity (%)'
                    }
                }
            }
        }
    });
</script>

@endsection
