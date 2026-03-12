@extends('layouts.app')
@section('content_title', 'Dashboard')

@section('content')

@php
    $isAdmin = auth()->user()->role == 1;
@endphp

<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 20px;
}

/* === STATUS CARD CONTAINER === */
.status-card {
    flex: 1 1 200px;
    background: #ffffff;
    color: #111;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
    margin-left: 10px;
}

.status-card:hover {
    background: #1f6423;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.status-body {
    padding: 12px 16px;
}

.status-title {
    font-size: 12px;
    font-weight: 500;
    color: #666;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline;
}

.status-content {
    display: inline;
    margin-left: 8px;
}

.status-number {
    font-size: 18px;
    font-weight: 700;
    color: #111;
}

.status-card:hover .status-title {
    color: rgba(255,255,255,0.85);
}

.status-card:hover .status-number {
    color: #ffffff;
}

/* Status cards floating inside map */
.map-status-cards {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    z-index: 1000;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.map-status-cards .status-card {
    flex: 1 1 auto;
    min-width: 150px;
}

/* Sensor List - Waste Type Cards */
.sensor-list-card {
    border: none;
    border-radius: 5px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    background: #ffffff;
    overflow: hidden;
}

.sensor-list-header {
    padding: 12px 16px;
    border: none;
}

.sensor-list-header h6 {
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
}

.sensor-list-body {
    padding: 16px;
    background: #fafbfc;
}

.asset-item {
    background: #f7fafc;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 8px;
}

.asset-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e2e8f0;
}

.asset-name {
    font-size: 12px;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
}

.asset-location {
    font-size: 10px;
    color: #718096;
    margin-top: 1px;
}

.asset-location i {
    color: #e53e3e;
}

.btn-view-asset {
    background: #48bb78;
    border: none;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 10px;
    transition: all 0.2s ease;
}

.btn-view-asset:hover {
    background: #38a169;
    color: white;
}

.asset-footer {
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px dashed #e2e8f0;
}

.last-emptied {
    font-size: 9px;
    color: #718096;
}

.last-emptied i {
    color: #4299e1;
}

.waste-types-row {
    display: flex;
    gap: 6px;
    margin-bottom: 6px;
}

.waste-type-card {
    flex: 1;
    background: #ffffff;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.waste-type-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.waste-type-card.glass {
    border-color: #90cdf4;
    background: linear-gradient(135deg, #ebf8ff 0%, #ffffff 100%);
}

.waste-type-card.paper {
    border-color: #f6e05e;
    background: linear-gradient(135deg, #fffff0 0%, #ffffff 100%);
}

.waste-type-card.general {
    border-color: #cbd5e0;
    background: linear-gradient(135deg, #f7fafc 0%, #ffffff 100%);
}

.waste-icon {
    font-size: 16px;
    margin-bottom: 4px;
}

.waste-icon.glass {
    color: #2b6cb0;
}

.waste-icon.paper {
    color: #b7791f;
}

.waste-icon.general {
    color: #4a5568;
}

.waste-label {
    font-size: 9px;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
    margin-bottom: 3px;
}

.waste-capacity {
    font-size: 14px;
    font-weight: 700;
}

.waste-capacity.full {
    color: #e53e3e;
}

.waste-capacity.half {
    color: #ed8936;
}

.waste-capacity.empty {
    color: #48bb78;
}

.waste-capacity.undetected {
    color: #a0aec0;
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

/* Larger switch for WhatsApp notification */
.switch-lg {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch-lg input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch-lg .slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}

.switch-lg .slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

.switch-lg input:checked + .slider {
  background-color: #28a745;
}

.switch-lg input:checked + .slider:before {
  transform: translateX(26px);
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
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
    height: 100%;
    background: transparent;
}

/* Map container */
#dashboardMap {
    width: 100%;
    flex-grow: 1;
    border-radius: 12px;
    overflow: hidden;
    min-height: 200px;
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.08);
}

/* Map controls styling - floating inside map */
.map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    display: flex;
    gap: 4px;
}

.map-controls button {
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: all 0.2s ease;
}

.map-controls button:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}

