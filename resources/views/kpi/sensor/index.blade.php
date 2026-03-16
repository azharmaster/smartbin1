@extends($layout)
@section('content_title', 'KPI Sensor')
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

    .stat-card {
        margin-bottom: 10px;
    }

    .table-responsive {
        font-size: 0.85rem;
    }
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #28a745;
}

.stat-card.warning {
    border-left-color: #ffc107;
}

.stat-card.danger {
    border-left-color: #dc3545;
}

.stat-card.info {
    border-left-color: #17a2b8;
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-card .stat-label {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 5px;
}

.stat-card .stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #343a40;
}

.stat-card .stat-sub {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

.table-responsive {
    overflow-x: auto;
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    vertical-align: middle;
    font-size: 0.9rem;
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

.progress-thin {
    height: 8px;
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

.view-details-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <p class="mb-0"><i class="fas fa-signal"></i> Sensor Performance Metrics</p>
        <div class="ms-auto">
            <a href="{{ route('kpi.sensor.export', ['period' => $period]) }}" class="btn btn-sm btn-success">
                <i class="fas fa-download"></i> Export CSV
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

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-12 col-md-4 stat-card">
                <div class="stat-label">Total Devices</div>
                <div class="stat-value">{{ $summary['total_devices'] }}</div>
                <div class="stat-sub">Monitoring period: {{ $period }} days</div>
            </div>
            <div class="col-12 col-md-2 stat-card warning">
                <div class="stat-label">Weak Network</div>
                <div class="stat-value">{{ $summary['devices_with_weak_network'] }}</div>
                <div class="stat-sub">Devices affected</div>
            </div>
            <div class="col-12 col-md-2 stat-card danger">
                <div class="stat-label">Abnormal Data</div>
                <div class="stat-value">{{ $summary['devices_with_abnormal_data'] }}</div>
                <div class="stat-sub">Devices with issues</div>
            </div>
            <div class="col-12 col-md-2 stat-card info">
                <div class="stat-label">Avg Data Frequency</div>
                <div class="stat-value">{{ $summary['avg_data_frequency'] }}%</div>
                <div class="stat-sub">Expected: 30-min intervals</div>
            </div>
            <div class="col-12 col-md-2 stat-card success">
                <div class="stat-label">Avg RSRP</div>
                <div class="stat-value">{{ $summary['avg_rsrp'] }}</div>
                <div class="stat-sub">dBm</div>
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
                <small><i class="fas fa-info-circle"></i> Monitors network quality, data frequency & abnormal readings</small>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Device</th>
                        <th>Asset Name</th>
                        <th>Weak Network</th>
                        <th>Abnormal Data</th>
                        <th>Data Frequency</th>
                        <th>Avg RSRP</th>
                        <th>Avg NSR</th>
                        <th>Latest Reading</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sensorKpiData as $index => $data)
                    <tr class="text-center">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $data['device_name'] }}</strong>
                            <br>
                            <small class="text-muted">{{ $data['device_id'] }}</small>
                        </td>
                        <td>{{ $data['asset_name'] }}</td>
                        <td>
                            <span class="badge {{ $data['weak_network_percentage'] >= 20 ? 'badge-weak' : ($data['weak_network_percentage'] >= 10 ? 'badge-normal-signal' : 'badge-strong') }}">
                                {{ $data['weak_network_count'] }} ({{ $data['weak_network_percentage'] }}%)
                            </span>
                        </td>
                        <td>
                            @if($data['abnormal_data_count'] > 0)
                                <span class="badge badge-abnormal">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $data['abnormal_data_count'] }}
                                </span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="me-2">{{ $data['data_frequency_percentage'] }}%</span>
                                <div class="progress progress-thin" style="width: 80px;">
                                    <div class="progress-bar {{ $data['data_frequency_percentage'] >= 80 ? 'bg-success' : ($data['data_frequency_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                         style="width: {{ $data['data_frequency_percentage'] }}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">{{ $data['actual_readings'] }}/{{ $data['expected_readings'] }}</small>
                        </td>
                        <td>
                            <span class="{{ $data['avg_rsrp'] > -80 ? 'rsrp-good' : ($data['avg_rsrp'] > -100 ? 'rsrp-fair' : 'rsrp-poor') }}">
                                {{ $data['avg_rsrp'] }} dBm
                            </span>
                        </td>
                        <td>{{ $data['avg_nsr'] }} dB</td>
                        <td>
                            {{ $data['latest_capacity'] }}%
                            <br>
                            <small class="text-muted">{{ $data['latest_reading_time'] ? $data['latest_reading_time']->format('d M H:i') : 'N/A' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('kpi.sensor.details', ['deviceId' => $data['device_id'], 'period' => $period]) }}"
                               class="btn btn-sm btn-info view-details-btn"
                               title="View Details">
                                <i class="fas fa-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(count($sensorKpiData) === 0)
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i> No sensor data available for the selected period.
        </div>
        @endif

    </div>
