@extends('layouts.app')
@section('content_title')
    {{ $period === 'month' ? 'Monthly' : 'Weekly' }} Summary Report
@endsection

@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#summaryHelpModal" style="
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
    title="Summary Report Guide">
    ?
</button>

<div class="container-fluid">

    {{-- ================= HEADER ROW ================= --}}
    <div class="row mb-4 align-items-center no-print">
        <div class="col-md-4">
            <form method="GET" action="{{ route('summary') }}" class="d-flex gap-2 align-items-end">

            {{-- Period selector --}}
            <div>
                <label class="form-label fw-bold">Period</label>
                <select name="period"
                        class="form-select fw-bold"
                        onchange="this.form.submit()">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>
                        Today
                    </option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>
                        Monthly
                    </option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>
                        Weekly
                    </option>
                </select>
            </div>

            {{-- Month picker --}}
            @if ($period === 'month')
                <div>
                    <label class="form-label fw-bold">Select Month</label>
                    <input type="month"
                        name="month"
                        value="{{ $monthInput }}"
                        class="form-control fw-bold"
                        onchange="this.form.submit()">
                </div>
            @endif

            {{-- Week picker --}}
            @if ($period === 'week')
                <div>
                    <label class="form-label fw-bold">Select Week</label>
                    <input type="week"
                        name="week"
                        class="form-control fw-bold"
                        value="{{ request('week', now()->format('Y-\WW')) }}"
                        onchange="this.form.requestSubmit()">
                </div>
            @endif
            </form>

        </div>

        <div class="col-md-2 ms-auto text-end">
            <button class="btn btn-outline-primary mt-2" onclick="printDashboard()">
                <i class="fas fa-print me-1"></i> Print 
            </button>
        </div>

        <div class="col-md-2">
            <form method="POST" action="{{ route('summary.sendEmail') }}">
                @csrf

                <input type="hidden" name="period" value="{{ $period }}">

                @if($period === 'month')
                    <input type="hidden" name="month" value="{{ $monthInput }}">
                @endif

                @if($period === 'week')
                    <input type="hidden" name="week" value="{{ request('week') }}">
                @endif

                <button class="btn btn-success mt-2 w-100">
                    <i class="fas fa-envelope me-1"></i> Send Report
                </button>
            </form>
        </div>
    </div>

    {{-- ================= SUMMARY METRICS CARDS ================= --}}
    <div class="row g-4 mb-4 no-print">
        {{-- Total Full Events --}}
        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Full Events</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($summaryMetrics->total_full_events) }}</h2>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Average Fill Time --}}
        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Avg Fill Time</h6>
                            <h2 class="mb-0 fw-bold">{{ $summaryMetrics->avg_fill_time }} <small class="fs-6">hrs</small></h2>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-hourglass-half fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Average Clear Time --}}
        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Avg Clear Time</h6>
                            <h2 class="mb-0 fw-bold">{{ $summaryMetrics->avg_clear_time }} <small class="fs-6">hrs</small></h2>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-broom fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Cleaning This Month --}}
        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Cleaning</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($summaryMetrics->total_cleaning) }}</h2>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clipboard-check fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Active Bins --}}
        <div class="col-md-2-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Active Bins</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($summaryMetrics->total_active_bins) }}</h2>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-recycle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
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
    <div class="row g-4 mt-1 align-items-stretch">

        {{-- Average Clear Time --}}
        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-broom me-2"></i>
                    Average Bin Clear Time (Hours)
                </div>
                <div class="card-body p-2" style="height: 320px;">
                    <canvas id="avgClearChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Cleaning History --}}
        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header summary-gradient text-white">
                    <i class="fas fa-history me-2"></i>
                    Cleaning History
                </div>

                {{-- Scrollable content --}}
                <div class="card-body p-0" style="height: 320px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Asset</th>
                                <th>Device / Compartment</th>
                                <th>Cleaned At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cleaningLogs as $log)
                                <tr>
                                    <td>{{ $log->asset_name }}</td>
                                    <td>{{ $log->device_name }}</td>
                                    <td>{{ $log->cleaned_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No cleaning records found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ROW 3 ================= --}}
    <div class="row g-4 mt-1 align-items-stretch">
        {{-- Insight Box --}}
        <div class="col-lg-6 d-flex">
            <div class="card shadow-sm border-0 w-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-lightbulb me-2"></i>
                    {{ ucfirst($period) }} Insights
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
                    <img src="{{ asset('uploads/asset/' . $asset->picture) }}"
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
    const labels = @json($binAnalytics->pluck('asset_name'));

    /* Times Full */
    new Chart(document.getElementById('timesFullChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Times Became Full',
                data: @json($binAnalytics->pluck('times_full')),
                backgroundColor: 'rgba(142,68,173,0.8)',
                borderColor: '#8e44ad',
                borderWidth: 1
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
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Fill Time (Hours)',
                data: @json($binAnalytics->pluck('avg_fill_time')),
                backgroundColor: 'rgba(46,204,113,0.8)',
                borderColor: '#2ecc71',
                borderWidth: 1
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
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Clear Time (Hours)',
                data: @json($binAnalytics->pluck('avg_clear_time')),
                backgroundColor: 'rgba(231,76,60,0.8)',
                borderColor: '#e74c3c',
                borderWidth: 1
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
<script>
function printDashboard() {

    // Convert all canvases to images
    document.querySelectorAll('canvas').forEach(canvas => {

        // Skip if already converted
        if (canvas.dataset.printed) return;

        const img = document.createElement('img');
        img.src = canvas.toDataURL('image/png', 1.0);
        img.style.width = '100%';
        img.style.maxHeight = '320px';
        img.classList.add('print-chart');

        img.dataset.canvasId = canvas.id;
        canvas.dataset.printed = true;

        canvas.style.display = 'none';
        canvas.parentNode.appendChild(img);
    });

    // Give browser time to render images
    setTimeout(() => {
        window.print();

        // Restore after printing
        setTimeout(() => {
            document.querySelectorAll('.print-chart').forEach(img => {
                const canvas = document.getElementById(img.dataset.canvasId);
                if (canvas) canvas.style.display = '';
                img.remove();
            });

            document.querySelectorAll('canvas').forEach(c => {
                delete c.dataset.printed;
            });

        }, 500);

    }, 300);
}
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

    /* Hide elements marked no-print */
    .no-print {
        display: none !important;
    }

    /* Expand scrollable cards for print */
    .card-body {
        height: auto !important;
        overflow: visible !important;
    }

    /* Make charts taller and full width */
    .card-body canvas,
    .card-body img.print-chart {
        width: 100% !important;
        height: 450px !important; /* taller for paper */
    }

    /* Tables flow properly across pages */
    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid;
    }

    thead {
        display: table-header-group;
    }

    /* Prevent chart images from splitting across pages */
    img.print-chart {
        page-break-inside: avoid;
    }

    /* Ensure colors are preserved */
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
.card-body::-webkit-scrollbar {
    width: 6px;
}
.card-body::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

/* 5 equal columns for summary cards */
@media (min-width: 768px) {
    .col-md-2-4 {
        flex: 0 0 20%;
        max-width: 20%;
    }
}
</style>

<!-- Summary Report Help Modal -->
<div class="modal fade" id="summaryHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Summary Report – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-info-circle"></i> Purpose</h6>
        <p>
          This page provides an analytical summary of bin performance based on
          sensor data and cleaning records. Reports can be viewed by
          <strong>Today, Weekly, or Monthly</strong> period.
        </p>

        <hr>

        <h6><i class="fas fa-calendar-alt"></i> Selecting Report Period</h6>
        <ul>
          <li><strong>Today</strong> – Shows data for the current day only.</li>
          <li><strong>Weekly</strong> – Select a specific week to analyze performance.</li>
          <li><strong>Monthly</strong> – Select a specific month for overall trend analysis.</li>
          <li>The report updates automatically when you change the period.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-chart-line"></i> Charts Explanation</h6>
        <ul>
          <li>
            <strong>Number of Times Each Bin Became Full</strong><br>
            Shows how often each bin reached full capacity during the selected period.
            High numbers indicate high-traffic areas.
          </li>
          <li>
            <strong>Average Time to Become Full (Hours)</strong><br>
            Displays the average duration it takes for a bin to fill up.
            Shorter time means faster waste accumulation.
          </li>
          <li>
            <strong>Average Bin Clear Time (Hours)</strong><br>
            Shows the average time taken to clear a bin after it becomes full.
            Longer times may indicate delayed cleaning response.
          </li>
        </ul>

        <hr>

        <h6><i class="fas fa-history"></i> Cleaning History Table</h6>
        <ul>
          <li>Displays all cleaning records within the selected period.</li>
          <li>Shows asset name, device/compartment, and cleaning timestamp.</li>
          <li>If no records appear, it means no cleaning activity was logged during that period.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-lightbulb"></i> Insights Section</h6>
        <ul>
          <li>Provides quick operational insights based on bin behavior.</li>
          <li>Helps identify high-demand areas and optimize collection schedules.</li>
          <li>Supports data-driven maintenance decisions.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-image"></i> Asset Images</h6>
        <ul>
          <li>Displays all registered bins with their images.</li>
          <li>Click on an image to view it in full size.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-print"></i> Print & Email Report</h6>
        <ul>
          <li><strong>Print</strong> – Generates a printer-friendly version of the report.</li>
          <li><strong>Send Report</strong> – Sends the report summary via email to the currently logged-in user’s registered email address.</li>
          <li>Charts are automatically converted into images when printing.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>All metrics are calculated based on bin sensor state changes.</li>
          <li>Ensure correct period selection before printing or sending reports.</li>
          <li>This report is useful for performance monitoring and operational planning.</li>
        </ul>

      </div>

    </div>
  </div>
</div>

@endsection
