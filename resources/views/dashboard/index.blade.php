@extends('layouts.app')
@section('content_title', 'Dashboard')

@section('content')

<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 20px;
}

.status-card {
    width: 19%;
    border: 2px solid #ddd;
    border-radius: 12px;
    padding: 18px;
    background: #fff;
    color: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    margin-left: 10px;
    transition: 0.2s ease-in-out;
}

.status-card:hover {
    box-shadow: 0 5px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.status-title {
    font-size: 20px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 15px;
}

.status-content {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.status-icon {
    font-size: 32px;
}

.status-number {
    font-size: 32px;
    font-weight: bold;
}

.card-total { background-color: #808080; }
.card-full { background-color: #FF0000; }
.card-half { background-color: #FFFF00; }
.card-empty { background-color: #00FF00; }
.card-undetected { background-color: #000080; }
.card-primary { background-color: #3f44b5ff;}

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


.floor-frame {
    margin-top: 40px;
    width: 600px;
    max-width: 100%;
    padding: 20px;
    border-radius: 12px;
    border: 2px solid #ddd;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    background-color: #fff;
}

.floor-frame select {
    width: 100%;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}

.floor-frame img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #aaa;
    transition: transform 0.2s, box-shadow 0.2s;
}

.floor-frame img:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.bins-container {
    margin-top: 40px;
    width: 100%;
    max-width: 100%;
    border: 2px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    background-color: #fff;
    padding: 20px;
}

.bins-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.bins-header h2 {
    font-size: 1.4rem;
}

.bin-count {
    font-weight: bold;
}
/*
.collapse-btn {
    border: none;
    background: transparent;
    cursor: pointer;
}
*/
.bins-list {
    max-height: 600px;
    overflow-y: auto;
}

.bin-card {
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #f5f5f5;
}

.bin-card.warning { background-color: #f8d7da; }
.bin-card.normal { background-color: #d4edda; }

.bin-progress {
    width: 100%;
    background-color: #e9ecef;
    height: 10px;
    border-radius: 6px;
    margin-top: 5px;
}

.bin-progress-bar {
    height: 10px;
    border-radius: 6px;
}

.progress-warning { background-color: #e74c3c; }
.progress-normal { background-color: #7ccc63; }

.bin-details {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
}

#mapAndList {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 25px;
    margin-top: 40px;
}

.floor-frame {
    width: 45%;
}

.bins-container {
    width: 55%;
}

.map-card-body {
    overflow: hidden;
    height: 650px; /* initial height */
    transition: height 0.3s ease; /* smooth animation */
}

</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

@php
function trend($current, $previous) {
    if ($current > $previous) return ['▲', 'text-success'];
    if ($current < $previous) return ['▼', 'text-danger'];
    return ['—', 'text-muted'];
}
@endphp

<div class="d-flex flex-wrap">
    <div class="status-card card-total">
        <div class="status-title">Total Devices</div>
        <div class="status-content">
            <i class="fas fa-satellite-dish status-icon"></i>
            <span class="status-number">{{ $totalDevices }}</span>
        </div>
        <div class="status-trend {{ $totalTrend['class'] }}">
            {{ $totalTrend['icon'] }} {{ $totalTrend['value'] }} <small>vs last month</small>
        </div>
    </div>

    <div class="status-card card-full">
        <div class="status-title">Full Devices</div>
        <div class="status-content">
            <i class="fas fa-trash status-icon"></i>
            <span class="status-number">{{ $fullDevices }}</span>
        </div>
        <div class="status-trend {{ $fullTrend['class'] }}">
            {{ $fullTrend['icon'] }} {{ $fullTrend['value'] }} <small>vs last month</small>
        </div>
    </div>

    <div class="status-card card-half">
        <div class="status-title ">Half Full</div>
        <div class="status-content">
            <i class="fas fa-exclamation-triangle status-icon"></i>
            <span class="status-number">{{ $halfDevices }}</span>
        </div>
        <div class="status-trend {{ $halfTrend['class'] }}">
            {{ $halfTrend['icon'] }} {{ $halfTrend['value'] }} <small>vs last month</small>
        </div>
    </div>

    <div class="status-card card-empty">
        <div class="status-title">Empty Devices</div>
        <div class="status-content">
            <i class="fas fa-recycle status-icon"></i>
            <span class="status-number">{{ $emptyDevices }}</span>
        </div>
        <div class="status-trend {{ $emptyTrend['class'] }}">
            {{ $emptyTrend['icon'] }} {{ $emptyTrend['value'] }} <small>vs last month</small>
        </div>
    </div>

    <div class="status-card card-undetected">
        <div class="status-title">Undetected</div>
        <div class="status-content">
            <i class="fas fa-minus-circle status-icon"></i>
            <span class="status-number">{{ $undetectedDevices }}</span>
        </div>
        <div class="status-trend {{ $undetectedTrend['class'] }}">
            {{ $undetectedTrend['icon'] }} {{ $undetectedTrend['value'] }} <small>vs last month</small>
        </div>
    </div>
</div>

<div class="container-fluid mt-4">

    <div class="row">

        <!-- LEFT COLUMN: MAP -->
        <div class="col-lg-6">

            <!--SMARTBIN TRACK-->
<div class="card mb-4">
    <div class="card-header smartbin-gradient">
        <h5 class="mb-0 text-white">
            <i class="fas fa-trash"></i> SmartBin Clear Time
        </h5>
    </div>
    <div class="card-body">
        <canvas id="smartBinClearChart" height="120"></canvas>
    </div>
</div>

<!-- SmartBin Gradient Style -->
<style>
.smartbin-gradient {
    background: linear-gradient(135deg, #0a1f44, #1e90ff);
}
</style>

            <!-- SIMPLE USER LIST BELOW TODO LIST -->
            <div class="card p-3 mt-4">
                <h5 class="mb-3"><i class="fas fa-users"></i> Users</h5>
                <ul class="list-group">
                    @foreach($users as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $user->name }}
                            <span class="badge bg-primary">
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
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Activity Calendar
                    </h5>
                </div>

                <div class="card-body p-2">
                    <div id="supervisorCalendar"></div>
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

/* ============================= */
/* Gradient Header (Added Only)  */
/* ============================= */
.card-header.bg-primary {
    background: linear-gradient(135deg, #0a1f44, #1e90ff) !important;
}
</style>

            <!-- TODO LIST -->
            <div class="card p-3">
                <h5 class="mb-3">
                    <a href="{{ route('todos.index') }}" class="text-decoration-none text-dark">
                        <i class="fas fa-list-check"></i> To Do List
                    </a>
                </h5>

                <ul class="list-group list-group-flush">
                    @foreach($todos as $todo)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $todo->todo }}
                            <form method="POST" action="{{ route('todos.complete', $todo->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-success">Done</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>

</div>

{{-- View Task Modal --}}
<div class="modal fade" id="viewTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tasks mr-2"></i> Task Details
                </h5>
                <button type="button" onclick="$('#viewTaskModal').modal('hide')"
                        class="btn p-0 text-white" style="font-size: 1.5rem;">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="taskId"></span></p>
                <p><strong>User:</strong> <span id="taskUser"></span></p>
                <p><strong>Asset:</strong> <span id="taskAsset"></span></p>
                <p><strong>Floor:</strong> <span id="taskFloor"></span></p>
                <hr>
                <p><strong>Description:</strong></p>
                <p id="taskDescription"></p>
                <p>
                    <strong>Status:</strong>
                    <span id="taskStatus" class="badge"></span>
                </p>
                <p><strong>Notes:</strong></p>
                <p id="taskNotes" class="text-muted"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger" onclick="$('#viewTaskModal').modal('hide')">
                    Close
                </button>
            </div>
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
binGradient.addColorStop(0, 'rgba(13, 110, 253, 0.4)');
binGradient.addColorStop(1, 'rgba(13, 110, 253, 0.05)');

new Chart(binCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($smartBinClearTimes->pluck('device_name')) !!},
        datasets: [{
            label: 'Hours to Clear',
            data: {!! json_encode($smartBinClearTimes->pluck('hours')) !!},
            borderColor: '#0d6efd',
            backgroundColor: binGradient,
            fill: true,
            tension: 0.45,
            borderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 9,
            pointBackgroundColor: '#0d6efd'
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

    const calendarEl = document.getElementById('supervisorCalendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 550,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: {!! json_encode($calendarEvents) !!},
        eventDisplay: 'block',

eventClick: function(info) {
    const event = info.event;
    const props = event.extendedProps;

    $('#taskId').text(event.id);
    $('#taskUser').text(props.user);
    $('#taskAsset').text(props.asset);
    $('#taskFloor').text(props.floor);
    $('#taskDescription').text(event.title);
    $('#taskNotes').text(props.notes);

    $('#taskStatus')
        .text(props.status.replace('_', ' '))
        .removeClass()
        .addClass('badge ' + (
            props.status === 'completed' ? 'badge-success' :
            props.status === 'in_progress' ? 'badge-info' :
            props.status === 'pending' ? 'badge-warning' :
            'badge-danger'
        ));

    $('#viewTaskModal').modal('show');
}
    });

    calendar.render();

});
</script>



@endsection
