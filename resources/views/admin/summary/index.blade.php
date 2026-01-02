@extends('layouts.app')
@section('content_title', 'Summary Reports')

@section('content')
<div class="container-fluid">

    <!-- Month Selector + Print Button -->
    <div class="row mb-4 align-items-end">
        <div class="col-md-4">
            <form method="GET" action="{{ route('summary') }}">
                <label class="form-label font-weight-bold">Select Month:</label>
                <input type="month" name="month" value="{{ $month }}" class="form-control" onchange="this.form.submit()">
            </form>
        </div>

        <div class="col-md-2 ms-auto text-end">
            <button class="btn btn-outline-primary mt-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4">

        <!-- Capacity Distribution -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white rounded-top">
                    Device Capacity Distribution
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="capacityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Devices by Floor -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-success text-white rounded-top">
                    Devices by Floor
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="floorChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mt-2">

        <!-- Full Bin Trend -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-danger text-white rounded-top">
                    Full Bin Trend (Daily)
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Full Counts per Bin -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-info text-white rounded-top">
                    Number of Times Each Bin Was Full
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="fullCountsChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Asset Images -->
    <div class="row g-3 mt-4">
        @foreach($assets as $asset)
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm text-center border-0 rounded-3 p-2 h-100">
                <h6 class="fw-bold mb-2">{{ $asset->asset_name }}</h6>
                @if($asset->picture)
                    <img src="{{ asset('storage/' . $asset->picture) }}"
                         alt="Asset Picture"
                         class="img-fluid rounded"
                         style="height: 150px; object-fit: cover; transition: transform 0.3s;"
                         onclick="window.open(this.src, '_blank')"
                         onmouseover="this.style.transform='scale(1.05)';"
                         onmouseout="this.style.transform='scale(1)';">
                @else
                    <div class="d-flex flex-column justify-content-center align-items-center py-5 text-muted">
                        <i class="far fa-image fs-2 mb-2"></i>
                        <span>No image</span>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>

<!-- Charts JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* Capacity Distribution */
new Chart(document.getElementById('capacityChart'), {
    type: 'doughnut',
    data: {
        labels: ['Empty', 'Half Full', 'Full'],
        datasets: [{
            data: [{{ $capacityStats->empty_count }}, {{ $capacityStats->half_count }}, {{ $capacityStats->full_count }}],
            backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c'],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

/* Devices by Floor */
new Chart(document.getElementById('floorChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($devicesByFloor->pluck('floor_name')) !!},
        datasets: [{
            label: 'Total Devices',
            data: {!! json_encode($devicesByFloor->pluck('total')) !!},
            backgroundColor: '#3498db',
            borderRadius: 5
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

/* Full Bin Trend */
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($fullTrend->pluck('date')) !!},
        datasets: [{
            label: 'Full Bins',
            data: {!! json_encode($fullTrend->pluck('total')) !!},
            borderColor: '#e74c3c',
            backgroundColor: 'rgba(231,76,60,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

/* Full Counts per Bin */
new Chart(document.getElementById('fullCountsChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($fullCounts->pluck('asset_name')) !!},
        datasets: [{
            label: 'Times Full',
            data: {!! json_encode($fullCounts->pluck('total_full')) !!},
            backgroundColor: '#8e44ad',
            borderRadius: 5
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>

<!-- Printable Styles -->
<style>
@media print {
    body { -webkit-print-color-adjust: exact; }
    .card { box-shadow: none !important; border: 1px solid #000; }
    .card-header { color: #000 !important; background-color: #fff !important; }
    canvas { page-break-inside: avoid; }
    .no-print { display: none !important; }
}
</style>
@endsection
