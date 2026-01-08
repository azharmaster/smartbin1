@extends('layouts.app')
@section('content_title', 'Dashboard')

@section('content')

<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 20px;
}

/* === STATUS CARD CONTAINER === */
.status-card {
    position: relative;
    flex: 1 1 180px;
    width: 19%;
    height: 150px;

    background: #ffffff;
    color: #111;
    border-radius: 14px;
    overflow: hidden;

    box-shadow: 0 8px 22px rgba(0,0,0,0.12);
    transition: background .25s ease, color .25s ease,
                transform .2s ease, box-shadow .2s ease;

    margin-left: 10px;
}

.status-card:hover {
    background: #1b5e20; /* DARK GREEN */
    color: #ffffff;

    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.35);
}

.status-body {
    padding: 18px 20px 42px;
    height: 100%;
    position: relative;
}

.status-title {
    font-size: 14px;
    font-weight: 600;
    opacity: 0.9;
    color: #111;
}

.status-content {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-number {
    font-size: 36px;
    font-weight: 800;
    color: #111;
}

.status-icon {
    position: absolute;
    right: 14px;
    top: 14px;
    font-size: 48px;
    opacity: 0.30;
    color: #111;
}

.status-card:hover .status-title,
.status-card:hover .status-number,
.status-card:hover .status-icon,
.status-card:hover .status-footer,
.status-card:hover .status-trend-footer,
.status-card:hover .status-more {
    color: #ffffff;
}

/* ICON COLORS */
.card-total .status-icon {
    color: #1e88e5;
}

.card-full .status-icon {
    color: #e53935; /* strong red */
}

.card-half .status-icon {
    color: #fb8c00;
}

.card-empty .status-icon {
    color: #43a047;
}

.card-undetected .status-icon {
    color: #616161;
}

.status-trend {
    font-size: 12px;
    margin-top: 8px;
    color: #000000ff;
}

.status-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 8px 14px;
    font-size: 12px;
    font-weight: 600;

    background: rgba(0,0,0,0.04);
    color: #111;

    transition: background .25s ease, color .25s ease;
}

.status-card:hover .status-footer {
    background: rgba(0,0,0,0.25);
}

a.status-footer {
    text-decoration: none;
    color: inherit;
}

a.status-footer:hover {
    text-decoration: none;
}

/* LEFT – trend */
.status-trend-footer {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #000000ff;
}

.status-trend-footer small {
    color: rgba(255,255,255,0.8);
    font-weight: 500;
}

/* RIGHT – more info */
.status-more {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    opacity: 0.9;
    transition: opacity .2s ease;
}

.status-card:hover .status-more {
    opacity: 1;
}


/* Container: tighter spacing */
.full-devices-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 14px; /* smaller gap */
    margin-left: 5px;
}

/* FULL DEVICE CARD */
.full-device-card {
    background-color: #6f060687;
    border: 2px solid #ff4d4d;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(255, 0, 0, 0.5);
    transition: transform 0.2s, box-shadow 0.2s;
}

.full-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 18px rgba(255, 0, 0, 0.7);
}

/* FULL STATUS – Pulsing badge */
.full-status {
    background-color: #FF0000;
    padding: 0.4em 0.9em;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 0 8px #FF0000, 0 0 12px #FF4d4d, 0 0 18px #FF6666;
    animation: pulse 1.5s infinite;
}

/* PULSE ANIMATION */
@keyframes pulse {
    0% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
    50% { box-shadow: 0 0 10px #CC0000, 0 0 14px #D93333, 0 0 20px #E06666; transform: scale(1.05); }
    100% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
}

/* HALF DEVICE CARD (similar style but yellow, no pulse) */
.half-device-card {
    background-color: #8f6f0587; /* translucent yellow-brown */
    border: 2px solid #f7d24a;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(255, 208, 0, 0.45);
    transition: transform 0.2s, box-shadow 0.2s;
}

.half-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 14px rgba(255, 208, 0, 0.6);
}

/* HALF STATUS – No pulse */
.half-status {
    background-color: #FFD700;
    padding: 0.4em 0.9em;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 0 8px #FFD70090;
}

/* Title size */
.full-device-card .fw-bold.fs-4,
.half-device-card .fw-bold.fs-4 {
    font-size: 1.3rem;
    font-weight: bold;
}

</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

@php
function trend($current, $previous) {
    if ($current > $previous) return ['▲'];
    if ($current < $previous) return ['▼'];
    return ['—', 'text-muted'];
}
@endphp

