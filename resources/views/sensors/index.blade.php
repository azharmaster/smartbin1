@extends($layout)
@section('content_title', 'Sensor')
@section('content')

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h5 class="mb-0">Sensor Data</h5>
        <div class="ms-auto">
            {{-- keep empty for future button --}}
        </div>
    </div>

    <div class="card-body">
        <div class="mb-4">
            <canvas id="capacityChart" height="120"></canvas>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
                <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
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
                    <tr>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const sensorData = @json($latestPerDevice);

    const labels = sensorData.map(item => item.device_id);
    const capacities = sensorData.map(item => item.capacity);
    const timestamps = sensorData.map(item => item.created_at);
</script>

<script>
    const ctx = document.getElementById('capacityChart').getContext('2d');

    new Chart(ctx, {
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
            plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const index = context.dataIndex;

                        // Convert MySQL datetime → JS Date
                        const date = new Date(timestamps[index]);

                        // Format nicely
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
</script>

@endsection
