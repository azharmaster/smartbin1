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
    background: #319237ff; /* DARK GREEN */
    color: #ffffff;

    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.35);
}
.status-card:hover::before {
    opacity: 0;            /* disappear on hover */
    transform: translateY(0); /* optional slight movement */
    box-shadow: none;       /* remove glow if any */
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
    opacity: 0.40;
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
.card-total:hover .status-icon {
color: rgb(0, 209, 251);
    transform: scale(1.2);      /* POP size */
    filter: drop-shadow(0 0 8px rgba(255, 235, 59, 0.7));
    transition:
        color 0.25s ease,
        transform 0.25s ease,
        filter 0.25s ease;
}
.card-total .status-icon {
    color: #1e88e5;
}

.card-full:hover .status-icon {
    color: #ff0000; /* strong red */
    transform: scale(1.2);      /* POP size */
    filter: drop-shadow(0 0 8px rgba(255, 235, 59, 0.7));
    transition:
        color 0.25s ease,
        transform 0.25s ease,
        filter 0.25s ease;
 
}

.card-full .status-icon {
    color: #e23532; /* strong red */
}

.card-half:hover .status-icon {
    color: #fb8c00;
    transform: scale(1.2);      /* POP size */
    filter: drop-shadow(0 0 8px rgba(255, 235, 59, 0.7));
    transition:
        color 0.25s ease,
        transform 0.25s ease,
        filter 0.25s ease;
 
}
.status-icon.half {
    background: linear-gradient(to top, #fc9c00 50%, #c26b00 50%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.card-half .status-icon {
    color: #fb8c00;
}

.card-empty:hover .status-icon{
    color: #2bff00; 
    transform: scale(1.2);      /* POP size */
    filter: drop-shadow(0 0 8px rgba(255, 235, 59, 0.7));
    transition:
        color 0.25s ease,
        transform 0.25s ease,
        filter 0.25s ease;
 
}

.status-icon.empty {
    color: #43a047;
    background: linear-gradient(to top, #43a047 10%, #007506 10%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.card-empty .status-icon {
    color: #43a047;
}

.card-undetected:hover .status-icon{
color: rgb(255, 255, 255);
    transform: scale(1.2);      /* POP size */
    filter: drop-shadow(0 0 8px rgba(255, 235, 59, 0.7));
    transition:
        color 0.25s ease,
        transform 0.25s ease,
        filter 0.25s ease;
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

    background: rgba(0,0,0,0.04);+
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

.notification-timeline {
    position: relative;
    margin-left: 20px;
}

.notification-timeline::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    padding-bottom: 20px;
}

.timeline-dot {
    width: 14px;
    height: 14px;
    background: #198754; /* success green */
    border-radius: 50%;
    margin-right: 15px;
    margin-top: 4px;
    z-index: 1;
}

.timeline-content {
    width: 100%;
}

.timeline-button {
    background: none;
    border: none;
    padding: 0;
    font-weight: 500;
    color: #212529; /* normal black text */
    cursor: pointer;
    text-align: left;
}

.timeline-button:hover {
    text-decoration: underline;
}

pre {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    white-space: pre-wrap;
    font-family: inherit;
}

/* Custom ON/OFF toggle switch */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #2ba11d; /* Blue when ON */
}

input:checked + .slider:before {
  transform: translateX(24px);
}

<style>
.save-bookmark i {
    color: #28a745; /* normal green outline */
    transition: color 0.2s ease, transform 0.2s ease;
}

.save-bookmark:hover i {
    color: #28a745; 
}

.save-bookmark:hover i:before {
    content: "\f02e"; /* Unicode for solid bookmark */
    font-family: "Font Awesome 5 Free";
    font-weight: 900; /* needed for solid icon */
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- FullCalendar (CSS + JS) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>


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
            <div class="status-title">Total Sensors</div>
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
            <div class="status-title">Full Sensors</div>
            <div class="status-content">
            <i class="fas fa-trash status-icon full"></i>
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
            <div class="status-title">Half-Full Sensors</div>
            <div class="status-content">
                <i class="fas fa-trash status-icon half"></i>
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
            <div class="status-title">Empty Sensors</div>
            <div class="status-content">
                <i class="fas fa-trash status-icon empty"></i>
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

    <!-- <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.main.dashboard') }}" class="btn btn-success">
            <i class="fas fa-chart-line"></i> Live Dashboard
        </a>
    </div> -->


    <!-- ROW 1: SMARTBIN CHART -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header smartbin-gradient d-flex align-items-center">
                    <h5 class="mb-0 text-white fs-6 d-flex align-items-center">
                        <i class="fas fa-trash me-2"></i> SmartBin Clear Time
                    </h5>

                    <select id="assetFilter" class="form-select form-select-sm w-auto ms-auto">
                        @foreach($smartBinClearTimes as $assetName => $devices)
                            <option value="{{ $assetName }}">{{ $assetName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="position-relative w-100" style="min-height:240px;">
                    <canvas id="smartBinClearChart"></canvas>
                </div>
            </div>
        </div>
    </div>

<!-- SmartBin Animated Gradient Style -->
<style>
.smartbin-gradient {
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

//whatsapp toggle responsive
@media (max-width: 576px) {
    .card-body.d-flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>


    <!-- ROW 2: USERS + TODO -->
    <div class="row">
        <!-- LEFT COLUMN: MAP -->
        <div class="col-lg-6">
            <!-- NOTIFICATION LOGS-->
            <div class="card mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6">
                        📤 Notification Sent
                        <span class="badge badge-info">{{ $todayNotifications->count() }}</span>
                    </h5>
                </div>

                <div class="card-body p-3">
                    <div class="notification-timeline">
                        @forelse($todayNotifications->take(10) as $log)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>

                                <div class="timeline-content">
                                    <button
                                        class="timeline-button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#notif{{ $log->id }}">

                                        🕒 {{ $log->sent_at->format('H:i:s') }}
                                    </button>

                                    <div id="notif{{ $log->id }}" class="collapse mt-2">
                                        <pre class="mb-0 text-sm">{{ $log->message_preview }}</pre>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">
                                No notifications sent today
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6">
                        <i class="fas fa-sms"></i> WhatsApp Notification
                    </h5>
                </div>
                <div class="card-body d-flex justify-content-between align-items-center" style="background-color: #f8f9fa; border-radius: 0 0 10px 10px;">
                    
                    <!-- Left: Label -->
                    <span class="fw-semibold">Notification Status</span>

                    <!-- Right: Toggle + ON/OFF + Save -->
                    <div class="d-flex align-items-center ml-auto">
                        <span class="text-muted small mr-1">OFF</span>

                        <label class="switch m-0 mx-2">
                            <input type="checkbox" id="whatsappNotificationSwitch" 
                                {{ $whatsappNotificationActive ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>

                        <span class="text-muted small ml-1">ON</span>

                        <!-- Save icon -->
                        <button id="saveWhatsappNotification" class="btn p-0 ml-3 save-bookmark" title="Save">
                            <i class="far fa-bookmark fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- SIMPLE USER LIST  -->
            <div class="card shadow-sm mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6">
                        <i class="fas fa-users"></i> Users
                    </h5>
                </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%;">#</th>
                                <th style="width:30%;">Name</th>
                                <th style="width:15%;">Role</th>
                                <th style="width:20%;">Phone</th>
                                <th style="width:30%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        @switch($user->role)
                                            @case(1) <span class="badge bg-danger">Admin</span> @break
                                            @case(2) <span class="badge bg-primary">Staff</span> @break
                                            @case(3) <span class="badge bg-secondary">User</span> @break
                                            @case(4) <span class="badge bg-success">Supervisor</span> @break
                                            @default <span class="badge bg-dark">Unknown</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($user->phone)
                                            @php
                                                $cleanPhone = preg_replace('/\D+/', '', $user->phone);
                                                $cleanPhone = ltrim($cleanPhone, '0');
                                                $fullPhone = '60' . $cleanPhone;
                                            @endphp
                                            <a href="tel:{{ $fullPhone }}" class="text-decoration-none me-2">
                                                {{ $user->phone }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->phone)
                                            <a href="tel:{{ $fullPhone }}" class="btn btn-sm btn-outline-primary me-1" title="Call">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                            <a href="https://wa.me/{{ $fullPhone }}" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
        </div>


       <!-- RIGHT COLUMN -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6">
                        <i class="fas fa-trash-alt me-2"></i> Sensor Lists
                    </h5>
                </div>

                <div class="card-body p-2">
                    <div class="accordion" id="assetAccordion">

                        @forelse($assetsWithDevices as $asset)
                            <div class="accordion-item mb-2">
                                <h2 class="accordion-header" id="heading{{ $asset->id }}">
                                    <button
                                        class="accordion-button collapsed fw-semibold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#asset{{ $asset->id }}">

                                        🗑️ {{ $asset->name }}
                                        {{ $asset->asset_name }}
                                    </button>
                                </h2>

                                <div
                                    id="asset{{ $asset->id }}"
                                    class="accordion-collapse collapse"
                                    data-bs-parent="#assetAccordion">

                                    <div class="accordion-body p-2">
                                        <ul class="list-group list-group-flush">

                                            @foreach($asset->devices as $device)
                                                @php
                                                    $latest = $device->sensors->last();
                                                    $level = $latest->capacity ?? null;

                                                    $badge = match (true) {
                                                        $level === null => 'secondary',
                                                        $level >= 86 => 'danger',
                                                        $level >= 41 => 'warning',
                                                        default => 'success',
                                                    };
                                                @endphp

                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $device->device_name }}</strong><br>
                                                        <small class="text-muted">
                                                        </small>
                                                    </div>

                                                    <span class="badge bg-{{ $badge }}">
                                                        {{ $level !== null ? $level.'%' : 'Undetected' }}
                                                    </span>
                                                </li>
                                            @endforeach

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">
                                No assets found
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>

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
                cursor: pointer;
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
    cursor: pointer;
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
    const smartBinData = @json($smartBinClearTimes);

    const ctx = document.getElementById('smartBinClearChart').getContext('2d');
    let chart;

    function renderChart(assetName) {
        const devices = smartBinData[assetName];

        // collect all unique dates
        const labels = Array.from(
            { length: Math.max(...Object.values(devices).map(d => d.length)) },
            (_, i) => `Clear #${i + 1}`
        );

        const datasets = Object.entries(devices).map(([deviceName, records]) => {
            const dataMap = {};
            records.forEach(r => dataMap[r.date] = r.hours);

            return {
                label: deviceName,
                data: labels.map((_, i) => records[i]?.hours ?? null),
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 5,
                spanGaps: true
            };
        });

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.raw} hours`
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Latest Bin is Cleared (10)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours to Clear'
                        }
                    }
                }
            }
        });
    }

    // initial load
    const assetSelect = document.getElementById('assetFilter');
    renderChart(assetSelect.value);

    // filter change
    assetSelect.addEventListener('change', e => {
        renderChart(e.target.value);
    });
});
</script>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" role="dialog" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded shadow-sm">

      <!-- Header -->
      <div class="modal-header border-bottom">
        <h5 class="modal-title" id="eventDetailsModalLabel">
          <i class="fas fa-calendar-alt"></i> Event Details
        </h5>
        <!-- Header Close Button -->
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <p><strong>Name:</strong> <span id="eventName"></span></p>
        <p><strong>Location:</strong> <span id="eventLocation"></span></p>
        <p><strong>PIC Phone:</strong> <span id="eventPic"></span></p>
        <p><strong>Start:</strong> <span id="eventStart"></span></p>
        <p><strong>End:</strong> <span id="eventEnd"></span></p>
      </div>

      

    </div>
  </div>
</div>




<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('holidaycalendar');
    if (!calendarEl) return;

    const calendarEvents = @json($calendarCombined);

    const isMobile = window.innerWidth < 768;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: isMobile ? 'dayGridWeek' : 'dayGridMonth',
        height: isMobile ? 'auto' : 550,

        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: isMobile ? '' : 'dayGridMonth,timeGridWeek'
        },
        eventDisplay: 'block',

        events: calendarEvents,

        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.title);
        },

        eventClick: function(info) {

            $('#eventLocation').parent().hide();
            $('#eventPic').parent().hide();

            // Modal title
            if (info.event.extendedProps.type === 'holiday') {
                $('#eventDetailsModalLabel').html('<i class="fas fa-umbrella-beach"></i> Holiday Details');
            } else {
                $('#eventDetailsModalLabel').html('<i class="fas fa-calendar-alt"></i> Event Details');
            }

            $('#eventName').text(info.event.title);

            const startDate = info.event.start;
            let endDate = info.event.end ?? info.event.start;

            // 🔑 FIX: FullCalendar end date is exclusive for all-day events
            if (info.event.extendedProps.type === 'holiday') {
                endDate = new Date(endDate);
                endDate.setDate(endDate.getDate() - 1);
            }

            $('#eventStart').text(startDate.toLocaleDateString());
            $('#eventEnd').text(endDate.toLocaleDateString());

            if (info.event.extendedProps.type === 'event') {
                $('#eventLocation').parent().show();
                $('#eventPic').parent().show();

                $('#eventLocation').text(info.event.extendedProps.location);
                $('#eventPic').text(info.event.extendedProps.pic_phone);
            }

            $('#eventDetailsModal').modal('show');
            info.jsEvent.preventDefault();
        }
    });

    calendar.render();
    console.log(@json($calendarCombined));

    $(document).on('click', '.modal .close', function() {
        $(this).closest('.modal').modal('hide');
    });

    const whatsappSwitch = document.getElementById('whatsappNotificationSwitch');
    const saveButton = document.getElementById('saveWhatsappNotification');

    saveButton.addEventListener('click', function() {
        fetch('{{ route("dashboard.toggleWhatsappNotification") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                is_active: whatsappSwitch.checked
            }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                whatsappSwitch.checked = data.is_active;
                alert('Notification status saved!');
            }
        })
        .catch(err => console.error(err));
    });
});
</script>


@endsection
