@extends('layouts.app')

@section('content_title', 'Summary Reports')

@section('content')
<div class="container-fluid">

    <div class="row">

        <!-- Capacity -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Device Capacity Distribution</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="capacityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices by Floor -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">Devices by Floor</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="floorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <!-- Task Status -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">Task Status</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="taskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Bin Trend -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">Full Bin Trend</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* =========================
 * CAPACITY PIE
 * ========================= */
new Chart(document.getElementById('capacityChart'), {
    type: 'pie',
    data: {
        labels: ['Empty', 'Half Full', 'Full'],
        datasets: [{
            data: [
                {{ $capacityStats->empty_count }},
                {{ $capacityStats->half_count }},
                {{ $capacityStats->full_count }}
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

/* =========================
 * DEVICES BY FLOOR
 * ========================= */
new Chart(document.getElementById('floorChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($devicesByFloor->pluck('floor_name')) !!},
        datasets: [{
            data: {!! json_encode($devicesByFloor->pluck('total')) !!}
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

/* =========================
 * TASK STATUS
 * ========================= */
new Chart(document.getElementById('taskChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($taskStats->pluck('status')) !!},
        datasets: [{
            data: {!! json_encode($taskStats->pluck('total')) !!}
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

/* =========================
 * FULL BIN TREND
 * ========================= */
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($fullTrend->pluck('date')) !!},
        datasets: [{
            data: {!! json_encode($fullTrend->pluck('total')) !!}
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endsection
