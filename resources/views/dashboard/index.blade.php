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
    background-color: #6f0606b8;
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
    background-color: #8f6f05b7;
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

/* HALF DEVICE CARD (similar style but yellow, no pulse) */
.empty-device-card {
    background-color: #14431bc7;
    border: 2px solid rgb(40, 255, 80);
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.empty-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 14px rgba(41, 56, 43, 0.2);
}

/* EMPTY STATUS – No pulse */
.empty-status {
    background-color: #4cd964;
    padding: 0.4em 0.9em;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 0 8px #4cd96381;
}

/* Title size */
.full-device-card .fw-bold.fs-4,
.half-device-card .fw-bold.fs-4, 
.empty-device-card .fw-bold.fs-4, {
    font-size: 1.3rem;
    font-weight: bold;
}

.sensor-row {
    padding: 6px 0;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.sensor-row:last-child {
    border-bottom: none;
}

.asset-device-card {
    min-height: unset; /* let it grow naturally */
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

.devices-grid {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start; /* prevents stretching to tallest card */
    gap: 1rem;
}

/* shared card sizing */
.device-card {
    padding: 16px;
    border-radius: 12px;
    min-height: 130px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* hover effect */
.device-link:hover .device-card {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
}

@media (max-width: 1200px) {
    .devices-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .devices-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .devices-grid {
        grid-template-columns: 1fr;
    }
}

#deviceFilter {
    border-radius: 10px;
    min-width: 100px;
    min-height: 10px;
}

#deviceFilter:focus {
    box-shadow: 0 0 0 0.15rem rgba(108, 117, 125, 0.25); /* subtle */
}

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

.bar-empty {
    background-color: #2ecc71; /* green */
}

.asset-card {
    background-color: #a4a4a454; /* Dark card background */
    border-radius: 12px;
    display: block;
    flex: 0 0 calc(50% - 0.5rem); /* default: two per row */
    box-sizing: border-box;
}

/* Responsive adjustments */
@media (max-width: 992px) {  /* Medium devices and below */
    .asset-card {
        flex: 0 0 calc(50% - 0.5rem); /* still two per row */
    }
}

@media (max-width: 768px) { /* Small devices (tablets) */
    .asset-card {
        flex: 0 0 100%; /* one per row */
    }
}

@media (max-width: 576px) { /* Extra small devices (phones) */
    .asset-card {
        flex: 0 0 100%; /* one per row */
    }
}
/* Card body */
.map-card-body {
    position: relative;
    padding: 15px;    
    display: flex;
    flex-direction: column;
    gap: 10px; 
    height: 100%;
}

/* Map container */
#dashboardMap {
    width: 100%;
    flex-grow: 1;  
    border-radius: 10px;
    overflow: hidden;
    min-height: 200px;         
}

/* Responsive adjustments */
@media (max-width: 992px) { 
    #dashboardMap {
        height: 450px;
    }
}

@media (max-width: 576px) { 
    #dashboardMap {
        height: 300px;
    }
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