<div class="d-flex flex-wrap">
    <div class="status-card card-total">
        <div class="status-body">
            <div class="status-title">Total Devices</div>
            <div class="status-content">
                <i class="fas fa-satellite-dish status-icon"></i>
                <span class="status-number">{{ $totalDevices }}</span>
            </div>
        </div>
        <div class="status-footer">
            <a href="{{ route('admin.main.dashboard') }}" class="status-footer">
                <span class="status-trend-footer">
                    {{ $totalTrend['icon'] }}
                    {{ $totalTrend['value'] }}
                </span>

                <span class="status-more">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </span>
            </a>
        </div>
    </div>

    <div class="status-card card-full">
        <div class="status-body">
            <div class="status-title">Full Devices</div>
            <div class="status-content">
                <i class="fas fa-trash status-icon"></i>
                <span class="status-number">{{ $fullDevices }}</span>
            </div>
        </div>
        <div class="status-footer">
            <a href="{{ route('admin.main.dashboard') }}" class="status-footer">
                <span class="status-trend-footer"></span>
                <span class="status-more">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </span>
            </a>
        </div>
    </div>

    <div class="status-card card-half">
        <div class="status-body">
            <div class="status-title">Half Full</div>
            <div class="status-content">
                <i class="fas fa-exclamation-triangle status-icon"></i>
                <span class="status-number">{{ $halfDevices }}</span>
            </div>
        </div>
        <div class="status-footer">
            <a href="{{ route('admin.main.dashboard') }}" class="status-footer">
                <span class="status-trend-footer"></span>
                <span class="status-more">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </span>
            </a>
        </div>
    </div>

    <div class="status-card card-empty">
        <div class="status-body">
            <div class="status-title">Empty Devices</div>
            <div class="status-content">
                <i class="fas fa-recycle status-icon"></i>
                <span class="status-number">{{ $emptyDevices }}</span>
            </div>
        </div>
        <div class="status-footer">
            <a href="{{ route('admin.main.dashboard') }}" class="status-footer">
                <span class="status-trend-footer"></span>
                <span class="status-more">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </span>
            </a>
        </div>
    </div>

    <div class="status-card card-undetected">
        <div class="status-body">
            <div class="status-title">Undetected</div>
            <div class="status-content">
                <i class="fas fa-minus-circle status-icon"></i>
                <span class="status-number">{{ $undetectedDevices }}</span>
            </div>
        </div>
        <div class="status-footer">
            <a href="#" class="status-footer">
                <span class="status-trend-footer"></span>
                <span class="status-more">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </span>
            </a>
        </div>
    </div>
</div>

<div class="container-fluid mt-4">

    <!-- ROW 1: SMARTBIN CHART -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6">
                        <i class="fas fa-trash"></i> SmartBin Clear Time
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="smartBinClearChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

<!-- SmartBin Gradient Style -->
<style>
.smartbin-gradient {
    background: linear-gradient(135deg, #1b5e20, #4bb352ff);
}
</style>

    <!-- ROW 2: USERS + TODO -->
    <div class="row">
        <!-- LEFT COLUMN: MAP -->
        <div class="col-lg-6">
            <!-- SIMPLE USER LIST BELOW TODO LIST -->
            <div class="card p-3">
                <h5 class="mb-3 fs-6"><i class="fas fa-users"></i> Users</h5>
                <ul class="list-group">
                    @foreach($users as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $user->name }}
                            <span class="badge bg-success">
                                @switch($user->role)
                                    @case(1) Admin @break
                                    @case(2) Staff @break
                                    @case(3) User @break
                                    @case(4) Supervisor @break
                                    @default Unknown
                                @endswitch
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-6">

            <!-- Activity Calendar -->
            <div class="card shadow-sm mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 fs-6">
                        <a href="{{ route('holidays.index') }}" class="text-white text-decoration-none">
                            <i class="fas fa-calendar-alt me-2"></i> Calendar
                        </a>
                    </h5>
                </div>

                <div class="card-body p-2">
                    <div id="holidaycalendar"></div>
                </div>
            </div>

            <style>
            /* Remove underline / hover highlight on day numbers */
            .fc-daygrid-day-number {
                text-decoration: none !important;
            }

            /* Change hover background */
            .fc-daygrid-day:hover {
                background-color: #f4f6f9;
            }

            /* Today highlight */
            .fc-day-today {
                background-color: rgba(0, 123, 255, 0.1) !important;
            }

            /* Event style */
            .fc-event {
                border-radius: 6px;
                padding: 2px 4px;
                font-size: 0.85rem;
            }

            /* Add gap between view buttons (Month / Week / Day) */
            .fc .fc-button-group {
                gap: 6px;
            }

            /* Optional: make buttons slightly rounded */
            .fc .fc-button {
                border-radius: 6px;
            }
            </style>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🔥 SWEETALERT POP-UP FOR FULL BINS
    @if($fullDevices > 0)
        Swal.fire({
            icon: 'warning',
            title: 'Full Trash Bins Detected!',
            html: '<b>{{ $fullDevices }}</b> bin(s) are FULL and need to be cleared.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#d33',
        });
    @endif

    //smartbin tracker
const binCtx = document.getElementById('smartBinClearChart').getContext('2d');

const binGradient = binCtx.createLinearGradient(0, 0, 0, 300);
binGradient.addColorStop(0, 'rgba(33, 60, 17, 0.79)');
binGradient.addColorStop(1, 'rgba(21, 68, 20, 0.05)');

new Chart(binCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($smartBinClearTimes->pluck('device_name')) !!},
        datasets: [{
            label: 'Hours to Clear',
            data: {!! json_encode($smartBinClearTimes->pluck('hours')) !!},
            borderColor: '#1b5e20',
            backgroundColor: binGradient,
            fill: true,
            tension: 0.45,
            borderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 9,
            pointBackgroundColor: '#1b5e20'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1f2933',
                titleColor: '#fff',
                bodyColor: '#d1d5db',
                cornerRadius: 8,
                callbacks: {
                    label: ctx => `${ctx.raw} hours`
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    color: '#6b7280',
                    maxRotation: 45,
                    minRotation: 30
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)',
                    borderDash: [4, 4]
                },
                title: {
                    display: true,
                    text: 'Hours',
                    color: '#374151'
                },
                ticks: {
                    color: '#6b7280'
                }
            }
        }
    }
});

});
</script>


{{-- calender js--}}

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('holidaycalendar');
    if (!calendarEl) return;

    const holidays = @json($calendarHolidays);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 550,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        eventDisplay: 'block',

        events: holidays, // ✅ holidays from DB

        eventDidMount: function(info) {
            // Optional tooltip
            info.el.setAttribute('title', info.event.title);
        }
    });

    calendar.render();
});
</script>

@endsection