/* Leaflet popup styling */
.leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    padding: 0;
    overflow: hidden;
}

.leaflet-popup-content {
    margin: 0;
    padding: 0;
    width: 220px !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.leaflet-popup-tip {
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

/* Custom popup header */
.popup-header {
    background: linear-gradient(135deg, #103913ff, #1f6423ff);
    color: white;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
}

.popup-body {
    padding: 12px 16px;
    background: #fff;
    color: #333;
    font-size: 13px;
}

.popup-body p {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Custom marker styles */
.custom-marker {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.custom-marker:hover {
    transform: scale(1.15);
    z-index: 1000;
}

.marker-pin {
    width: 40px;
    height: 40px;
    border-radius: 50% 50% 50% 0;
    background: #fff;
    position: absolute;
    transform: rotate(-45deg);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    border: 3px solid #fff;
}

.marker-pin::after {
    content: '';
    width: 24px;
    height: 24px;
    background: rgba(255,255,255,0.3);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(45deg);
    border-radius: 50%;
}

.marker-icon {
    position: relative;
    z-index: 2;
    font-size: 18px;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

/* Pulse animation for critical markers */
.marker-pulse {
    position: absolute;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 0, 0, 0.4);
    animation: markerPulse 1.5s infinite;
    z-index: 1;
    pointer-events: none;
}

@keyframes markerPulse {
    0% {
        transform: scale(0.5);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

/* Color variants */
.marker-red .marker-pin {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    border-color: #ff416c;
}

.marker-orange .marker-pin {
    background: linear-gradient(135deg, #f7971e, #ffd200);
    border-color: #f7971e;
}

.marker-green .marker-pin {
    background: linear-gradient(135deg, #11998e, #38ef7d);
    border-color: #11998e;
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
                <!-- <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6"><i class="fas fa-map-marked-alt"></i> Assets Map</h5>
                </div> -->

                <!-- Card Body -->
                <div class="card-body map-card-body" style="height: 650px; position: relative;">
                    <!-- Map Controls - Floating -->
                    <div class="map-controls">
                        <button id="zoomIn" class="btn btn-info btn-sm"><i class="fas fa-search-plus"></i></button>
                        <button id="zoomOut" class="btn btn-danger btn-sm"><i class="fas fa-search-minus"></i></button>
                        <button id="resetView" class="btn btn-secondary btn-sm"><i class="fas fa-crosshairs"></i> Reset</button>
                    </div>

                    <!-- Status Cards - Floating Bottom -->
                    <div class="map-status-cards">
                        <div class="status-card card-total">
                            <div class="status-body">
                                <div class="status-title">Total Bins Installed</div>
                                <div class="status-content">
                                    <span class="status-number">{{ $totalBinsInstalled }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="status-card card-half">
                            <div class="status-body">
                                <div class="status-title">Active Bins</div>
                                <div class="status-content">
                                    <span class="status-number">{{ $activeBins }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="status-card card-full">
                            <div class="status-body">
                                <div class="status-title">Full Bins</div>
                                <div class="status-content">
                                    <span class="status-number">{{ $fullBins }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="status-card card-empty">
                            <div class="status-body">
                                <div class="status-title">Collection Trip Today</div>
                                <div class="status-content">
                                    <span class="status-number">{{ $collectionTripToday }}</span>
                                </div>
                            </div>
                        </div>
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
                                                    <div class="progress-bar {{ $barClass }}" style="width: {{ number_format($capacity, 0) }}%;"></div>
                                                </div>
                                                <div class="text-white small fw-bold ms-1">{{ number_format($capacity, 0) }}%</div>
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
<div class="sensor-list-card mb-3">
    <div class="card-header smartbin-gradient sensor-list-header">
        <h6 class="mb-0">
            <i class="fas fa-trash-alt me-2"></i> Sensor List
        </h6>
    </div>

    <div class="sensor-list-body">
        @forelse($assetsWithDevices as $asset)
            <div class="asset-item">

                <!-- ASSET HEADER -->
                <div class="asset-header">
                    <div>
                        <div class="asset-name">
                            {{ $asset->name }} {{ $asset->asset_name }}
                        </div>
                        <div class="asset-location">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $asset->location ?? 'Unknown' }}
                        </div>
                    </div>

                    <a href="{{ route('master-data.assets.details', $asset->id) }}"
                       class="btn btn-view-asset">
                        <i class="far fa-eye"></i>
                    </a>
                </div>

                <!-- WASTE TYPE CARDS -->
                @php
                    // Group devices by waste type
                    $wasteTypes = ['GLASS' => null, 'PAPER' => null, 'GENERAL' => null];
                    $lastEmptied = $lastEmptiedTimes->get($asset->id);
                    
                    foreach($asset->devices as $device) {
                        $latest = $device->latestSensor;
                        $level = $latest?->capacity;
                        $deviceNameUpper = strtoupper($device->device_name);
                        
                        foreach(array_keys($wasteTypes) as $type) {
                            if (str_contains($deviceNameUpper, $type)) {
                                $wasteTypes[$type] = [
                                    'level' => $level,
                                    'latest' => $latest,
                                    'device' => $device
                                ];
                                break;
                            }
                        }
                    }
                @endphp

                <div class="waste-types-row">
                    @foreach($wasteTypes as $type => $data)
                        @if($data !== null)
                            @php
                                $level = $data['level'];
                                $latest = $data['latest'];
                                
                                if ($level === null) {
                                    $status = 'undetected';
                                    $displayValue = '—';
                                } elseif ($level > ($asset->capacitySetting->half_to ?? 79)) {
                                    $status = 'full';
                                    $displayValue = number_format($level, 0) . '%';
                                } elseif ($level > ($asset->capacitySetting->empty_to ?? 39)) {
                                    $status = 'half';
                                    $displayValue = number_format($level, 0) . '%';
                                } else {
                                    $status = 'empty';
                                    $displayValue = number_format($level, 0) . '%';
                                }
                                
                                $timestamp = $latest?->created_at
                                    ? $latest->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m h:i')
                                    : '—';
                                
                                $batteryLow = $latest && $latest->battery !== null && $latest->battery_percentage <= 20;
                            @endphp
                            <div class="waste-type-card {{ strtolower($type) }}">
                                <div class="waste-icon {{ strtolower($type) }}">
                                    @if($type === 'GLASS')
                                        <i class="fas fa-wine-bottle"></i>
                                    @elseif($type === 'PAPER')
                                        <i class="fas fa-newspaper"></i>
                                    @else
                                        <i class="fas fa-trash-alt"></i>
                                    @endif
                                </div>
                                <div class="waste-label">{{ $type }}</div>
                                <div class="waste-capacity {{ $status }}">{{ $displayValue }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- LAST EMPTIED -->
                <div class="asset-footer">
                    <span class="last-emptied">
                        <i class="fas fa-history me-1"></i> Last emptied: {{ $lastEmptied ? $lastEmptied->diffForHumans() : 'Never' }}
                    </span>
                </div>

            </div>
        @empty
            <div class="text-muted text-center py-3">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
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
    background: linear-gradient(270deg, #9457b3, #672d84, #9457b3);
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
        <div class="col-lg-8">
            <!-- NOTIFICATION LOGS-->
<div class="card mb-4">
    <div class="card-header smartbin-gradient">
        <h5 class="mb-0 text-white fs-6 d-flex align-items-center">
            <span>
                <i class="fas fa-inbox"></i> Notification Sent
                <span class="badge badge-info">{{ $todayNotifications->flatten()->count() }}</span>
            </span>

            <a href="{{ route('notifications.index') }}"
            class="ms-auto btn btn-sm btn-light d-flex align-items-center gap-1">
                <i class="fas fa-eye"></i>
            </a>
        </h5>
    </div>

    <div class="card-body p-3">
        @if($todayNotifications->isNotEmpty())
            @foreach($todayNotifications as $date => $logs)
                <div class="mb-3">
                    <!-- Date Header -->
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-calendar-day text-success"></i>
                                @if(Carbon\Carbon::parse($date)->isToday())
                                    Today
                                @elseif(Carbon\Carbon::parse($date)->isYesterday())
                                    Yesterday
                                @else
                                    {{ Carbon\Carbon::parse($date)->format('d M Y') }}
                                @endif
                            </h6>
                        </div>
                        <span class="badge bg-success">{{ $logs->count() }} notifications</span>
                    </div>

                    <!-- Notification Timeline for this date -->
                    <div class="notification-timeline">
                        @php
                            // Get unique messages by message_preview for summary
                            $uniqueLogs = $logs->unique('message_preview')->take(10);
                            $totalCount = $logs->unique('message_preview')->count();
                        @endphp

                        @forelse($uniqueLogs as $log)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>

                                <div class="timeline-content">
                                    <button
                                        class="timeline-button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#notif{{ $log->id }}">

                                        <i class="fas fa-history"></i>
                                        {{ Carbon\Carbon::parse($log->sent_at)->timezone('Asia/Kuala_Lumpur')->format('H:i:s') }}
                                        <span class="text-muted small">({{ $logs->where('message_preview', $log->message_preview)->count() }}x)</span>
                                    </button>

                                    <div id="notif{{ $log->id }}" class="collapse mt-2">
                                        <pre class="mb-0 text-sm">{{ $log->message_preview }}</pre>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">
                                No notifications for this date
                            </div>
                        @endforelse

                        @if($totalCount > 10)
                            <div class="text-center mt-2">
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-success">
                                    View all {{ $totalCount }} notifications <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="my-3">
            @endforeach
        @else
            <div class="text-muted text-center py-4">
                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                <p>No notifications sent today</p>
            </div>
        @endif
    </div>
</div>

            <div class="card mb-4 shadow-sm">
                <div class="card-header smartbin-gradient border-0">
                    <h5 class="mb-0 text-white fs-6">
                        <i class="fab fa-whatsapp me-2"></i> WhatsApp Notification
                    </h5>
                </div>
                <div class="card-body" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0 0 10px 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-semibold">
                                <i class="fas fa-bell text-success"></i> Notification Status
                            </h6>
                            <small class="text-muted">
                                <span id="whatsappStatusText" class="fw-bold {{ $whatsappNotificationActive ? 'text-success' : 'text-muted' }}">
                                    {{ $whatsappNotificationActive ? '● Active' : '○ Inactive' }}
                                </span>
                            </small>
                        </div>

                        <!-- Toggle Switch -->
                        <div>
                            <label class="switch switch-lg">
                                <input type="checkbox" id="whatsappNotificationSwitch"
                                    {{ $whatsappNotificationActive ? 'checked' : '' }}
                                    {{ !$isAdmin ? 'disabled title=No authorization' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>


       <!-- RIGHT COLUMN -->
        <div class="col-lg-4">

        <!-- Upcoming Holidays and Events -->
            <div class="card shadow-sm mb-4">
                <div class="card-header smartbin-gradient">
                    <h5 class="mb-0 text-white fs-6">
                        <i class="fas fa-calendar-plus me-2"></i> Upcoming Holidays & Events
                    </h5>
                </div>
                <div class="card-body" style="background-color: #f8f9fa; border-radius: 0 0 10px 10px;">
                    @if(isset($upcomingHolidaysAndEvents) && $upcomingHolidaysAndEvents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingHolidaysAndEvents as $item)
                                @php
                                    $startDate = Carbon\Carbon::parse($item['start_date']);
                                    $endDate = $item['end_date'] ? Carbon\Carbon::parse($item['end_date']) : null;
                                    $daysUntilStart = now()->diffInDays($startDate, false);
                                    
                                    if ($daysUntilStart == 0) {
                                        $statusText = 'Today';
                                        $badgeClass = 'bg-danger';
                                    } elseif ($daysUntilStart == 1) {
                                        $statusText = 'Tomorrow';
                                        $badgeClass = 'bg-warning';
                                    } else {
                                        $statusText = 'In ' . ceil($daysUntilStart) . ' days';
                                        $badgeClass = 'bg-info';
                                    }
                                @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">
                                            @if($item['type'] === 'holiday')
                                                <i class="fas fa-umbrella-beach text-danger"></i> {{ $item['name'] }}
                                            @else
                                                <i class="fas fa-calendar-alt text-success"></i> {{ $item['name'] }}
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day"></i> 
                                            {{ $startDate->format('Y-m-d') }}
                                            @if($endDate)
                                                → {{ $endDate->format('Y-m-d') }}
                                            @endif
                                            @if($item['type'] === 'event' && isset($item['location']))
                                                <br><i class="fas fa-map-marker-alt"></i> {{ $item['location'] }}
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge {{ $badgeClass }} rounded-pill">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-check-circle fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">No upcoming holidays or events in the next 7 days</p>
                        </div>
                    @endif
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

<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('dashboardMap').setView([3.1427, 101.7176], 17.5);

    // Add OpenStreetMap tiles with modern CartoDB Voyager
       L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: 'TRX SmartBin'
    }).addTo(map);

    // Map controls
    document.getElementById('zoomIn').addEventListener('click', () => map.zoomIn());
    document.getElementById('zoomOut').addEventListener('click', () => map.zoomOut());
    document.getElementById('resetView').addEventListener('click', () => map.setView([3.1427, 101.7181], 17.5));

    // Custom marker icon factory
    function createCustomMarker(color, assetName, deviceCount) {
        const pulseHtml = color === 'red' ? '<div class="marker-pulse"></div>' : '';
        
        return L.divIcon({
            html: `
                <div class="custom-marker marker-${color}">
                    ${pulseHtml}
                    <div class="marker-pin"></div>
                    <i class="fas fa-trash marker-icon"></i>
                </div>
            `,
            className: '',
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -42]
        });
    }

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

            $emptyCount = $devices->filter(fn($device) =>
                $device->latestSensor && $device->asset->capacitySetting &&
                $device->latestSensor->capacity <= $device->asset->capacitySetting->empty_to
            )->count();

            if($fullCount > 0) {
                $color = 'red';
                $statusText = 'Critical';
            } elseif($halfCount > 0) {
                $color = 'orange';
                $statusText = 'Moderate';
            } else {
                $color = 'green';
                $statusText = 'Normal';
            }

            $totalDevices = $devices->count();
            
            // Get average battery percentage
            $batteryLevels = $devices->filter(fn($d) => 
                $d->latestSensor && $d->latestSensor->battery !== null
            )->map(fn($d) => $d->latestSensor->battery_percentage);
            $avgBattery = $batteryLevels->isNotEmpty() ? round($batteryLevels->avg()) : null;
            
            // Get last emptied time
            $lastEmptied = $lastEmptiedTimes->get($asset->id);
            $lastEmptiedFormatted = $lastEmptied ? $lastEmptied->format('d/m/Y H:i') : 'Never';
            $lastEmptiedRelative = $lastEmptied ? $lastEmptied->diffForHumans() : null;
            
            // Get predicted full time
            $predictedFull = $predictedFullTimes->get($asset->id);
            $predictedFullFormatted = $predictedFull ? $predictedFull->format('d/m/Y H:i') : 'N/A';
            $predictedFullRelative = $predictedFull ? $predictedFull->diffForHumans() : null;
        @endphp

        @if($asset->latitude && $asset->longitude)
            L.marker([{{ $asset->latitude }}, {{ $asset->longitude }}], {
                icon: createCustomMarker('{{ $color }}', '{{ $asset->asset_name ?? 'Asset' }}', {{ $totalDevices }})
            }).addTo(map).bindPopup(`
                <div class="popup-header">
                    <i class="fas fa-trash"></i> {{ $asset->asset_name ?? 'Asset' }}
                </div>
                <div class="popup-body" style="padding: 0;">
                    <!-- Status Section -->
                    <div style="padding: 12px 16px; border-bottom: 1px solid #e9ecef; background: #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12px; color: #666;"><i class="fas fa-chart-bar"></i> Status</span>
                            <span style="color: {{ $color === 'red' ? '#dc3545' : ($color === 'orange' ? '#fd7e14' : '#28a745') }}; font-weight: 700; font-size: 13px;">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Info Rows -->
                    <div style="padding: 12px 16px;">
                        <!-- Devices Count -->
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="font-size: 12px; color: #666;"><i class="fas fa-box"></i> Devices</span>
                            <span style="font-weight: 600; font-size: 13px;">{{ $totalDevices }}</span>
                        </div>
                        
                        <!-- Battery Level -->
                        @if($avgBattery !== null)
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="font-size: 12px; color: #666;">
                                <i class="fas fa-battery-three-quarters" style="color: {{ $avgBattery <= 20 ? '#dc3545' : ($avgBattery <= 50 ? '#fd7e14' : '#28a745') }};"></i> Battery
                            </span>
                            <span style="font-weight: 600; font-size: 13px; color: {{ $avgBattery <= 20 ? '#dc3545' : ($avgBattery <= 50 ? '#fd7e14' : '#28a745') }};">{{ $avgBattery }}%</span>
                        </div>
                        @endif
                        
                        <!-- Last Emptied -->
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="font-size: 12px; color: #666;"><i class="fas fa-history"></i> Last Emptied</span>
                            <div style="text-align: right;">
                                <div style="font-weight: 600; font-size: 12px;">{{ $lastEmptiedFormatted }}</div>
                                @if($lastEmptiedRelative)
                                <div style="font-size: 10px; color: #999;">{{ $lastEmptiedRelative }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Predicted Full -->
                        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                            <span style="font-size: 12px; color: #666;">
                                <i class="fas fa-clock" style="color: {{ $predictedFull && $predictedFull->diffInHours(now()) <= 24 ? '#dc3545' : '#28a745' }};"></i> Predicted Full
                            </span>
                            <div style="text-align: right;">
                                <div style="font-weight: 600; font-size: 12px; color: {{ $predictedFull && $predictedFull->diffInHours(now()) <= 24 ? '#dc3545' : '#28a745' }};">{{ $predictedFullFormatted }}</div>
                                @if($predictedFullRelative)
                                <div style="font-size: 10px; color: #999;">{{ $predictedFullRelative }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            `);
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
        <h5 class="modal-title"><i class="fas fa-chart-pie"></i> Summary Notification</h5>
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
        <h5 class="modal-title">SMARTBIN Dashboard – User Guide (Latest Version)</h5>
        <!-- Close button -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-tachometer-alt"></i> 1. Dashboard Overview</h6>
        <p>
          The SMARTBIN Dashboard provides real-time monitoring of all SmartBin units,
          including sensor fill levels, notifications, asset locations, and event planning tools.
          It helps administrators monitor operations efficiently and respond quickly.
        </p>

        <hr>

        <h6><i class="fas fa-th-large"></i> 2. Top Summary Cards</h6>
        <p>Located at the top of the dashboard.</p>
        <ul>
          <li><strong>Total Sensors</strong> – Total registered sensors in the system.</li>
          <li><strong>Full Sensors</strong> – Bins that have reached critical capacity.</li>
          <li><strong>Half-Full Sensors</strong> – Bins that are partially filled.</li>
          <li><strong>Empty Sensors</strong> – Bins currently empty.</li>
          <li><strong>Undetected</strong> – Sensors not reporting data (offline/disconnected).</li>
        </ul>
        <p>👉 Click <strong>More info</strong> to view detailed sensor data.</p>

        <hr>

        <h6><i class="fas fa-map-marked-alt"></i> 3. Assets Map</h6>
        <p>
          Displays the live location of SmartBins on the interactive map.
        </p>
        <ul>
          <li>Zoom in/out using + and – buttons.</li>
          <li>Click on a bin marker to identify the bin's name.</li>
          <li>Reset button restores the default map position.</li>
        </ul>
        <p><strong>Purpose:</strong> Quickly locate bins and monitor geographical distribution.</p>

        <hr>

        <h6><i class="fas fa-list"></i> 4. Sensor List Panel</h6>
        <p>Located on the right side of the dashboard.</p>
        <ul>
          <li>Displays each SmartBin unit (e.g., TRX SmartBin 01).</li>
          <li>Shows individual sensor types (General, Paper, Glass, etc.).</li>
          <li>Displays current fill percentage with color indicators.</li>
          <li>Shows last updated timestamp.</li>
          <li>Eye icon button allows viewing detailed bin information.</li>
        </ul>
        <p><strong>Color Indicators:</strong></p>
        <ul>
          <li>🟢 Green – Safe level</li>
          <li>🟡 Yellow – Moderate level</li>
          <li>🔴 Red – Critical / Nearly Full</li>
        </ul>

        <hr>

        <h6><i class="fas fa-envelope"></i> 5. Notification Sent Panel</h6>
        <p>
          Displays the total number of notifications sent today.
        </p>
        <ul>
          <li>Shows notification count badge.</li>
          <li>If no alerts were sent: “No notifications sent today”.</li>
          <li>Eye icon allows viewing notification history.</li>
        </ul>

        <hr>

        <h6><i class="fab fa-whatsapp"></i> 6. WhatsApp Notification Control</h6>
        <p>Allows administrators to enable or disable automatic WhatsApp alerts.</p>
        <ul>
          <li><strong>ON</strong> – System sends alerts automatically when bins reach threshold.</li>
          <li><strong>OFF</strong> – WhatsApp notifications are disabled.</li>
        </ul>
        <p>Recommended to keep ON during operational hours.</p>

        <hr>

        <h6><i class="fas fa-calendar-alt"></i> 7. Smart Calendar (Events & Notifications)</h6>
        <p>
          Interactive monthly/weekly calendar view.
        </p>
        <ul>
          <li>Displays public holidays and special events.</li>
          <li>Shows grouped notification logs per day.</li>
          <li>Click a notification label to view all alerts for that date.</li>
          <li>Switch between Month and Week view.</li>
        </ul>
        <p><strong>Purpose:</strong> Assist planning during high-traffic events or holidays.</p>

        <hr>

        <h6><i class="fas fa-question-circle"></i> 8. Help Button</h6>
        <p>
          The orange <strong>?</strong> button at the bottom right opens this User Guide.
        </p>

        <hr>

        <h6><i class="fas fa-check-circle"></i> 9. Best Practices</h6>
        <ul>
          <li>✔ Check dashboard daily.</li>
          <li>✔ Respond immediately to red (critical) bins.</li>
          <li>✔ Monitor undetected sensors.</li>
          <li>✔ Keep WhatsApp notifications enabled.</li>
          <li>✔ Review calendar before major events.</li>
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
            right: isMobile ? 'dayGridWeek,dayGridMonth' : 'dayGridMonth,dayGridWeek'
        },
        eventDisplay: 'block',
        
        buttonText: {
            month: 'Month',
            week: 'Week',
            day: 'DAY',
            list: 'LIST'
        },

        events: calendarEvents,

        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.title);
        },

        eventClick: function(info) {
    if (info.event.extendedProps.type === 'notification_group') {
        // Get the clicked date
        const clickedDate = info.event.start.toLocaleDateString();
        
        // Filter notifications for the clicked date only
        const notificationsForDate = [];
        let totalNotifications = 0;
        let fullBinsCount = 0;
        let emptyBinsCount = 0;
        let otherCount = 0;

        info.event.extendedProps.notifications.forEach(n => {
            let messages = n.message_preview.split('🆔').filter(m => m.trim() !== '');
            messages.forEach(msg => {
                notificationsForDate.push(msg.trim());
                totalNotifications++;

                // Categorize notifications
                const msgLower = msg.toLowerCase();
                if (msgLower.includes('full') || msgLower.includes('critical')) {
                    fullBinsCount++;
                } else if (msgLower.includes('half') || msgLower.includes('moderate')) {
                    halfBinsCount++;
                } else if (msgLower.includes('empty') || msgLower.includes('cleared')) {
                    emptyBinsCount++;
                } else {
                    otherCount++;
                }
            });
        });

        // Build HTML for the clicked date
        let html = '';

        // Summary Section
        html += `<div class="card bg-light mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie"></i> Summary for ${clickedDate}</h6>
                <div class="row g-3">
                    <div class="col-4">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-primary">${totalNotifications}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-danger">${otherCount}</div>
                            <small class="text-muted">Full Bins</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-success">${emptyBinsCount}</div>
                            <small class="text-muted">Emptied</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        // Pie Chart Section
        html += `<div class="card mb-3">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-chart-pie"></i> Notification Details
            </div>
            <div class="card-body">
                <canvas id="notificationPieChart" height="250"></canvas>
            </div>
        </div>`;

        $('#notificationListContainer').html(html);
        
        // Render Pie Chart
        const ctx = document.getElementById('notificationPieChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (window.notificationChartInstance) {
            window.notificationChartInstance.destroy();
        }
        
        window.notificationChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Full Bins', 'Emptied'],
                datasets: [{
                    data: [otherCount, emptyBinsCount],
                    backgroundColor: [
                        '#dc3545',  // Red for Full Bins
                        '#28a745'   // Green for Emptied
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
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
    const whatsappStatusText = document.getElementById('whatsappStatusText');

    // Auto-save when toggle changes
    whatsappSwitch.addEventListener('change', function() {
        const isActive = whatsappSwitch.checked;
        
        // Update status text immediately
        if (isActive) {
            whatsappStatusText.textContent = '● Active';
            whatsappStatusText.className = 'fw-bold text-success';
        } else {
            whatsappStatusText.textContent = '○ Inactive';
            whatsappStatusText.className = 'fw-bold text-muted';
        }

        // Auto-save to server
        fetch('{{ route("dashboard.toggleWhatsappNotification") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                is_active: isActive
            }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Success notification
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'WhatsApp notification status updated to ' + (data.is_active ? 'Active' : 'Inactive'),
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                // Revert if failed
                whatsappSwitch.checked = !isActive;
                if (whatsappSwitch.checked) {
                    whatsappStatusText.textContent = '● Active';
                    whatsappStatusText.className = 'fw-bold text-success';
                } else {
                    whatsappStatusText.textContent = '○ Inactive';
                    whatsappStatusText.className = 'fw-bold text-muted';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update notification status',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        })
        .catch(err => {
            console.error(err);
            // Revert if failed
            whatsappSwitch.checked = !isActive;
            if (whatsappSwitch.checked) {
                whatsappStatusText.textContent = '● Active';
                whatsappStatusText.className = 'fw-bold text-success';
            } else {
                whatsappStatusText.textContent = '○ Inactive';
                whatsappStatusText.className = 'fw-bold text-muted';
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update notification status',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        });
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

@endsection