<!-- Floating Help Button -->
        <button type="button" onclick="openHelp()" style="
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
            title="Help / User Guide"
        >
            ?
        </button>

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
    <div class="row mb-4">
        <!-- LEFT COLUMN: MAP -->
        <div class="col-lg-9">
            <div class="card card-success map-card mb-4">
                <!-- Header -->
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6"><i class="fas fa-map-marked-alt"></i> Assets Map</h5>
                </div>

                <!-- Card Body -->
                <div class="card-body map-card-body" style="height: 650px; position: relative;">
                    <!-- Controls -->
                    <div class="map-controls mb-3 d-flex gap-2 align-items-center flex-wrap">
                        <button id="zoomIn" class="btn btn-secondary btn-sm"><i class="fas fa-search-plus"></i></button>
                        <button id="zoomOut" class="btn btn-secondary btn-sm"><i class="fas fa-search-minus"></i></button>
                        <button id="resetView" class="btn btn-secondary btn-sm"><i class="fas fa-crosshairs"></i> Reset</button>
                    </div>

                    <!-- Leaflet Map -->
                    <div id="dashboardMap"></div>
                </div>
            </div>
        </div>
        {{-- right column --}}
        <div class="col-lg-3">
            {{-- DEVICES GRID --}}
        {{-- -<div class="card mb-4">
            <div class="card-header smartbin-gradient">
                <h5 class="mb-0 text-white fs-6">
                    <i class="fas fa-trash-alt me-2"></i> Sensor Lists
                </h5>
            </div>

            <div id="static-device-grid" class="p-2 scroll-body" style="overflow-y: auto; max-height: 650px;">
                
                <div class="d-flex justify-content-end mb-2">
                    <select id="deviceFilter" class="form-select form-select-sm w-auto px-1">
                        <option value="critical">Default</option>
                        <option value="full">Full</option>
                        <option value="half">Half</option>
                        <option value="empty">Empty</option>
                    </select>
                </div>

                <div class="devices-grid">
                    @foreach($assetsWithDevices as $asset)
                        <div class="asset-card card mb-2 p-2">
                            <div class="fw-bold fs-6 mb-1 text-dark d-flex justify-content-between align-items-center asset-toggle"
                                role="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#assetDevices{{ $asset->id }}">
                                {{ $asset->asset_name }}
                                <i class="fas fa-chevron-down small"></i>
                            </div>

                            <div class="collapse asset-collapse" id="assetDevices{{ $asset->id }}">
                                @foreach($asset->devices as $device)
                                    @php
                                        $sensor = $device->latestSensor;
                                        $setting = $asset->capacitySetting;
                                        $capacity = $sensor->capacity ?? 0;
                                        $emptyTo = $setting->empty_to ?? 39;
                                        $halfTo  = $setting->half_to ?? 79;

                                        if ($capacity > $halfTo) {
                                            $status = 'full';
                                            $badge  = 'FULL';
                                            $badgeClass = 'full-status text-white';
                                            $cardClass  = 'full-device-card';
                                            $barClass   = 'bg-danger';
                                        } elseif ($capacity > $emptyTo) {
                                            $status = 'half';
                                            $badge  = 'HALF';
                                            $badgeClass = 'half-status text-white';
                                            $cardClass  = 'half-device-card';
                                            $barClass   = 'bg-warning';
                                        } else {
                                            $status = 'empty';
                                            $badge  = 'EMPTY';
                                            $badgeClass = 'empty-status text-white';
                                            $cardClass  = 'empty-device-card';
                                            $barClass   = 'bg-success';
                                        }
                                    @endphp

                                    <a href="javascript:void(0)" class="text-decoration-none device-link mb-1 d-block"
                                    onclick="openAssetDetails('{{ route('master-data.assets.details', ['asset' => $device->asset->id]) }}')">
                                        <div class="device-card {{ $cardClass }}" data-status="{{ $status }}">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="fw-bold fs-6 text-white">{{ $device->device_name }}</div>
                                                <div class="badge {{ $badgeClass }}">{{ $badge }}</div>
                                            </div>

                                            <div class="text-white small mb-1">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $device->asset->floor->floor_name ?? 'Unknown' }}
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px; border-radius: 999px;">
                                                    <div class="progress-bar {{ $barClass }}" style="width: {{ $capacity }}%;"></div>
                                                </div>
                                                <div class="text-white small fw-bold ms-1">{{ $capacity }}%</div>
                                            </div>

                                            @if($sensor && $sensor->battery)
                                                <div class="text-white smaller d-flex align-items-center mt-1">
                                                    <i class="fas fa-battery-three-quarters me-1"></i>
                                                    {{ $sensor->battery_percentage }}%
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>--}}
<div class="card mb-3" style="font-size: 1.0rem;">
    <div class="card-header smartbin-gradient py-2">
        <h6 class="mb-0 text-white">
            <i class="fas fa-trash-alt me-1"></i> Sensor List
        </h6>
    </div>

    <div class="card-body p-2">
        @forelse($assetsWithDevices as $asset)
            <div class="border rounded p-2 mb-2">

                <!-- ASSET HEADER -->
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="lh-sm">
                        <div class="fw-semibold">
                            {{ $asset->name }} {{ $asset->asset_name }}
                        </div>

                        <!-- LOCATION (ONCE PER ASSET) -->
                        <div class="text-muted" style="font-size: 0.65rem;">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $asset->location ?? 'Unknown location' }}
                        </div>
                    </div>

                    <a href="{{ route('master-data.assets.details', $asset->id) }}"
                       class="btn btn-success btn-xs px-2 py-1"
                       title="View Asset">
                        <i class="far fa-eye"></i>
                    </a>
                </div>

                <!-- DEVICES -->
                <div class="d-flex flex-column gap-1 mt-1">
                    @foreach($asset->devices as $device)
                        @php
                            $latest = $device->latestSensor;
                            $level  = $latest?->capacity;

                            $setting = $asset->capacitySetting;
                            $emptyTo = $setting->empty_to ?? 39;
                            $halfTo  = $setting->half_to ?? 79;

                            $badge = match (true) {
                                $level === null     => 'secondary',
                                $level > $halfTo   => 'danger',
                                $level > $emptyTo  => 'warning',
                                default            => 'success',
                            };
                            $timestamp = $latest?->created_at
                                ? $latest->created_at->format('Y-m-d H:i')
                                : '—';
                        @endphp

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ $badge }} px-2 py-1">
                                {{ $device->device_name }}
                                {{ $level !== null ? '· '.$level.'%' : '· Undetected' }}
                            </span>

                            <small class="text-muted" style="font-size: 0.65rem;">
                                {{ $timestamp }}
                            </small>
                        </div>
                    @endforeach
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
</div>

