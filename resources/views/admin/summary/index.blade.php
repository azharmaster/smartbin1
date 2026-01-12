@extends('layouts.app')
@section('content_title', 'Monthly Summary Report')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ROW ================= --}}
    <div class="row mb-4 align-items-center no-print">
        <div class="col-md-4">
            <form method="GET" action="{{ route('summary') }}">
                <div class="input-group">
                    <span class="input-group-text bg-success text-white">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <input type="month"
                           name="month"
                           value="{{ $monthInput }}"
                           class="form-control fw-bold"
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <div class="col-md-2 ms-auto text-end">
            <button class="btn btn-outline-primary mt-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print 
            </button>
        </div>

        <div class="col-md-2">
            <form method="POST" action="{{ route('summary.sendEmail') }}">
                @csrf
                <input type="hidden" name="month" value="{{  $monthInput }}">
                <button class="btn btn-success mt-2 w-100">
                    <i class="fas fa-envelope me-1"></i> Send Report
                </button>
            </form>
        </div>
    </div>

    {{-- ================= CHART ROW 1 ================= --}}
    <div class="row g-4">
        {{-- Times Bin Became Full --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Number of Times Each Bin Became Full
                </div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="timesFullChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Average Time to Fill --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-hourglass-half me-2"></i>
                    Average Time for Bin to Become Full (Hours)
                </div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="avgFillChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= CHART ROW 2 ================= --}}
    <div class="row g-4 mt-1">
        {{-- Average Clear Time --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-broom me-2"></i>
                    Average Bin Clear Time (Hours)
                </div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="avgClearChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Insight Box --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-lightbulb me-2"></i>
                    Monthly Insights
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Bins with higher fill frequency indicate high-traffic areas.</li>
                        <li>Long clear times suggest delayed response or inefficient routing.</li>
                        <li>Fast fill rates may require increased collection frequency.</li>
                        <li>All metrics are calculated based on sensor state transitions.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ASSET IMAGES ================= --}}
    <div class="row g-3 mt-4">
        @foreach($assets as $asset)
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0 text-center p-2 h-100 asset-card">
                <h6 class="fw-bold mb-2">{{ $asset->asset_name }}</h6>

                @if($asset->picture)
                    <img src="{{ asset('storage/' . $asset->picture) }}"
                         class="img-fluid rounded asset-img"
                         onclick="window.open(this.src, '_blank')">
                @else
                    <div class="text-muted py-5">
                        <i class="far fa-image fs-2 mb-2"></i><br>
                        No image
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>

{{-- ================= CHARTS ================= --}}
<script src="//unpkg.com/alpinejs" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const labels = @json($binAnalytics->pluck('device_name'));

    /* Times Full */
    new Chart(document.getElementById('timesFullChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Times Became Full',
                data: @json($binAnalytics->pluck('times_full')),
                borderColor: '#8e44ad',
                backgroundColor: 'rgba(142,68,173,0.2)',
                fill: true,
                tension: 0.3,       // smooth curve
                pointRadius: 5,
                pointBackgroundColor: '#8e44ad'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, min: 0 }
            }
        }
    });

    /* Avg Fill Time */
    new Chart(document.getElementById('avgFillChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Fill Time (Hours)',
                data: @json($binAnalytics->pluck('avg_fill_time')),
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46,204,113,0.2)',
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: '#2ecc71'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, min: 0 }
            }
        }
    });

    /* Avg Clear Time */
    new Chart(document.getElementById('avgClearChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Clear Time (Hours)',
                data: @json($binAnalytics->pluck('avg_clear_time')),
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231,76,60,0.2)',
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: '#e74c3c'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, min: 0 }
            }
        }
    });
});
</script>

<style>
.summary-gradient {
     background: linear-gradient(270deg, #1b5e20, #4bb352ff, #1b5e20);
    background-size: 400% 400%;
    animation: smartbinGradient 8s ease infinite;
}

/* Animation */
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
.summary-gradient-red {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
}
.summary-gradient-purple {
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
}
.asset-card:hover {
    transform: translateY(-4px);
    transition: 0.3s;
}
.asset-img {
    height: 150px;
    object-fit: cover;
}
@media print {
    .no-print { display: none !important; }
    canvas { page-break-inside: avoid; }
}
</style>
@endsection