</div>

<!-- Help Modal -->
<button type="button" data-bs-toggle="modal" data-bs-target="#kpiSensorHelpModal" style="
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #faa70c;
    color: #fff;
    border: none;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
    z-index: 999;
" title="KPI SENSOR Guide">
    ?
</button>

<div class="modal fade" id="kpiSensorHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">KPI SENSOR – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="font-size: 14px;">
        <h6><i class="fas fa-tools"></i> Purpose</h6>
        <p>
          The <strong>KPI SENSOR</strong> page monitors sensor health by tracking network quality, data transmission frequency,
          and detecting abnormal data patterns.
        </p>

        <hr>

        <h6><i class="fas fa-chart-line"></i> Key Metrics</h6>
        <ul>
          <li><strong>Total Devices:</strong> Number of sensors being monitored.</li>
          <li><strong>Weak Network:</strong> Devices with poor signal strength (RSRP &lt; -100 dBm).</li>
          <li><strong>Abnormal Data:</strong> Devices reporting invalid readings (negative values, etc.).</li>
          <li><strong>Avg Data Frequency:</strong> Percentage of expected readings received (target: every 30 minutes).</li>
          <li><strong>Avg RSRP:</strong> Average signal strength in dBm.</li>
          <li><strong>Avg NSR:</strong> Average noise-to-signal ratio in dB.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-table"></i> Table Columns</h6>
        <ul>
          <li><strong>Device:</strong> Device name and ID.</li>
          <li><strong>Asset Name:</strong> Associated bin name.</li>
          <li><strong>Weak Network:</strong> Count and percentage of weak signal readings.
              <ul>
                  <li><span class="badge bg-danger">Red</span>: ≥20% weak signals</li>
                  <li><span class="badge bg-info">Blue</span>: 10-20% weak signals</li>
                  <li><span class="badge bg-success">Green</span>: &lt;10% weak signals</li>
              </ul>
          </li>
          <li><strong>Abnormal Data:</strong> Count of invalid readings (negative capacity, battery, etc.).</li>
          <li><strong>Data Frequency:</strong> Visual indicator of data transmission reliability.</li>
          <li><strong>Avg RSRP:</strong> Signal strength (closer to 0 = better).
              <ul>
                  <li><span class="rsrp-good">Green</span>: &gt; -80 dBm (Good)</li>
                  <li><span class="rsrp-fair">Yellow</span>: -80 to -100 dBm (Fair)</li>
                  <li><span class="rsrp-poor">Red</span>: &lt; -100 dBm (Poor)</li>
              </ul>
          </li>
          <li><strong>Avg NSR:</strong> Signal quality (higher = better).</li>
          <li><strong>Latest Reading:</strong> Most recent capacity and timestamp.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-triangle"></i> Abnormal Data Detection</h6>
        <p>The system flags the following as abnormal:</p>
        <ul>
          <li>Negative capacity values</li>
          <li>Negative battery voltage</li>
          <li>Positive RSRP values (should be negative)</li>
          <li>Negative NSR values</li>
        </ul>

        <hr>

        <h6><i class="fas fa-filter"></i> Filtering</h6>
        <ul>
          <li>Use the <strong>Period</strong> dropdown to select the time range.</li>
          <li>Devices are sorted by <strong>Weak Network Percentage</strong> (highest first).</li>
        </ul>

        <hr>

        <h6><i class="fas fa-download"></i> Export</h6>
        <p>Click the <strong>Export CSV</strong> button to download the data for analysis.</p>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>Expected data frequency: 48 readings per day (every 30 minutes).</li>
          <li>RSRP values should be negative; closer to 0 indicates stronger signal.</li>
          <li>NSR values should be positive; higher values indicate better signal quality.</li>
          <li>Click <strong>Details</strong> to view detailed sensor data for a specific device.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

@endsection
