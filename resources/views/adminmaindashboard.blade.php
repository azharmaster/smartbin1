@extends('layouts.nosidebar')

@section('content_title', 'Main Dashboard')

@section('content')

<div class="container-fluid mt-4">
    <div class="row">

        {{-- LEFT COLUMN : FLOOR MAP --}}
        <div class="col-lg-8">

            {{-- ✅ YOUR MAP CODE (UNCHANGED) --}}
            <div class="card card-success map-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt"></i> Floor Map
                    </h5>
                    <button class="btn p-0 collapse-btn ms-auto" id="toggleMap" type="button">
                        <i class="fas fa-minus fa-lg"></i>
                    </button>
                </div>

                <div class="map-collapse-wrapper" style="height: 650px; overflow: hidden; transition: height 0.3s ease;">
                    <div class="card-body map-card-body" style="height: 100%;">

                        @php
                            $firstFloor = $floors->first();
                        @endphp

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
                                        data-asset-name="{{ $asset->asset_name ?? 'Asset' }}"
                                        data-asset-status="{{ $asset->status ?? 'Unknown' }}"
                                        title="{{ $asset->asset_name ?? 'Asset' }}"
                                        style="position: absolute;
                                               width: 24px; height: 24px;
                                               left: calc({{ $asset->x }}px + 135px);
                                               top: calc({{ $asset->y }}px - 10px); cursor:pointer;">
                                        <i class="fas fa-trash-alt"
                                           style="font-size: 22px; color: #166b34;
                                                  filter: drop-shadow(0 0 4px #00ff7a);"></i>
                                    </div>
                                @endforeach

                                {{-- BIN POPUP --}}
                                <div id="binPopup" style="display:none; position:absolute; background:#fff; border:1px solid #333; padding:10px; border-radius:6px; z-index:1000; min-width:150px; box-shadow: 0 0 10px rgba(0,0,0,.3);"></div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN : WARNING DEVICES --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Warning Devices
                    </h5>
                </div>

                <div class="card-body">

                    {{-- FULL --}}
                    <div class="warning-card danger mb-3">
                        <div class="d-flex justify-content-between">
                            <h4>TR001-1</h4>
                            <span class="badge badge-danger">FULL</span>
                        </div>
                        <small><i class="fas fa-map-marker-alt"></i> Concourse</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-danger" style="width: 90%"></div>
                        </div>
                    </div>

                    {{-- HALF --}}
                    <div class="warning-card warning mb-3">
                        <div class="d-flex justify-content-between">
                            <h4>TR001-2</h4>
                            <span class="badge badge-warning">HALF</span>
                        </div>
                        <small><i class="fas fa-map-marker-alt"></i> Concourse</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-warning" style="width: 60%"></div>
                        </div>
                    </div>

                    {{-- HALF --}}
                    <div class="warning-card warning">
                        <div class="d-flex justify-content-between">
                            <h4>TR001-3</h4>
                            <span class="badge badge-warning">HALF</span>
                        </div>
                        <small><i class="fas fa-map-marker-alt"></i> Concourse</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-warning" style="width: 55%"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

{{-- BIN POPUP & FLOOR SCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const floorSelect = document.getElementById('floorSelect');
        const floorImage = document.getElementById('dashboardFloorImage');
        const assetMarkers = document.querySelectorAll('.asset-marker');
        const binPopup = document.getElementById('binPopup');

        // Show popup on bin click (centered above bin)
        assetMarkers.forEach(marker => {
            marker.addEventListener('click', function(e) {
                const rect = marker.getBoundingClientRect();
                const containerRect = document.getElementById('dashboardMapInner').getBoundingClientRect();
                
                const popupWidth = binPopup.offsetWidth || 150;
                const popupHeight = binPopup.offsetHeight || 60;
                
                // Center horizontally, above the bin
                const leftPos = rect.left - containerRect.left + (rect.width / 2) - (popupWidth / 2);
                const topPos = rect.top - containerRect.top - popupHeight - 10; // 10px above
                
                binPopup.style.left = leftPos + 'px';
                binPopup.style.top = topPos + 'px';
                binPopup.innerHTML = `
                    <b>${marker.dataset.assetName}</b><br>
                    Status: ${marker.dataset.assetStatus}<br>
                    ID: ${marker.dataset.assetId}
                `;
                binPopup.style.display = 'block';
            });
        });

        // Hide popup when clicking outside
        document.getElementById('dashboardMapInner').addEventListener('click', function(e) {
            if(!e.target.classList.contains('asset-marker') && !e.target.closest('.asset-marker')) {
                binPopup.style.display = 'none';
            }
        });

        // Switch floor
        floorSelect.addEventListener('change', function() {
            const selectedImage = this.value;
            floorImage.src = selectedImage;

            const selectedFloorId = this.options[this.selectedIndex].dataset.floorId;

            assetMarkers.forEach(marker => {
                if(marker.dataset.floorId === selectedFloorId) {
                    marker.style.display = 'block';
                } else {
                    marker.style.display = 'none';
                }
            });

            binPopup.style.display = 'none'; // hide popup when switching floors
        });
    });
</script>

{{-- STYLES --}}
<style>
.warning-card {
    padding: 15px;
    border-radius: 12px;
    color: #fff;
}
.warning-card.danger {
    background: #b76b6b;
    box-shadow: 0 0 15px rgba(255,0,0,.4);
}
.warning-card.warning {
    background: #c8b67a;
    box-shadow: 0 0 15px rgba(255,193,7,.4);
}
.progress {
    height: 8px;
}

/* Optional: bin popup arrow centered */
#binPopup::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
}
</style>

@endsection
