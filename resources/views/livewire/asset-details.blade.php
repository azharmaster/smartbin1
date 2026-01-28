<div> {{-- Single Livewire root wrapper start --}}

<style>
* {
    box-sizing: border-box;
}

.page-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    gap: 20px;
    width: 100%;
}

/* Desktop columns */
.left-column {
    flex: 0 0 50%;
}

.right-column {
    flex: 1;
}

.bin-fill {
    transition: fill-opacity 0.2s ease, filter 0.2s ease;
}

.bin-fill:hover {
    fill-opacity: 1.0;
    filter: brightness(1.15);
}

.device-card {
    margin-left: 16px;
    width: 240px;
    background: #ffffff;
    border-radius: 12px;
    padding: 38px 14px 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    display: none;
    font-family: system-ui, -apple-system, sans-serif;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.card-header h4 {
    margin: 0;
    font-size: 15px;
}

.status-pill {
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pill.empty { background: #d4f5dc; color: #1b4f1f; }
.status-pill.half  { background: #fff3c4; color: #8a6d00; }
.status-pill.full  { background: #ffd6d6; color: #8b0000; }

.card-body {
    font-size: 13px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
}

.info-row.muted {
    margin-top: 10px;
    font-size: 11px;
    color: #777;
}

/* =========================
RESPONSIVE FIXES
========================= */

/* Tablets & below */
@media (max-width: 1024px) {

    /* Main container stack */
    .page-wrapper {
        flex-direction: column;
    }

    .left-column,
    .right-column {
        flex: 1 1 100% !important;
        width: 100%;
    }

    /* Map height smaller */
    .map-container {
        height: 420px !important;
    }

    /* Asset card stack image + info */
    .asset-card {
        flex-direction: column;
    }

    .asset-image {
        width: 100% !important;
        max-width: none !important;
    }
}

/* Mobile */
@media (max-width: 768px) {

    /* BIN + DEVICES stack */
    .bin-device-wrapper {
        flex-direction: column;
    }

    .bin-card {
        flex: 1 1 100% !important;
        max-width: 100%;
    }

    /* Device grid → single column */
    .device-grid {
        grid-template-columns: 1fr !important;
    }

    /* Smaller map */
    .map-container {
        height: 320px !important;
    }

    /* Marker stays visible */
    #marker {
        width: 28px;
        height: 28px;
    }
}

/* Small phones */
@media (max-width: 480px) {

    .map-container {
        height: 260px !important;
    }

    .card-header h4 {
        font-size: 14px;
    }
}
</style>

    <div class="page-wrapper" style="max-width: 1200px; display: flex; gap: 20px;">

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


        <div class="left-column" style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Map container -->
            <div class="map-container" style="position: relative; width: 100%; height: 600px;"
                x-data="draggableMarker({{ $asset->id }}, {{ $asset->x ?? 0 }}, {{ $asset->y ?? 0 }})">

                <img src="{{ asset('uploads/floor/' . $asset->floor->picture) }}"
                    alt="Floor Map"
                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px; pointer-events: none;">

                @php
                    $maxCapacity = $asset->devices
                        ->map(fn($d) => $d->sensors->first()->capacity ?? 0)
                        ->max();

                    if ($maxCapacity >= 86) {
                        $markerColor = '#e74c3c';
                        $glowColor   = '#ff6b6b';
                    } elseif ($maxCapacity >= 41) {
                        $markerColor = '#f1c40f';
                        $glowColor   = '#ffe066';
                    } else {
                        $markerColor = '#2ecc71';
                        $glowColor   = '#6bff95';
                    }
                @endphp

                <div id="marker"
                    data-asset-id="{{ $asset->id }}"
                    data-floor-id="{{ $asset->floor_id }}"
                    title="{{ $asset->asset_name ?? 'Asset' }}"
                    style="
                        position: absolute;
                        width: 34px;
                        height: 34px;
                        left: calc({{ $asset->x }}% - 17px);
                        top: calc({{ $asset->y }}% - 17px);
                        background: #ffffff;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 
                            0 4px 12px rgba(0,0,0,0.25),
                            0 0 0 3px rgba(255,255,255,0.85);
                        cursor: pointer;
                        z-index: 10;
                    ">
                    <i class="fas fa-trash-alt"
                    style="
                            font-size: 18px;
                            color: {{ $markerColor }};
                            filter: drop-shadow(0 0 6px {{ $glowColor }});
                    ">
                    </i>
                </div>
            </div>
        </div>

        <!-- Right Column: Asset + Bin + Devices -->
        <div class="right-column" style="display: flex; flex-direction: column;">

            <!-- Section container -->
            <div style="
                background: #f8f9fb;
                border-radius: 14px;
                padding: 14px;
                display: flex;
                flex-direction: column;
                gap: 14px;
            ">

                <!-- ASSET CARD -->
                <div class="asset-card"
                    style="display: flex; gap: 16px; padding: 14px; border-radius: 14px;
                    background: #ffffff;
                    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
                    align-items: flex-start;
                ">

                    <!-- Asset Image -->
                    <div class="asset-image" style="flex: 0 0 220px;">
                        @if($asset->picture)
                            <img src="{{ asset('uploads/asset/' . $asset->picture) }}"
                                alt="Asset Picture"
                                style="
                                    width: 100%;
                                    max-height: 220px;
                                    object-fit: contain;
                                    border-radius: 10px;
                                    background: #f1f3f5;
                                    cursor: pointer;
                                "
                                onclick="window.open(this.src, '_blank')">
                        @else
                            <div style="
                                height: 220px;
                                border-radius: 10px;
                                background: #f1f3f5;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                color: #999;
                            ">
                                <i class="far fa-image" style="font-size: 32px;"></i>
                                <p style="margin: 6px 0 0;">No image</p>
                            </div>
                        @endif
                    </div>

                    <!-- Asset Info -->
                    <div style="flex: 1; position: relative;">

                        <!-- Edit asset -->
                        <div style="position: absolute; top: 0; right: 0;">
                            <x-asset.form-asset
                                :id="$asset->id"
                                :floors="$floors"
                                :picture="$asset->picture"
                                style="font-size: 11px; padding: 4px 8px;"
                            />
                        </div>

                        <div style="
                            padding-top: 26px;
                            display: grid;
                            grid-template-columns: 110px 1fr;
                            row-gap: 8px;
                            column-gap: 12px;
                            font-size: 14px;
                        ">

                            <span style="color: #6b7280;">Floor</span>
                            <span style="font-weight: 600; color: #111827;">
                                {{ $asset->floor->floor_name ?? '-' }}
                            </span>

                            <span style="color: #6b7280;">Serial No</span>
                            <span style="font-weight: 600; color: #111827;">
                                {{ $asset->serialNo ?? '-' }}
                            </span>

                            <span style="color: #6b7280;">Location</span>
                            <span style="font-weight: 600; color: #111827;">
                                {{ $asset->location ?? '-' }}
                            </span>

                            <span style="color: #6b7280;">Model</span>
                            <span style="font-weight: 600; color: #111827;">
                                {{ $asset->model ?? '-' }}
                            </span>

                        </div>
                    </div>
                </div>
                        @if($asset->devices->count() < 3)
                            <div style="text-align: right;">
                                <x-device.form-device
                                    :assets="$assets"
                                    :asset_id="$asset->id"
                                    style="font-size: 11px; padding: 4px 8px;"
                                />
                            </div>
                        @endif

                <!-- BIN + DEVICES -->
                <div class="bin-device-wrapper" style="display: flex; gap: 14px; align-items: stretch;">

                    <div class="bin-card" style="display: flex; flex-direction: column;
                        background: #ffffff;
                        border-radius: 14px;
                        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.19);
                        overflow: hidden;
                    ">

                        <!-- Card Header -->
                        <div style="
                            padding: 10px 14px;
                            font-size: 14px;
                            font-weight: 600;
                            color: #121010;
                            border-bottom: 1px solid #e5e7eb;
                            background: #fafafa;
                        ">
                            Capacity Levels
                        </div>

                        <!-- SVG Container -->
                        <div style="
                            flex: 1;
                            padding: 12px;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            background: #f7f7f7;
                        ">
                            <svg viewBox="0 0 320 240"
                                height="100%"
                                width="100%"
                                preserveAspectRatio="xMidYMid meet">

                                @foreach($compartments as $comp)
                                    <polygon
                                        class="bin-fill"
                                        points="{{ implode(' ', array_map(fn($p) => $p[0].','.$p[1], $comp['fill'])) }}"
                                        fill="{{ $comp['color'] }}"
                                        fill-opacity="0.8"
                                        stroke="none"
                                    />
                                    <text
                                        x="{{ $comp['labelPos'][0] }}"
                                        y="{{ $comp['capacityTextY'] }}"
                                        text-anchor="middle"
                                        fill="#000"
                                        font-size="13"
                                        font-weight="600"
                                        pointer-events="none"
                                    >
                                        {{ $comp['capacity'] }}%
                                    </text>
                                @endforeach

                                <!-- Compartment outlines and device names -->
                                @foreach($compartments as $comp)
                                    <polygon
                                        points="{{ implode(' ', array_map(fn($p) => $p[0].','.$p[1], $comp['outline'])) }}"
                                        fill="none"
                                        stroke="#000000"
                                        stroke-width="1"
                                    />
                                    <text
                                        x="{{ $comp['labelPos'][0] }}"
                                        y="{{ $comp['deviceNameY'] }}"
                                        text-anchor="middle"
                                        fill="#000"
                                        font-size="12"
                                        font-weight="600"
                                        pointer-events="none"
                                    >
                                        {{ $comp['label'] }}
                                    </text>
                                @endforeach

                                @if(!empty($compartments))
                                    <!-- Outer bin outline -->
                                    <polygon
                                        points="{{ implode(', ', [
                                            $compartments[0]['outline'][0][0].','.$compartments[0]['outline'][0][1],
                                            $compartments[count($compartments)-1]['outline'][1][0].','.$compartments[count($compartments)-1]['outline'][1][1],
                                            $compartments[count($compartments)-1]['outline'][2][0].','.$compartments[count($compartments)-1]['outline'][2][1],
                                            $compartments[0]['outline'][3][0].','.$compartments[0]['outline'][3][1],
                                        ]) }}"
                                        fill="none"
                                        stroke="#000000"
                                        stroke-width="3"
                                        stroke-linejoin="round"
                                    />
                                @else
                                    <!-- Draw empty placeholder bin -->
                                    <rect x="55" y="15" width="210" height="210" fill="#f0f0f0" stroke="#999" stroke-width="3" />
                                @endif
                            </svg>
                        </div>
                    </div>

                    <!-- DEVICES -->
                    <div class="device-grid" style="flex: 1; 
                        display: grid; 
                        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
                        gap: 12px;
                        align-content: start;
                    ">

                        @foreach($asset->devices as $device)
                        @php
                            $sensor = $device->sensors->sortByDesc('created_at')->first();
                        @endphp
                        @php

                        $nsrValue = $sensor?->nsr !== null ? (float) $sensor->nsr : null;

                        if ($nsrValue === null) {
                            $nsrColor = '#777';
                            $nsrTooltip = 'No signal data';
                        } elseif ($nsrValue > 20) {
                            $nsrColor = '#10b981'; // very good, green
                            $nsrTooltip = 'Maximum speed, high modulation';
                        } elseif ($nsrValue > 13) {
                            $nsrColor = '#22d3ee'; // good, cyan
                            $nsrTooltip = 'Good speed';
                        } elseif ($nsrValue > 5) {
                            $nsrColor = '#facc15'; // medium, yellow
                            $nsrTooltip = 'Normal speed, connection starting to slow';
                        } elseif ($nsrValue > 0) {
                            $nsrColor = '#f97316'; // weak, orange
                            $nsrTooltip = 'Slow, often lose connection';
                        } else {
                            $nsrColor = '#ef4444'; // very weak, red
                            $nsrTooltip = 'Almost unable to connect, many errors';
                        }

                            // Capacity value and color (for cog + trash icon)
                            $capacityValue = $sensor?->capacity ?? 0;
                            $capacityColor = $capacityValue <= $capacitySetting->empty_to ? '#1b4f1f' :
                                            ($capacityValue <= $capacitySetting->half_to ? '#f2c224' : '#e74c3c');

                            // Battery value and color
                            $batteryValue = $sensor?->battery ?? 0;
                            if ($batteryValue <= 20) {
                                $batteryColor = '#ff0000'; // red
                            } elseif ($batteryValue <= 50) {
                                $batteryColor = '#f97316'; // orange
                            } elseif ($batteryValue <= 80) {
                                $batteryColor = '#facc15'; // yellow
                            } else {
                                $batteryColor = '#10b981'; // green
                            }

                            // RSRP value and color (wifi)
                            $rsrpValue = $sensor?->rsrp !== null ? (int) $sensor->rsrp : null;
                            if ($rsrpValue === null) {
                                $wifiColor = '#777';
                                $wifiTooltip = 'No signal data';
                            } elseif ($rsrpValue >= -80) {
                                $wifiColor = '#10b981';
                                $wifiTooltip = 'Signal strong, very fast and stable';
                            } elseif ($rsrpValue > -90) {
                                $wifiColor = '#22d3ee';
                                $wifiTooltip = 'Normal, fast connection';
                            } elseif ($rsrpValue > -100) {
                                $wifiColor = '#facc15';
                                $wifiTooltip = 'Starting to slow, sometimes spotty connection';
                            } elseif ($rsrpValue > -105) {
                                $wifiColor = '#f97316';
                                $wifiTooltip = 'Slow, always buffering, very connection';
                            } else {
                                $wifiColor = '#ef4444';
                                $wifiTooltip = 'Signal is extremely weak, cannot connect';
                            }
                        @endphp
                        <div style="
                            position: relative;
                            padding: 14px;
                            border-radius: 14px;
                            background: #ffffff;
                            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
                            border-left: 5px solid {{ $capacityColor }};
                        ">

                            {{-- Top-right controls --}}
                            <div style="
                                position: absolute;
                                top: 8px;
                                right: 8px;
                                display: flex;
                                gap: 6px;
                                z-index: 5;
                            ">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-toggle="modal"
                                        data-target="#deviceModal{{ $device->id }}">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>

                                <form method="POST"
                                    action="{{ route('devices.destroy', $device->id) }}"
                                    onsubmit="return confirm('Delete this device?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" style="
                                        width: 26px;
                                        height: 26px;
                                        border-radius: 8px;
                                        border: none;
                                        background: #fee2e2;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        cursor: pointer;
                                    ">
                                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Device name --}}
                            <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #121010;">
                                {{ $device->device_name }}
                            </h3>
                            <p style="margin: 2px 0 10px; font-size: 12px; color: #777;">
                                Last updated: {{ $sensor?->created_at ?? 'N/A' }}
                            </p>
                            <div style="
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 6px;
                                font-size: 13px;
                                color: #474747;
                            ">
                                <div>
                                    <i class="fas fa-battery-full" style="color: {{ $batteryColor }};" title="Battery level"></i>
                                    <strong>{{ $sensor?->battery ?? 'N/A' }}%</strong>
                                </div>
                                <div>
                                    <i class="fas fa-trash-alt" style="color: {{ $capacityColor }};" title="Current capacity"></i>
                                    <strong>{{ $sensor?->capacity ?? 'N/A' }}%</strong>
                                </div>
                                <div>
                                    <i class="fas fa-wifi" style="color: {{ $wifiColor }};" title="{{ $wifiTooltip }}"></i>
                                    <strong>{{ $sensor?->rsrp ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <i class="fas fa-signal" style="color: {{ $nsrColor }};" title="{{ $nsrTooltip }}"></i>
                                    <strong>{{ $sensor?->nsr ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Draggable markers JS --}}
<script>
function draggableMarker(assetId, startX, startY) {
    return {
        x: startX,
        y: startY,
        init() {
            const marker = this.$el.querySelector('#marker');
            const container = this.$el;

            let offsetX = 0, offsetY = 0;

            // 🔹 Initial render using %
            marker.style.left = `calc(${this.x}% - ${marker.offsetWidth / 2}px)`;
            marker.style.top  = `calc(${this.y}% - ${marker.offsetHeight / 2}px)`;

            // 🔹 IMPORTANT for mobile
            marker.style.touchAction = 'none';

            const onMouseMove = (e) => {
                const rect = container.getBoundingClientRect();

                let newX = e.clientX - rect.left - offsetX;
                let newY = e.clientY - rect.top - offsetY;

                // Keep marker inside container
                newX = Math.max(0, Math.min(newX, rect.width - marker.offsetWidth));
                newY = Math.max(0, Math.min(newY, rect.height - marker.offsetHeight));

                // 🔹 Convert PX ➜ %
                this.x = (newX / rect.width) * 100;
                this.y = (newY / rect.height) * 100;

                marker.style.left = `calc(${this.x}% - ${marker.offsetWidth / 2}px)`;
                marker.style.top  = `calc(${this.y}% - ${marker.offsetHeight / 2}px)`;
            };

            const onMouseUp = () => {
                document.removeEventListener('pointermove', onMouseMove);
                document.removeEventListener('pointerup', onMouseUp);

                // Livewire update (store % values)
                @this.updatePosition({{ $asset->id }}, this.x, this.y);
            };

            marker.addEventListener('pointerdown', (e) => {
                e.preventDefault();

                const markerRect = marker.getBoundingClientRect();
                offsetX = e.clientX - markerRect.left;
                offsetY = e.clientY - markerRect.top;

                document.addEventListener('pointermove', onMouseMove);
                document.addEventListener('pointerup', onMouseUp);
            });
        }
    }
}
</script>
<script>
function openHelp() {
    $('#helpModal').modal('show');
}
</script>



    {{-- Device Modals moved to bottom for proper Bootstrap/Livewire functionality --}}
@foreach($asset->devices as $device)
<div class="modal fade" id="deviceModal{{ $device->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('devices.update', $device->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Device</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    {{-- Device ID --}}
                    <div class="form-group">
                        <label>Device ID</label>
                        <input type="text"
                               class="form-control"
                               name="id_device"
                               value="{{ $device->id_device }}"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Device Name</label>
                        <input type="text"
                               class="form-control"
                               name="device_name"
                               value="{{ $device->device_name }}">
                    </div>
                    <!-- Add other device fields here if needed -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- help modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Asset Monitoring – User Guide</h5>
      </div>
        <div class="modal-body" style="font-size: 14px; line-height: 1.6;">
        <!-- Asset Location -->
        <h6 class="fw-bold mb-2">
            <i class="fas fa-map-marked-alt"></i> Asset Location on Map
        </h6>
        <ol class="mb-3">
            <li>
            Drag the <i class="fas fa-trash text-success"></i> marker to set the asset’s location on the map.
            </li>
            <li>
            The location can be updated or changed at any time.
            </li>
        </ol>

        <!-- Asset Info -->
        <h6 class="fw-bold mb-2">
            <i class="fas fa-info-circle"></i> Asset Info
        </h6>
        <ol class="mb-3">
            <li>
            Click the <i class="fas fa-pencil-alt"></i> button to edit the asset’s information.
            </li>
            <li>
            Asset details such as name, description, and picture can be updated.
            </li>
        </ol>

        <!-- Capacity / Sensor Details -->
        <h6 class="fw-bold mb-2">
            <i class="fas fa-clipboard"></i> Capacity Levels / Sensor Details
        </h6>
        <ol class="mb-3">
            <li>
            Each compartment shown in the image represents one sensor installed in the bin.
            </li>
            <li>
            Sensor color indicators:
            <ul class="mt-1">
                <li><span style="color:#e74c3c;">Red</span> – Full</li>
                <li><span style="color:#f1c40f;">Yellow</span> – Half Full</li>
                <li><span style="color:#2ecc71;">Green</span> – Empty</li>
            </ul>
            </li>
            <li>
            Click the <i class="fas fa-pencil-alt"></i> button to edit a sensor’s properties.
            </li>
            <li>
            Click the <i class="fas fa-plus"></i> button to add a new sensor device.
            </li>
        </ol>

        <!-- Note -->
        <div class="alert alert-info py-2 mb-0">
            <strong>Note:</strong> The add (<i class="fas fa-plus"></i>) button is only available if fewer than
            <strong>three</strong> sensor devices are attached to the asset.
        </div>

        </div>
    </div>
  </div>
</div>

@endforeach

</div> {{-- Single Livewire root wrapper end --}}

