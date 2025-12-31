@extends('layouts.admin')

@section('content_title', 'Summary Reports')

@section('content')
<div class="container-fluid">

    <!-- ROW 1 -->
    <div class="row">
        <div class="col-md-6">
            <canvas id="capacityChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="floorChart"></canvas>
        </div>
    </div>

    <!-- ROW 2 -->
    <div class="row mt-4">
        <div class="col-md-6">
            <canvas id="taskChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="trendChart"></canvas>
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
                {{ $capacityStats->empty }},
                {{ $capacityStats->half }},
                {{ $capacityStats->full }}
            ]
        }]
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
    }
});
</script>
@endsection
