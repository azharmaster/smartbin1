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

.card-total { background-color: #8c9195ff; }
.card-full { background-color: #e74c3c; }
.card-half { background-color: #f39c12; }
.card-empty { background-color: #7ccc63; }
.card-undetected { background-color: #2c3e50; }

.full-devices-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-left: 14px;
}

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

.full-status {
    background-color: #FF0000;
    padding: 0.5em 1em;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 700;
    box-shadow: 0 0 8px #FF0000, 0 0 12px #FF4d4d, 0 0 18px #FF6666;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
    50% { box-shadow: 0 0 10px #CC0000, 0 0 14px #D93333, 0 0 20px #E06666; transform: scale(1.05); }
    100% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
}

.full-device-card .fw-bold.fs-4 {
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

.collapse-btn {
    border: none;
    background: transparent;
    cursor: pointer;
}

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
    transition: all 0.3s ease;
}

.map-card-body.collapsed {
    display: none;
}
</style>
{{-- @if($fullDevices > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> Alert!</strong>  
        There are <strong>{{ $fullDevices }}</strong> full trash bins that need attention.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif --}}
<div class="d-flex flex-wrap">
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
        <div class="status-title ">Half Full</div>
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

{{-- ✅ FULL WIDTH MAP AT THE VERY TOP --}}
<div class="w-100 mb-4">

    <div class="card card-success map-card" style="margin-top: 20px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-map-marked-alt"></i> Floor Map
            </h5>
            <button class="btn btn-tool collapse-btn" style="padding-left: 1050px;" type="button">
                <i class="fas fa-plus" ></i>
            </button>
        </div>

        <div class="card-body map-card-body collapsed" style="height: 100%;"> {{-- ✅ AUTO COLLAPSED --}}

            @php
                $firstFloor = $floors->first();
            @endphp

            <div class="map-controls mb-3 d-flex gap-2 align-items-center flex-wrap">
                <select id="floorSelect">
                    @foreach($floors as $floor)
                        <option value="{{ asset('uploads/floor/' . $floor->picture) }}"
                                data-floor-id="{{ $floor->id }}">
                            {{ $floor->floor_name }}
                        </option>
                    @endforeach
                </select>&nbsp;

                <button id="zoomIn" type="button" class="btn btn-secondary">
                    <i class="fas fa-search-plus"></i>
                </button>&nbsp;

                <button id="zoomOut" type="button" class="btn btn-secondary">
                    <i class="fas fa-search-minus"></i>
                </button>&nbsp;

                <button id="resetView" type="button" class="btn btn-secondary">
                    <i class="fas fa-crosshairs"></i> Reset
                </button>&nbsp;
            </div>

<!-- DASHBOARD MAP — matches admin/editor map size & coordinate system -->
<div id="dashboardMapWrapper" style="position: relative; width: 50%; height: 600px; margin: 0 auto;">
    <div id="dashboardMapInner" style="position: relative; width: 100%; height: 100%;">

        <img
            id="dashboardFloorImage"
            src="{{ $firstFloor ? asset('uploads/floor/' . $firstFloor->picture) : '' }}"
            alt="Floor Image"
            style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px; pointer-events: none;"
        >

        {{-- ALL ASSET MARKERS (use same pixel x/y as admin) --}}
        @foreach($assetsWithCoords as $asset)
    <div class="asset-marker"
         data-asset-id="{{ $asset->id }}"
         data-floor-id="{{ $asset->floor_id }}"
         title="{{ $asset->asset_name ?? 'Asset' }}"
         style="
             position: absolute;
             width: 24px;
             height: 24px;
             cursor: pointer;
             left: {{ $asset->x ?? 0 }}px;
             top: {{ $asset->y ?? 0 }}px;
             z-index: 9999;
             display: flex;
             justify-content: center;
             align-items: center;
         ">
         <i class="fas fa-trash-alt" style="
    font-size: 22px;
    color: #166b34ff;
    filter: drop-shadow(0 0 4px #00ff7a) drop-shadow(0 0 6px #00ff7a);
"></i>
    </div>
@endforeach
    </div>
</div>
    </div>
</div>



{{-- ✅ ALERT FULL BINS + TODO LIST SAME ROW --}}
<div class="d-flex gap-4 mt-4">

    {{-- LEFT: ALERT / FULL BINS --}}
    <div style="flex: 2;">
        <div class="full-devices-cards d-flex flex-wrap gap-3">
            @foreach($fullDevicesCollection as $device)
                <a href="{{ route('master-data.assets.details', $device->asset->id) }}" class="text-decoration-none">
                    <div class="card full-device-card position-relative p-3" style="width: 280px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                            <div class="badge full-status text-white fw-bold fs-5">FULL</div>
                        </div>

                        <div class="mt-2 text-white">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $device->asset->floor->floor_name ?? 'Unknown Floor' }}
                        </div>

                        <div class="progress mt-3" style="height: 10px;">
                            <div class="progress-bar bg-danger"
                                style="width: {{ $device->latestSensor->capacity ?? 0 }}%;">
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- RIGHT: TODO LIST --}}
    <div style="flex: 1;">
        <div class="card p-3">
            <h4 class="fw-bold mb-3">
                <a href="{{ route('todos.index') }}" class="text-decoration-none text-dark">
                    To Do List
                </a>
            </h4>
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



{{-- ✅ BINS LIST AT THE VERY BOTTOM --}}
{{-- <div class="bins-container">
        <div class="bins-header">
            <h2><i class="fas fa-list"></i>Bin List</h2>
            <div class="bin-count" id="binCount">{{ $devices->count() }} Tong</div>
            <button class="collapse-btn bins-collapse" aria-label="Toggle bins">
                <i class="fas fa-minus"></i>
            </button>
        </div>
        <div class="bins-list" id="binsList">
            @foreach($devices as $device)
                @php
                    $sensor = $device->latestSensor;
                    $capacity = $sensor->capacity ?? 0;
                    $statusClass = $capacity > 85 ? 'warning' : 'normal';
                    $statusText = $capacity > 85 ? 'AMARAN' : 'NORMAL';
                    $lastUpdated = $sensor->time ?? 'Tiada Data';
                @endphp
                <div class="bin-card {{ $statusClass }}">
                    <div class="bin-header">
                        <div class="bin-id">{{ $device->device_name }}</div>
                        <div class="bin-status status-{{ $statusClass }}">{{ $statusText }}</div>
                    </div>
                    <div class="bin-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $device->asset->floor->floor_name ?? 'Unknown Floor' }}</span>
                    </div>
                    <div class="bin-progress">
                        <div class="bin-progress-bar progress-{{ $statusClass }}" style="width: {{ $capacity }}%"></div>
                    </div>
                    <div class="bin-details">
                        <div class="bin-percentage">{{ $capacity }}%</div>
                        <div class="last-updated">{{ $lastUpdated }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div> --}}

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ✅ ZOOM SETUP
    let scale = 1;
    const mapInner = document.getElementById('dashboardMapInner');   // ✅ FIXED
    const image = document.getElementById('dashboardFloorImage');    // ✅ FIXED

    document.getElementById('zoomIn').addEventListener('click', function () {
        scale += 0.1;
        mapInner.style.transform = `scale(${scale})`;
        mapInner.style.transformOrigin = "top left";
    });

    document.getElementById('zoomOut').addEventListener('click', function () {
        scale = Math.max(0.5, scale - 0.1);
        mapInner.style.transform = `scale(${scale})`;
        mapInner.style.transformOrigin = "top left";
    });

    document.getElementById('resetView').addEventListener('click', function () {
        scale = 1;
        mapInner.style.transform = `scale(1)`;
        mapInner.style.transformOrigin = "top left";
    });

    // ✅ COLLAPSE BUTTON
    document.querySelector('.collapse-btn').addEventListener('click', function () {
        const body = document.querySelector('.map-card-body');
        body.classList.toggle('collapsed');

        this.innerHTML = body.classList.contains('collapsed')
            ? '<i class="fas fa-plus"></i>'
            : '<i class="fas fa-minus"></i>';
    });

    // ✅ FLOOR SWITCH + MARKER FILTER
    const floorSelect = document.getElementById('floorSelect');

    floorSelect.addEventListener('change', function () {
        const selectedImage = this.value;
        const selectedFloorId = this.options[this.selectedIndex].dataset.floorId;

        // ✅ Change image
        image.src = selectedImage;

        // ✅ Filter markers
        document.querySelectorAll('.asset-marker').forEach(marker => {
            if (marker.dataset.floorId === selectedFloorId) {
                marker.style.display = 'block';
            } else {
                marker.style.display = 'none';
            }
        });
    });

    // ✅ DEFAULT FLOOR FILTER ON PAGE LOAD
    const defaultFloorId = floorSelect.options[floorSelect.selectedIndex].dataset.floorId;

    document.querySelectorAll('.asset-marker').forEach(marker => {
        if (marker.dataset.floorId === defaultFloorId) {
            marker.style.display = 'block';
        } else {
            marker.style.display = 'none';
        }
    });

    @if($fullDevices > 0)
    alert("⚠️ There are {{ $fullDevices }} FULL trash bins that need attention!");
@endif


});
</script>

@endsection
