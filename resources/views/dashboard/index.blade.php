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

.card-total { background-color: #8c9195ff; }       /* Soft Blue */
.card-full { background-color: #e74c3c; }        /* Soft Red */
.card-half { background-color: #f39c12; }        /* Soft Yellow/Gold */
.card-empty { background-color: #7ccc63; }       /* Soft Green */
.card-undetected { background-color: #2c3e50; }  /* Soft Purple */

.full-devices-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-left: 14px;
}

.full-device-card {
    background-color: #6f060687; /* dark red */
    border: 2px solid #ff4d4d;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(255, 0, 0, 0.5);
    transition: transform 0.2s, box-shadow 0.2s;
}

.full-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 18px rgba(255, 0, 0, 0.7);
}

.full-status {
    background-color: #FF0000; /* bright red */
    padding: 0.5em 1em; /* slightly bigger padding */
    border-radius: 25px; /* more oval */
    font-size: 1.1rem; /* bigger text */
    font-weight: 700;
    /* glowing effect */
    box-shadow: 0 0 8px #FF0000, 0 0 12px #FF4d4d, 0 0 18px #FF6666;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666;
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 10px #CC0000, 0 0 14px #D93333, 0 0 20px #E06666;
        transform: scale(1.05);
    }
    100% {
        box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666;
        transform: scale(1);
    }
}

.full-device-card .fw-bold.fs-4 {
    font-size: 1.3rem; /* bigger device name */
    font-weight: bold;
}

</style>

<div class="d-flex flex-wrap">
    <!-- Total Devices -->
    <div class="status-card card-total">
        <div class="status-title">Total Devices</div>
        <div class="status-content">
            <i class="fas fa-satellite-dish status-icon"></i>
            <span class="status-number">{{ $totalDevices }}</span>
        </div>
    </div>

    <!-- Full Devices -->
    <div class="status-card card-full">
        <div class="status-title">Full Devices</div>
        <div class="status-content">
            <i class="fas fa-trash status-icon"></i>
            <span class="status-number">{{ $fullDevices }}</span>
        </div>
    </div>

    <!-- Half Full -->
    <div class="status-card card-half">
        <div class="status-title ">Half Full</div>
        <div class="status-content">
            <i class="fas fa-exclamation-triangle status-icon"></i>
            <span class="status-number">{{ $halfDevices }}</span>
        </div>
    </div>

    <!-- Empty -->
    <div class="status-card card-empty">
        <div class="status-title">Empty Devices</div>
        <div class="status-content">
            <i class="fas fa-recycle status-icon"></i>
            <span class="status-number">{{ $emptyDevices }}</span>
        </div>
    </div>

    <!-- Undetected -->
    <div class="status-card card-undetected">
        <div class="status-title">Undetected</div>
        <div class="status-content">
            <i class="fas fa-minus-circle status-icon"></i>
            <span class="status-number">{{ $undetectedDevices }}</span>
        </div>
    </div>
</div>

<div class="full-devices-cards mt-4 d-flex flex-wrap gap-3">
    @foreach($fullDevicesCollection as $device)
        <a href="{{ route('master-data.assets.index') }}" class="text-decoration-none">
            <div class="card full-device-card position-relative p-3" style="width: 280px;">
                
                <!-- Device Name Top Left -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                    
                    <!-- Full Circle Top Right -->
                    <div class="badge full-status text-white fw-bold fs-5">FULL</div>
                </div>

                <!-- Floor Location -->
                <div class="mt-2 text-white">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    {{ $device->asset->floor->floor_name ?? 'Unknown Floor' }}
                </div>

                <!-- Progress Bar -->
                <div class="progress mt-3" style="height: 10px; border-radius: 6px;">
                    <div class="progress-bar bg-danger" role="progressbar" 
                         style="width: {{ $device->latestSensor->capacity ?? 0 }}%;" 
                         aria-valuenow="{{ $device->latestSensor->capacity ?? 0 }}" 
                         aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>

            </div>
        </a>
    @endforeach
</div>

@endsection