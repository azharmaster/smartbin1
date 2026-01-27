@extends($layout)
@section('content_title', 'Sensor')
@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#sensorsHelpModal" style="
        position: fixed;
        top: 90px;
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
    "
    title="Sensors Guide"
>
    ?
</button>

<style>
/* ===== Mobile adjustments ===== */
@media (max-width: 576px) {

    /* Stack controls vertically */
    .sensor-controls {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px;
    }

    .sensor-controls form {
        width: 100%;
    }

    .sensor-controls input,
    .sensor-controls select,
    .sensor-controls button {
        width: 100%;
    }

    /* Increase chart height on mobile */
    #capacityChart {
        max-height: 400px; /* bigger for mobile */
        min-height: 350px;
    }

    /* Make table text smaller */
    table th,
    table td {
        font-size: 0.8rem;
        white-space: nowrap;
    }
}

/* ===== Table Styling ===== */
.table-responsive {
    overflow-x: auto;
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    vertical-align: middle;
}

.table tbody td {
    vertical-align: middle;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.03);
}

.table-hover tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.1);
}

.table td,
.table th {
    padding: 0.65rem 0.75rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.9rem;
    }
}
</style>

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h5 class="mb-0">Sensor Data</h5>
        <div class="ms-auto">
            {{-- keep empty for future button --}}
        </div>
    </div>

    <div class="card-body">
        <div class="mb-4">
            <div class="position-relative w-100" style="min-height:300px;">
                <canvas id="capacityChart"></canvas>
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
                <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 sensor-controls">
            <form method="GET" class="d-flex">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control form-control-sm me-2"
                    placeholder="Search Device ID or Network...">
                <button type="submit" class="btn btn-sm btn-success">Search</button>
            </form>

            <form method="GET" class="d-flex">
                <label class="me-2">Rows per page:</label>
                <select name="perPage" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" {{ request('perPage', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Device ID</th>
                        <th>Battery</th>
                        <th>Capacity</th>
                        <th>Network</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sensors as $index => $sensor)
                    <tr class="text-center">
                        <!-- Correct numbering across pages -->
                        <td>{{ $sensors->firstItem() + $index }}</td>
                        <td>{{ $sensor->device_id }}</td>
                        <td>{{ $sensor->battery }}%</td>
                        <td>{{ $sensor->capacity }}%</td>
                        <td>{{ $sensor->network }}</td>
                        <td>{{ $sensor->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    <div class="mt-3 d-flex justify-content-end">
        {{ $sensors->links('pagination::bootstrap-5') }}
    </div>

    </div>
</div>

<!-- Sensors Help Modal -->
<div class="modal fade" id="sensorsHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Sensor Data – User Guide</h5>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-tools"></i> Purpose</h6>
        <p>
          The <strong>Sensor</strong> page provides an overview of all connected devices.
          You can monitor <strong>capacity, battery, network,</strong> and the latest activity for each device.
        </p>

        <hr>

        <h6><i class="fas fa-chart-line"></i> Chart Overview</h6>
        <ul>
            <li>The line chart shows the <strong>latest capacity percentage</strong> for each device.</li>
            <li>Hover over points to see <strong>exact values and timestamps</strong>.</li>
            <li>The chart updates automatically when new data is available.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-table"></i> Table Overview</h6>
        <ul>
          <li><strong>#</strong>: Serial number across pages.</li>
          <li><strong>Device ID</strong>: Unique identifier of the sensor/device.</li>
          <li><strong>Battery</strong>: Current battery level percentage.</li>
          <li><strong>Capacity</strong>: Current capacity percentage.</li>
          <li><strong>Network</strong>: Device network status or signal.</li>
          <li><strong>Time</strong>: Last data update timestamp.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-search"></i> Searching & Pagination</h6>
        <ul>
            <li>Use the search box to filter by <strong>Device ID</strong> or <strong>Network</strong>.</li>
            <li>Adjust <strong>Rows per page</strong> to control pagination.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>Data displayed reflects the <strong>latest reading</strong> from each device.</li>
          <li>Ensure your devices are active and transmitting for accurate display.</li>
          <li>The page is fully responsive; charts and tables adapt for mobile.</li>
        </ul>

      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const sensorData = @json($latestPerDevice);

    const labels = sensorData.map(item => item.device_id);
    const capacities = sensorData.map(item => item.capacity);
    const timestamps = sensorData.map(item => item.created_at);
</script>

<script>
    const ctx = document.getElementById('capacityChart').getContext('2d');

    const capacityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Latest Capacity (%)',
                data: capacities,
                tension: 0.3,
                fill: false,
                borderWidth: 2,

                borderColor: '#28a745',   // ✅ line color (Bootstrap green)
                backgroundColor: 'rgba(46,204,113,0.2)',
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#28a745',

                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const date = new Date(timestamps[index]);
                            const formatted = date.toLocaleString('en-MY', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });

                            return [
                                `Capacity: ${context.raw}%`,
                                `Time: ${formatted}`
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Capacity (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Device ID'
                    }
                }
            }
        }
    });

    // Update chart height dynamically on resize
    window.addEventListener('resize', () => {
        const chartCanvas = document.getElementById('capacityChart');
        if (window.innerWidth < 576) {
            chartCanvas.style.maxHeight = '400px';
            chartCanvas.style.minHeight = '350px';
        } else {
            chartCanvas.style.maxHeight = '300px';
            chartCanvas.style.minHeight = '300px';
        }
        capacityChart.resize();
    });
</script>

@endsection
