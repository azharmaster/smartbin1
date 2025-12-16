@extends('layouts.nosidebaradmin')

@section('content_title', 'Main Dashboard')

@section('content')

{{-- back button --}}
<div class="container-fluid mt-3">
    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>&nbsp;

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

.card-total { background-color: rgba(255, 255, 255, 0.1); }
.card-full { background-color: rgba(255, 255, 255, 0.1); }
.card-half { background-color: rgba(255, 255, 255, 0.1); }
.card-empty { background-color: rgba(255, 255, 255, 0.1); }
.card-undetected { background-color: rgba(255, 255, 255, 0.1); }
.card-primary { background-color: rgba(255, 255, 255, 0.1); }

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

/* HALF DEVICE CARD (similar style but yellow, no pulse) */
.empty-device-card {
    background-color: #4cd9633a; /* translucent yellow-brown */
    border: 2px solid #4ab65eff;
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

.devices-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* ✅ 4 in a row */
    gap: 16px;
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
        grid-template-columns: repeat(3, 1fr);
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
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="d-flex flex-wrap">
    <div class="status-card card-total" style="color: #4cd964;">
        <div class="status-title">Total Devices</div>
        <div class="status-content" style="color: #fff">
            <i class="fas fa-satellite-dish status-icon"></i>
            <span class="status-number">{{ $totalDevices }}</span>
        </div>
    </div>
    <div class="status-card card-full" style="color: #4cd964;">
        <div class="status-title">Full Devices</div>
        <div class="status-content" style="color: #fff">
            <i class="fas fa-trash status-icon"></i>
            <span class="status-number">{{ $fullDevices }}</span>
        </div>
    </div>
    <div class="status-card card-half" style="color: #4cd964;">
        <div class="status-title ">Half Full</div>
        <div class="status-content" style="color: #fff">
            <i class="fas fa-exclamation-triangle status-icon"></i>
            <span class="status-number">{{ $halfDevices }}</span>
        </div>
    </div>
    <div class="status-card card-empty" style="color: #4cd964;">
        <div class="status-title">Empty Devices</div>
        <div class="status-content" style="color: #fff">
            <i class="fas fa-recycle status-icon"></i>
            <span class="status-number">{{ $emptyDevices }}</span>
        </div>
    </div>
    <div class="status-card card-undetected" style="color: #4cd964;">
        <div class="status-title">Undetected</div>
        <div class="status-content" style="color: #fff">
            <i class="fas fa-minus-circle status-icon"></i>
            <span class="status-number">{{ $undetectedDevices }}</span>
        </div>
    </div>
</div>


    <div class="card mb-4" style=" background-color: rgba(0, 0, 0, 0)">
        <div class="p-3 scroll-body" style="overflow-y: auto;">
            <!-- GRID CONTAINER -->
            <div class="d-flex justify-content-end mb-3">
                <select id="deviceFilter"
                        class="form-select form-select-md w-auto px-2">
                    <option value="critical">Default</option>
                    <option value="all">All Devices</option>
                    <option value="full">Full</option>
                    <option value="half">Half</option>
                    <option value="empty">Empty</option>
                </select>
            </div>

            <div class="devices-grid">

                {{-- FULL DEVICES --}}
                @foreach($fullDevicesCollection as $device)
                    <a href="#" class="text-decoration-none device-link open-bin-modal"
                     data-url="{{ route('admin.dashboard.bin.popup', $device->asset->id) }}">
                    <!-- class="text-decoration-none device-link"> -->

                        <div class="device-card full-device-card" data-status="full">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                                <div class="badge full-status text-white">FULL</div>
                            </div>

                            <div class="mt-1 text-white">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $device->asset->floor->floor_name ?? 'Unknown' }}
                            </div>

                            <div class="progress mt-2" style="height: 12px; border-radius: 999px; ">
                                <div class="progress-bar bg-danger"
                                    style="width: {{ $device->latestSensor->capacity ?? 0 }}%;"></div>
                            </div>
                        </div>
                    </a>
                @endforeach

                {{-- HALF DEVICES --}}
                @foreach($halfDevicesCollection as $device)
                    <a href="#" class="text-decoration-none device-link open-bin-modal"
                     data-url="{{ route('admin.dashboard.bin.popup', $device->asset->id) }}">

                    <!-- class="text-decoration-none device-link"> -->

                        <div class="device-card half-device-card" data-status="half">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                                <div class="badge half-status text-dark">HALF</div>
                            </div>

                            <div class="mt-1 text-white">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $device->asset->floor->floor_name ?? 'Unknown' }}
                            </div>

                            <div class="progress mt-2" style="height: 12px; border-radius: 999px;">
                                <div class="progress-bar bg-warning"
                                    style="width: {{ $device->latestSensor->capacity ?? 0 }}%;"></div>
                            </div>
                        </div>
                    </a>
                @endforeach

                {{-- EMPTY DEVICES --}}
                @foreach($emptyDevicesCollection as $device)
                    <a href="#" class="text-decoration-none device-link open-bin-modal"
                     data-url="{{ route('admin.dashboard.bin.popup', $device->asset->id) }}">
                    <!-- class="text-decoration-none device-link"> -->

                        <div class="device-card empty-device-card" data-status="empty">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-bold fs-4 text-white">{{ $device->device_name }}</div>
                                <div class="badge empty-status text-dark">EMPTY</div>
                            </div>

                            <div class="mt-1 text-white">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $device->asset->floor->floor_name ?? 'Unknown' }}
                            </div>

                            <div class="progress mt-2" style="height: 12px; border-radius: 999px;">
                                <div class="progress-bar bg-success"
                                    style="width: {{ $device->latestSensor->capacity ?? 0 }}%;"></div>
                            </div>
                        </div>
                    </a>
                @endforeach

            </div>
        </div>
    </div>


{{-- BIN POPUP & FLOOR SCRIPT --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    const filterSelect = document.getElementById('deviceFilter');
    const cards = document.querySelectorAll('.device-card');

    function applyFilter(filter) {
        cards.forEach(card => {
            const status = card.dataset.status;

            let show = false;

            if (filter === 'all') {
                show = true;
            } else if (filter === 'critical') {
                show = (status === 'full' || status === 'half');
            } else {
                show = (status === filter);
            }

            // hide/show entire link wrapper
            card.closest('a').style.display = show ? '' : 'none';
        });
    }

    // ✅ default: show FULL + HALF
    applyFilter('critical');

    filterSelect.addEventListener('change', () => {
        applyFilter(filterSelect.value);
    });

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

{{-- BIN DETAILS MODAL --}}
<div class="modal fade" id="binDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bin Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="binModalContent">
                {{-- content loaded dynamically --}}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.open-bin-modal').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();

            const url = this.dataset.url;

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('binModalContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('binDetailsModal')).show();
                });
        });
    });
});
</script>



@endsection
