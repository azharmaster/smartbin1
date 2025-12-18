@extends('layouts.staffapp')
@section('content_title', 'Staff Dashboard')

@section('content')

<style>
/* ===================== STATUS CARDS ===================== */
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

.card-total { background-color: #8c9195ff; }
.card-full { background-color: #e74c3c; }
.card-half { background-color: #f39c12; }
.card-empty { background-color: #7ccc63; }
.card-undetected { background-color: #2c3e50; }

/* ===================== CALENDAR STYLE ===================== */

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

<!-- ===================== STATUS SUMMARY ===================== -->
<div class="d-flex flex-wrap mb-4">
    <div class="status-card card-total">
        <div class="status-title">Total Devices</div>
        <div class="status-content">
            <i class="fas fa-satellite-dish status-icon"></i>
            <span class="status-number">{{ $totalDevices }}</span>
        </div>
    </div>
    <div class="status-card card-full">
        <div class="status-title">Full Devices</div>
        <div class="status-content">
            <i class="fas fa-trash status-icon"></i>
            <span class="status-number">{{ $fullDevices }}</span>
        </div>
    </div>
    <div class="status-card card-half">
        <div class="status-title">Half Full</div>
        <div class="status-content">
            <i class="fas fa-exclamation-triangle status-icon"></i>
            <span class="status-number">{{ $halfDevices }}</span>
        </div>
    </div>
    <div class="status-card card-empty">
        <div class="status-title">Empty Devices</div>
        <div class="status-content">
            <i class="fas fa-recycle status-icon"></i>
            <span class="status-number">{{ $emptyDevices }}</span>
        </div>
    </div>
    <div class="status-card card-undetected">
        <div class="status-title">Undetected</div>
        <div class="status-content">
            <i class="fas fa-minus-circle status-icon"></i>
            <span class="status-number">{{ $undetectedDevices }}</span>
        </div>
    </div>
</div>

<!-- ===================== 2-COLUMN LAYOUT ===================== -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- LEFT COLUMN: Calendar -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> My Task Calendar</h5>
                </div>
                <div class="card-body">
                    <div id="staffCalendar"></div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Chart + To-Do -->
        <div class="col-lg-6 mb-4">
            <!-- BAR CHART -->
            <div class="card card-success mb-4">
                <div class="card-header">
                    <h3 class="card-title">Monthly Task Status</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart" style="height:180px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- TO DO LIST -->
            <div class="card p-3">
                <h5 class="mb-3">
                    <a href="{{ route('todos.staffindex') }}" class="text-decoration-none text-dark">
                        To Do List
                    </a>
                </h5>
                <ul class="list-group list-group-flush">
                    @foreach($todos as $todo)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $todo->todo }}
                            <form method="POST" action="{{ route('todos.complete', $todo->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Done</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ===================== CHART.JS ===================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ===================== BAR CHART ===================== */
    const months        = @json($months);
    const pendingData   = @json($pendingPerMonth);
    const completedData = @json($completedPerMonth);
    const rejectedData  = @json($rejectedPerMonth);

    const ctx = document.getElementById('barChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: 'Pending', data: pendingData, backgroundColor: 'rgba(255, 206, 86, 0.9)' },
                { label: 'Completed', data: completedData, backgroundColor: 'rgba(75, 192, 192, 0.9)' },
                { label: 'Rejected', data: rejectedData, backgroundColor: 'rgba(255, 99, 132, 0.9)' }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });

    /* ===================== STAFF CALENDAR ===================== */
    const calendarEl = document.getElementById('staffCalendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 420,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
        events: [
            @foreach($assignedTasks->where('user_id', Auth::id()) as $task)
            {
                title: "{{ $task->title ?? 'Task' }}",
                start: "{{ \Carbon\Carbon::parse($task->created_at)->toDateString() }}",
                color: "{{ $task->status === 'completed' ? '#28a745' : '#f39c12' }}"
            },
            @endforeach
        ]
    });

    calendar.render();
});
</script>

@endsection
