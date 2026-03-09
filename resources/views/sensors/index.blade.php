@extends($layout)
@section('content_title', 'Sensor')
@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#sensorsHelpModal" style="
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
         <p class="mb-0"><i class="fas fa-table"></i> Sensors Data</p>
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
                    placeholder="Search Device ID...">
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
                        <th>Device Name</th>
                        <th>Device ID</th>
                        <th>Battery</th>
                        <th>Capacity</th>
                        <th>RSRP</th>
                        <th>SNR</th>
                        <th>Network Strength</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sensors as $index => $sensor)
                    <tr class="text-center">
                        <!-- Correct numbering across pages -->
                        <td>{{ $sensors->firstItem() + $index }}</td>
                        <td>
                            {{ $sensor->device->asset->asset_name ?? 'Unknown Bin' }}
                            <br>
                            <small class="text-muted">{{ $sensor->device->device_name ?? 'Unknown Device' }}</small>
                        </td>

                        <td>{{ $sensor->device_id }}</td>
                        <td>{{ $sensor->battery_percentage }}%</td>
                        <td>{{ $sensor->capacity }}%</td>
                        <td>{{ $sensor->rsrp }}</td>
                        <td>{{ $sensor->nsr }}</td>
                        <td>
                            @if($sensor->network_strength === 'Strong')
                                <span class="badge bg-success">{{ $sensor->network_strength }}</span>
                            @elseif($sensor->network_strength === 'Normal')
                                <span class="badge bg-info">{{ $sensor->network_strength }}</span>
                            @elseif($sensor->network_strength === 'Week')
                                <span class="badge bg-warning">{{ $sensor->network_strength }}</span>
                            @elseif($sensor->network_strength === 'Very Week')
                                <span class="badge bg-danger">{{ $sensor->network_strength }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $sensor->network_strength }}</span>
                            @endif
                        </td>
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-tools"></i> Purpose</h6>
        <p>
          The <strong>Sensor</strong> page provides a complete overview of all connected devices.
          You can monitor <strong>capacity, battery, RSRP, NSR,</strong> and see the latest readings for each device.
        </p>

        <hr>

        <h6><i class="fas fa-chart-line"></i> Chart Overview</h6>
        <ul>
            <li>The line chart shows the <strong>latest capacity percentage</strong> for each device over time.</li>
            <li>Hover over points to see <strong>exact capacity values and timestamps</strong>.</li>
            <li>The chart updates automatically whenever new data is available.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-table"></i> Table Overview</h6>
        <ul>
          <li><strong>#</strong>: Serial number across paginated results.</li>
          <li><strong>Device ID</strong>: Unique identifier of the sensor/device.</li>
          <li><strong>Battery</strong>: Current battery level in percentage (%).</li>
          <li><strong>Capacity</strong>: Current fill level of the bin in percentage (%).</li>
          <li><strong>RSRP</strong>: Reference Signal Received Power – measures the signal strength of the device in dBm. 
              <br> 
              <strong>Interpretation:</strong>
              <ul>
                <li>Closer to 0 → stronger signal</li>
                <li>Typical ranges:</li>
                <li>-70 dBm → Excellent signal</li>
                <li>-85 dBm → Good signal</li>
                <li>-100 dBm → Fair signal</li>
                <li>-110 dBm → Weak signal</li>
                <li>-120 dBm → Very weak / unstable</li>
              </ul>
              <em>Example:</em> RSRP = -80 dBm means the device has a good signal.</em>
          </li>
          <li><strong>NSR</strong>: Noise-to-Signal Ratio – measures signal quality relative to interference in dB.
              <br>
              <strong>Interpretation:</strong>
              <ul>
                <li>Higher NSR → better quality</li>
                <li>Typical ranges:</li>
                <li>15 dB → Excellent quality</li>
                <li>10 dB → Good quality</li>
                <li>5 dB → Fair quality</li>
                <li>2 dB → Poor quality / prone to errors</li>
              </ul>
              <em>Example:</em> NSR = 12 dB means the device has good signal quality.</em>
          </li>
          <li><strong>Network Strength</strong>: Visual indicator of network connection quality based on RSRP value:
              <br>
              <ul>
                <li><strong>Strong</strong>: RSRP > -80 dBm (Excellent connection)</li>
                <li><strong>Normal</strong>: RSRP between -80 and -100 dBm (Good connection)</li>
                <li><strong>Week</strong>: RSRP between -100 and -110 dBm (Fair connection)</li>
                <li><strong>Very Week</strong>: RSRP < -110 dBm (Poor connection)</li>
              </ul>
          </li>
          <li><strong>Time</strong>: Timestamp of the latest data reading from the device.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-search"></i> Searching & Pagination</h6>
        <ul>
            <li>Use the search box to filter by <strong>Device ID</strong>.</li>
            <li>Select <strong>Rows per page</strong> to control pagination and how many records are displayed per page.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>The table shows the <strong>most recent readings</strong> for each sensor/device.</li>
          <li>Ensure devices are active and transmitting data for accurate monitoring.</li>
          <li>The page layout is fully responsive; charts and tables adapt automatically for mobile or desktop screens.</li>
          <li>RSRP and NSR values can fluctuate; they provide an indication of signal strength and quality at the moment of measurement.</li>
        </ul>

      </div>

    </div>
  </div>
</div>

<!--OPEN HELP MODAL -->
<script>
function openHelp() {
    $('#helpModal').modal('show');
}
</script>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const sensorData = @json($latestPerDevice);

    // Use last 4 digits of device_id for chart labels
    const labels = sensorData.map(item => item.device_id_short);
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
                        text: 'Device ID (Last 4 Digits)'
                    },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45
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
