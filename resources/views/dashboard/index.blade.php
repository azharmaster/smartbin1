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

<div class="container-fluid mt-4">

    <div class="row">

        <!-- LEFT COLUMN: MAP -->
        <div class="col-lg-7">

            <div class="card card-success map-card mb-4">
                <!-- Header always visible -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt"></i> Floor Map
                    </h5>
                    <button class="btn p-0 collapse-btn ms-auto" id="toggleMap" type="button">
                        <i class="fas fa-minus fa-lg"></i>
                    </button>
                </div>

                <!-- Collapsible wrapper -->
                <div class="map-collapse-wrapper" style="height: 650px; overflow: hidden; transition: height 0.3s ease;">
                    <div class="card-body map-card-body" style="height: 100%;">

                        @php
                            $firstFloor = $floors->first();
                        @endphp

                        <!-- Controls -->
                        <div class="map-controls mb-3 d-flex gap-2 align-items-center flex-wrap">
                            <select id="floorSelect" class="form-select form-select-sm" style="width: 200px;">
                                @foreach($floors as $floor)
                                    <option value="{{ asset('uploads/floor/' . $floor->picture) }}"
                                            data-floor-id="{{ $floor->id }}">
                                        {{ $floor->floor_name }}
                                    </option>
                                @endforeach
                            </select>
                            <button id="zoomIn" class="btn btn-secondary btn-sm">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button id="zoomOut" class="btn btn-secondary btn-sm">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button id="resetView" class="btn btn-secondary btn-sm">
                                <i class="fas fa-crosshairs"></i> Reset
                            </button>
                        </div>

                        <!-- MAP -->
                        <div id="dashboardMapWrapper" style="position: relative; width: 100%; height: 600px;">
                            <div id="dashboardMapInner" style="position: relative; width: 100%; height: 100%;">

                                <img id="dashboardFloorImage"
                                    src="{{ $firstFloor ? asset('uploads/floor/' . $firstFloor->picture) : '' }}"
                                    alt="Floor Image"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">

                                @foreach($assetsWithCoords as $asset)
                                    <div class="asset-marker"
                                        data-asset-id="{{ $asset->id }}"
                                        data-floor-id="{{ $asset->floor_id }}"
                                        title="{{ $asset->asset_name ?? 'Asset' }}"
                                        style="position: absolute;
                                                width: 24px; height: 24px;
                                                left: calc({{ $asset->x }}px + 30px);
                                                top: calc({{ $asset->y }}px);">
                                        <i class="fas fa-trash-alt"
                                            style="font-size: 22px; color: #166b34;
                                                filter: drop-shadow(0 0 4px #00ff7a);"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           <!-- ASSIGNED TASKS -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-tasks"></i> Assigned Tasks
        </h5>
    </div>
    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped mb-0">
            <thead class="table-light sticky-top">
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Asset</th>
                    <th>Floor</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Sort tasks by creation date descending for display
                    $tasksToShow = $assignedTasks->sortByDesc('created_at')->take(10);
                @endphp

                @foreach($tasksToShow as $task)
                    <tr>
                        <td>{{ $task->id }}</td> <!-- Use original task ID for consistent numbering -->
                        <td>{{ $task->user->name ?? 'N/A' }}</td>
                        <td>{{ $task->asset->asset_name ?? 'N/A' }}</td>
                        <td>{{ $task->floor->floor_name ?? 'N/A' }}</td>
                        <td>{{ $task->description }}</td>
                        <td>
                            @php
                                $status = $task->status;
                                $badgeClass = match($status) {
                                    'pending' => 'bg-warning',
                                    'in_progress' => 'bg-info',
                                    'completed' => 'bg-success',
                                    'reject' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>
                        <td>{{ $task->notes ?? '-' }}</td>
                    </tr>
                @endforeach

                @if($tasksToShow->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center">No tasks assigned.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>




        </div>

        <!-- RIGHT COLUMN: FULL BINS + TODO LIST -->
        <div class="col-lg-5">

            <!-- FULL BINS -->
            <div class="card mb-4" style="max-height: 500px;">

                <div class="p-3 border-bottom sticky-header card-full">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-trash-alt"></i> Warning Devices
                    </h5>
                </div>
                <div class="p-3 scroll-body" style="overflow-y: auto; max-height: 440px;">
                    <div class="full-devices-cards">

                        {{-- FULL DEVICES --}}
                        @foreach($fullDevicesCollection as $device)
                            <a href="{{ route('master-data.assets.details', $device->asset->id) }}"
                            class="text-decoration-none">
                                <div class="full-device-card p-3" style="width: 430px;">

                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                                        <div class="badge full-status text-white">FULL</div>
                                    </div>

                                    <div class="mt-1 text-white">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $device->asset->floor->floor_name ?? 'Unknown' }}
                                    </div>

                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-danger"
                                            style="width: {{ $device->latestSensor->capacity ?? 0 }}%;"></div>
                                    </div>

                                </div>
                            </a>
                        @endforeach


                        {{-- HALF-FULL DEVICES --}}
                        @foreach($halfDevicesCollection as $device)
                            <a href="{{ route('master-data.assets.details', $device->asset->id) }}"
                            class="text-decoration-none">
                                <div class="half-device-card p-3" style="width: 430px;">

                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                                        <div class="badge half-status text-dark">HALF</div>
                                    </div>

                                    <div class="mt-1 text-white">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $device->asset->floor->floor_name ?? 'Unknown' }}
                                    </div>

                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-warning"
                                            style="width: {{ $device->latestSensor->capacity ?? 0 }}%;"></div>
                                    </div>

                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
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
                        @default Unknown
                    @endswitch
                </span>
            </li>
        @endforeach
    </ul>
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

    document.getElementById('toggleMap').addEventListener('click', function() {
        const wrapper = document.querySelector('.map-collapse-wrapper');

        if (wrapper.style.height === '0px' || wrapper.style.height === '0') {
            // Expand
            wrapper.style.height = '650px';
            this.querySelector('i').classList.replace('fa-plus', 'fa-minus');
        } else {
            // Collapse
            wrapper.style.height = '0';
            this.querySelector('i').classList.replace('fa-minus', 'fa-plus');
        }
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

});
</script>

@endsection