{{-- JS: ACCORDION + FILTER --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Only one asset dropdown open
    document.querySelectorAll('.asset-toggle').forEach(toggle => {
        toggle.addEventListener('click', function () {
            const target = this.getAttribute('data-bs-target');

            document.querySelectorAll('.asset-collapse').forEach(collapse => {
                if ('#' + collapse.id !== target) {
                    bootstrap.Collapse.getOrCreateInstance(collapse).hide();
                }
            });
        });
    });

    // Filter devices
    document.getElementById('deviceFilter').addEventListener('change', function () {
        const filter = this.value;

        document.querySelectorAll('.asset-card').forEach(asset => {
            let visible = 0;

            asset.querySelectorAll('.device-card').forEach(device => {
                if (filter === 'critical' || device.dataset.status === filter) {
                    device.closest('.device-link').style.display = 'block';
                    visible++;
                } else {
                    device.closest('.device-link').style.display = 'none';
                }
            });

            asset.style.display = visible > 0 ? 'block' : 'none';
        });
    });

});
</script>

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
                    <h5 class="mb-0 text-white fs-6 d-flex align-items-center">
                        <span>
                            <i class="fas fa-inbox"></i> Notification Sent
                            <span class="badge badge-info">{{ $todayNotifications->count() }}</span>
                        </span>

                        <a href="{{ route('notifications.index') }}"
                        class="ms-auto btn btn-sm btn-light d-flex align-items-center gap-1">
                            <i class="fas fa-eye"></i>
                        </a>
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

                                        <i class="fas fa-history"></i> {{ $log->sent_at->format('H:i:s') }}
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
document.addEventListener('DOMContentLoaded', function () {

    const trendData = @json($abnormalBinsTrend);

    const labels = trendData.map(d => d.date);
    const abnormalData = trendData.map(d => d.abnormal);
    const undetectedData = trendData.map(d => d.undetected);

    const ctx = document.getElementById('abnormalBinsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Abnormal Bins',
                    data: abnormalData,
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 4
                },
                {
                    label: 'Undetected Bins',
                    data: undetectedData,
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Bins'
                    },
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>
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
});
</script>

<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('dashboardMap').setView([3.1427, 101.7181], 17.5);//default trx

    // Add OpenStreetMap tiles
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(map);

    document.getElementById('zoomIn').addEventListener('click', () => map.zoomIn());
    document.getElementById('zoomOut').addEventListener('click', () => map.zoomOut());
    document.getElementById('resetView').addEventListener('click', () => map.setView([3.1427, 101.7181], 17.5));

    @foreach($assetsWithCoords as $asset)
        @php
            $devices = $asset->devices;

            $fullCount = $devices->filter(fn($device) => 
                $device->latestSensor && $device->asset->capacitySetting && 
                $device->latestSensor->capacity > $device->asset->capacitySetting->half_to
            )->count();

            $halfCount = $devices->filter(fn($device) => 
                $device->latestSensor && $device->asset->capacitySetting && 
                $device->latestSensor->capacity > $device->asset->capacitySetting->empty_to &&
                $device->latestSensor->capacity <= $device->asset->capacitySetting->half_to
            )->count();

            if($fullCount > 0) {
                $color = 'red';
            } elseif($halfCount > 0) {
                $color = 'orange';
            } else {
                $color = 'green';
            }
        @endphp

        @if($asset->latitude && $asset->longitude)
            L.marker([{{ $asset->latitude }}, {{ $asset->longitude }}], {
                icon: L.divIcon({
                    html: `
                    <i class="fas fa-trash
                        {{ $color === 'red' ? 'radar-icon' : '' }}"
                        style="
                            font-size:22px; 
                            color: {{ $color }};
                            -webkit-text-stroke: 0.3px white; /* thinner outline */
                            text-stroke: 0.3px white;        /* fallback */
                            text-shadow: 0 0 1px white;      /* subtle glow */
                        ">
                    </i>
                    `,
                    className: '',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                }),
                title: "{{ $asset->asset_name ?? 'Asset' }}"
            }).addTo(map).bindPopup(`<b>{{ $asset->asset_name ?? 'Asset' }}</b>`);
        @endif
    @endforeach
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

