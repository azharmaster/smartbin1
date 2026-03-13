@extends($layout)
@section('content_title', 'KPI BIN')
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
}

.table tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.badge-full {
    background-color: #dc3545;
}

.badge-normal {
    background-color: #28a745;
}

.progress-thin {
    height: 8px;
}
</style>

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <p class="mb-0"><i class="fas fa-chart-bar"></i> Bin Performance Metrics</p>
        <div class="ms-auto">
            <a href="{{ route('kpi.bin.export', ['period' => $period]) }}" class="btn btn-sm btn-success">
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
            <div class="col-12 col-md-3 stat-card">
                <div class="stat-label">Total Bins</div>
                <div class="stat-value">{{ $summary['total_bins'] }}</div>
                <div class="stat-sub">Monitoring period: {{ $period }} days</div>
            </div>
            <div class="col-12 col-md-3 stat-card warning">
                <div class="stat-label">Avg Full Count</div>
                <div class="stat-value">{{ $summary['bins_full_avg'] }}</div>
                <div class="stat-sub">Times bin reached ≥85%</div>
            </div>
            <div class="col-12 col-md-3 stat-card danger">
                <div class="stat-label">Most Problematic Bin</div>
                <div class="stat-value" style="font-size: 18px;">{{ $summary['most_problematic_bin'] }}</div>
                <div class="stat-sub">Highest full count</div>
            </div>
            <div class="col-12 col-md-3 stat-card info">
                <div class="stat-label">Avg Emptying Time</div>
                <div class="stat-value">{{ $summary['avg_emptying_time'] }}<small style="font-size: 14px;"> hrs</small></div>
                <div class="stat-sub">Full to empty duration</div>
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
                <small><i class="fas fa-info-circle"></i> Shows bins sorted by frequency of reaching full capacity</small>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Asset Name</th>
                        <th>Location</th>
                        <th>Full Count</th>
                        <th>Avg Emptying Duration</th>
                        <th>Current Capacity</th>
                        <th>Avg Capacity</th>
                        <th>Total Readings</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bins as $index => $bin)
                    <tr class="text-center">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $bin['asset']->asset_name }}</strong>
                            <br>
                            <small class="text-muted">{{ $bin['asset']->serialNo ?? '' }}</small>
                        </td>
                        <td>{{ $bin['asset']->location ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $bin['full_count'] >= 3 ? 'badge-full' : 'badge-normal' }}">
                                {{ $bin['full_count'] }}
                            </span>
                        </td>
                        <td>
                            @if($bin['avg_emptying_duration'])
                                {{ $bin['avg_emptying_duration'] }} hrs
                            @else
                                <span class="text-muted">No data</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="me-2">{{ $bin['current_capacity'] }}%</span>
                                <div class="progress progress-thin" style="width: 80px;">
                                    <div class="progress-bar {{ $bin['current_capacity'] >= 85 ? 'bg-danger' : ($bin['current_capacity'] >= 50 ? 'bg-warning' : 'bg-success') }}"
                                         style="width: {{ $bin['current_capacity'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $bin['avg_capacity'] }}%</td>
                        <td>{{ $bin['total_readings'] }}</td>
                        <td>
                            @if($bin['full_count'] >= 5)
                                <span class="badge bg-danger">Critical</span>
                            @elseif($bin['full_count'] >= 3)
                                <span class="badge bg-warning">Warning</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($bins->count() === 0)
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i> No bin data available for the selected period.
        </div>
        @endif

    </div>
</div>

<!-- Help Modal -->
<button type="button" data-bs-toggle="modal" data-bs-target="#kpiBinHelpModal" style="
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
" title="KPI BIN Guide">
    ?
</button>

<div class="modal fade" id="kpiBinHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">KPI BIN – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="font-size: 14px;">
        <h6><i class="fas fa-tools"></i> Purpose</h6>
        <p>
          The <strong>KPI BIN</strong> page helps monitor bin performance by tracking how often bins reach full capacity
          and measuring the time taken to empty them.
        </p>

        <hr>

        <h6><i class="fas fa-chart-line"></i> Key Metrics</h6>
        <ul>
          <li><strong>Total Bins:</strong> Number of bins being monitored.</li>
          <li><strong>Avg Full Count:</strong> Average number of times bins reached ≥85% capacity.</li>
          <li><strong>Most Problematic Bin:</strong> The bin with the highest full count.</li>
          <li><strong>Avg Emptying Time:</strong> Average time (in hours) from full to empty.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-table"></i> Table Columns</h6>
        <ul>
          <li><strong>Asset Name:</strong> Name of the bin.</li>
          <li><strong>Location:</strong> Physical location of the bin.</li>
          <li><strong>Full Count:</strong> Number of times the bin reached ≥85% capacity.</li>
          <li><strong>Avg Emptying Duration:</strong> Average time from full (≥85%) to empty (<20%).</li>
          <li><strong>Current Capacity:</strong> Latest capacity reading with visual indicator.</li>
          <li><strong>Avg Capacity:</strong> Average capacity over the selected period.</li>
          <li><strong>Total Readings:</strong> Number of sensor readings recorded.</li>
          <li><strong>Status:</strong> Overall status (Critical: ≥5, Warning: ≥3, Normal: <3).</li>
        </ul>

        <hr>

        <h6><i class="fas fa-filter"></i> Filtering</h6>
        <ul>
          <li>Use the <strong>Period</strong> dropdown to select the time range (24 hours, 7 days, 14 days, 30 days).</li>
          <li>Bins are sorted by <strong>Full Count</strong> (highest first) to identify problem areas.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-download"></i> Export</h6>
        <p>Click the <strong>Export CSV</strong> button to download the data for further analysis.</p>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>A bin is considered "full" when capacity reaches ≥85%.</li>
          <li>Emptying duration is calculated from full (≥85%) to empty (<20%).</li>
          <li>Bins with no sensor data in the selected period are not shown.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

@endsection