<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-bell"></i> Notifications for the Day</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="notificationListContainer"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Dashboard Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">SMARTBIN Dashboard – User Guide</h5>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-tachometer-alt"></i> 1. Dashboard Overview</h6>
        <p>
          The SMARTBIN Dashboard provides a real-time overview of smart bin sensors, system status, notifications, 
          and abnormal conditions. It allows administrators to monitor bin conditions efficiently 
          and take timely actions.
        </p>

        <hr>

        <h6><i class="fas fa-th-large"></i> 2. Top Summary Cards</h6>
        <p>Located at the top of the dashboard.</p>
        <ul>
          <li><strong>🔢 Total Sensors</strong>: Displays the total number of sensors registered in the system.</li>
          <li><strong>🟥 Full Sensors</strong>: Shows bins that have reached full capacity. Immediate action may be required.</li>
          <li><strong>🟨 Half-Full Sensors</strong>: Indicates bins that are partially filled. Used for monitoring upcoming collection needs.</li>
          <li><strong>🟩 Empty Sensors</strong>: Shows bins that are currently empty.</li>
          <li><strong>⚪ Undetected Sensors</strong>: Sensors that are offline, disconnected, or not reporting data.</li>
        </ul>
        <p>👉 Click “More info” on any card to view the live data.</p>

        <hr>

        <h6><i class="fas fa-chart-line"></i> 3. SmartBin Clear Time Chart</h6>
        <p>Displays sensor clearing history.</p>
        <ul>
          <li>Line chart showing hours taken to clear bins.</li>
          <li>Each line represents a sensor (TRX1-1, TRX1-2, TRX1-3).</li>
          <li>X-axis: Date in the week</li>
          <li>Y-axis: Time (hours)</li>
        </ul>
        <p><strong>Dropdown Filter:</strong> Select a specific SmartBin unit (e.g., TRX SmartBin 01) to view its data.</p>
        <p><strong>Purpose:</strong> Track cleaning efficiency, identify delays, and support operational decisions.</p>

        <hr>

        <h6><i class="fas fa-exclamation-triangle"></i> 4. Abnormal / Undetected Sensors Panel</h6>
        <p>Located on the right side of the dashboard. Displays sensors that are:</p>
        <ul>
          <li>Offline</li>
          <li>Undetected</li>
          <li>Sending abnormal readings</li>
        </ul>
        <p><strong>Information Shown:</strong> SmartBin name, Sensor ID (e.g., TRX1-1)</p>
        <p><strong>Indicator:</strong> Red dot indicates critical attention required</p>
        <p>👉 Action should be taken immediately to inspect the sensor or bin.</p>

        <hr>

        <h6><i class="fas fa-envelope"></i> 5. Notification Sent Panel</h6>
        <p>Shows the notification status for the current day.</p>
        <ul>
          <li>Number of alerts sent</li>
          <li>Message status</li>
          <li>If no notifications: “No notifications sent today”</li>
        </ul>

        <hr>

        <h6><i class="fas fa-mobile-alt"></i> 6. WhatsApp Notification Control</h6>
        <p>Allows administrators to control WhatsApp alerts.</p>
        <ul>
          <li><strong>ON</strong> – System sends WhatsApp alerts automatically</li>
          <li><strong>OFF</strong> – Notifications are disabled</li>
        </ul>
        <p>Use Case: Enable alerts during operational hours, disable during maintenance or testing.</p>

        <hr>

        <h6><i class="fas fa-broadcast-tower"></i> 7. Sensor Lists</h6>
        <p>Displays sensors under each SmartBin unit.</p>
        <ul>
          <li>SmartBin name</li>
          <li>Sensor ID</li>
          <li>Current fill percentage</li>
        </ul>
        <p>Example:</p>
        <pre>
            TRX SmartBin 01
            Sensor 1 – 17%
            Sensor 2 – 5%
            Sensor 3 – 12%
        </pre>

        <hr>

        <h6><i class="fas fa-users"></i> 8. Users Panel</h6>
        <p>Shows system users and their roles.</p>
        <ul>
          <li>Columns: Name, Role (Admin / Supervisor), Phone Number, Actions</li>
          <li>Action Buttons: 📞 Call user, 💬 WhatsApp user (quick communication during alerts)</li>
        </ul>

        <hr>

        <h6><i class="fas fa-calendar-alt"></i> 9. Calendar (Holiday & Event)</h6>
        <p>Displays public holidays and scheduled events.</p>
        <ul>
          <li>Monthly & weekly view</li>
          <li>Event labels (e.g., Concert)</li>
          <li>Used for planning collection schedules</li>
        </ul>

        <hr>

        <h6><i class="fas fa-play-circle"></i> 10. Live Dashboard Button</h6>
        <p>🟢 Live Dashboard opens real-time monitoring view and displays live sensor updates.</p>

        <hr>

        <h6><i class="fas fa-check-circle"></i> 11. Best Practices</h6>
        <ul>
          <li>✔ Check dashboard daily</li>
          <li>✔ Monitor abnormal sensors immediately</li>
          <li>✔ Keep notifications enabled</li>
          <li>✔ Review clear time trends weekly</li>
        </ul>

      </div>

    </div>
  </div>
</div>
<!--OPEN HELP MODAL -->
<script>
function openHelp() {
    $('#helpModal').modal('show');
}
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('holidaycalendar');
    if (!calendarEl) return;

    const calendarEvents = @json($calendarCombined);
    const isMobile = window.innerWidth < 768;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: isMobile ? 'dayGridWeek' : 'dayGridMonth',
        height: 'auto',
        contentHeight: 'auto',

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
    if (info.event.extendedProps.type === 'notification_group') {

        let html = '<ul class="list-group list-group-flush">';
        info.event.extendedProps.notifications.forEach(n => {
            html += `<li class="list-group-item">
                        ${n.message_preview ?? 'No message available'}
                     </li>`;
        });
        html += '</ul>';

        $('#notificationListContainer').html(html);
        $('#notificationModal').modal('show');
        info.jsEvent.preventDefault();
        return;
    }

            $('#eventLocation').parent().hide();
            $('#eventPic').parent().hide();

            // Modal title and content
            if (info.event.extendedProps.type === 'holiday') {
                $('#eventDetailsModalLabel').html('<i class="fas fa-umbrella-beach"></i> Holiday Details');
            } else if (info.event.extendedProps.type === 'event') {
                $('#eventDetailsModalLabel').html('<i class="fas fa-calendar-alt"></i> Event Details');
            } else if (info.event.extendedProps.type === 'notification') {
                $('#eventDetailsModalLabel').html('<i class="fas fa-bell"></i> Notification Details');
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

            // Show additional fields for events
            if (info.event.extendedProps.type === 'event') {
                $('#eventLocation').parent().show();
                $('#eventPic').parent().show();

                $('#eventLocation').text(info.event.extendedProps.location);
                $('#eventPic').text(info.event.extendedProps.pic_phone);
            }

            // Show notification details in modal (optional extra info)
            if (info.event.extendedProps.type === 'notification') {
                // Example: show sent_at time if exists
                $('#eventLocation').parent().show();
                $('#eventLocation').text('Sent At: ' + (info.event.extendedProps.sent_at ?? 'N/A'));
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

<script>
function openAssetDetails(url) {
    window.open(url, '_blank');
}

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector('#static-device-grid .devices-grid');
    const filterSelect = document.getElementById('deviceFilter');
    let currentFilter = 'critical';

    function applyFilter(filter = 'critical') {
        currentFilter = filter;

        // Loop over each asset card
        grid.querySelectorAll('.asset-card').forEach(assetCard => {
            let anyVisible = false;

            // Loop over device cards inside this asset
            assetCard.querySelectorAll('.device-card').forEach(deviceCard => {
                const status = deviceCard.dataset.status;
                const show =
                    filter === 'all' ||
                    (filter === 'critical' && ['full','half','empty'].includes(status)) ||
                    status === filter;

                deviceCard.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });

            // Show/hide the asset card depending on whether any devices are visible
            assetCard.style.display = anyVisible ? '' : 'none';
        });
    }

    // INITIAL FILTER
    applyFilter(currentFilter);

    filterSelect?.addEventListener('change', () => applyFilter(filterSelect.value));
});
</script>

<style>
/* Radar pulse - crisp thick expanding circle */
.radar-icon {
    position: relative;
    font-size: 22px;
    color: red; /* icon color */
    display: inline-block;
}

/* Expanding thick ring */
.radar-icon::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;           /* really small start */
    height: 5px;          /* make it equal to width for a circle */
    border: 6px solid currentColor; /* thick ring */
    border-radius: 50%;    /* keeps it circular */
    transform: translate(-50%, -50%) scale(1);
    opacity: 0.8;
    pointer-events: none;
    animation: radarPing 1.5s infinite;
}
@keyframes radarPing {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.8;
    }
    100% {
        transform: translate(-50%, -50%) scale(6); /* expand far */
        opacity: 0; /* fade out */
    }
}
</style>

@endsection
