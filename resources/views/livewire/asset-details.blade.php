<style>
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
        padding: 14px;
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
</style>
<div style="max-width: 1200px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; gap: 20px;">

<div style="flex: 0 0 50%; display: flex; flex-direction: column; gap: 16px;">

    <!-- Map container -->
    <div style="position: relative; width: 100%; height: 600px;"
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
             style="position: absolute;
                    width: 24px; height: 24px;
                    left: calc({{ $asset->x }}px - 18px);
                    top: calc({{ $asset->y }}px);">
            <i class="fas fa-trash-alt"
               style="font-size: 22px; color: {{ $markerColor }};
                      filter: drop-shadow(0 0 6px {{ $glowColor }});"></i>
        </div>
    </div>
</div>

<!-- Right Column: Asset + Bin + Devices -->
<div style="flex: 1; display: flex; flex-direction: column;">

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
        <div style="
            display: flex;
            gap: 16px;
            padding: 14px;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            align-items: flex-start;
        ">

            <!-- Asset Image -->
            <div style="flex: 0 0 220px;">
                @if($asset->picture)
                    <img src="{{ asset('storage/' . $asset->picture) }}"
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

        <!-- BIN + DEVICES -->
        <div style="display: flex; gap: 14px; align-items: stretch;">

            <!-- BIN CARD -->
            <div style="
                flex: 0 0 320px;
                background: #f7f7f7;
                border-radius: 14px;
                padding: 12px;
                display: flex;
                justify-content: center;
                align-items: center;
                box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            ">
                <svg viewBox="0 0 320 240" width="100%" height="auto" preserveAspectRatio="xMidYMid meet">
                    <!-- ... your SVG polygons and texts here ... -->
                    <!-- Partial fills -->
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

                    <!-- Outer bin outline -->
                    <polygon
                        points="{{ implode(' ', [
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
                </svg>
            </div>

            <!-- DEVICES -->
            <div style="
                flex: 1;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 12px;
                align-content: start;
            ">

                @if($asset->devices->count() < 3)
                    <div style="text-align: right;">
                        <x-device.form-device
                            :assets="$assets"
                            :asset_id="$asset->id"
                            style="font-size: 11px; padding: 4px 8px;"
                        />
                    </div>
                @endif

                @foreach($asset->devices as $device)
                @php
                    $sensor = $device->sensors->sortByDesc('time')->first();
  
                    $capacity = $sensor?->capacity ?? 0;

                    if ($capacity <= $capacitySetting->empty_to) {
                        $statusBorder = '#1b4f1f';
                        $statusBg = 'rgba(27, 79, 31, 0.3)'; // green tint
                    } elseif ($capacity <= $capacitySetting->half_to) {
                        $statusBorder = '#f2c224';
                        $statusBg = 'rgba(242, 194, 36, 0.08)'; // yellow tint
                    } else {
                        $statusBorder = '#e74c3c';
                        $statusBg = 'rgba(231, 77, 60, 0.26)'; // red tint
                    }
                @endphp

                <div style="
                    position: relative;
                    padding: 14px;
                    border-radius: 14px;
                    background:
                        linear-gradient(
                            to right,
                            {{ $statusBg }},
                            #ffffff 35%
                        );
                    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
                    border-left: 5px solid {{ $statusBorder }};
                    transition: transform 0.15s ease, box-shadow 0.15s ease;
                ">

                    <!-- Edit device -->
                    <div style="position: absolute; top: 0px; right: 10px;">
                        <x-device.form-device
                            :id="$device->id"
                            :assets="$assets"
                            :device_name="$device->device_name"
                            :asset_id="$asset->id"
                            :id_device="$device->id_device"
                            style="font-size: 10px;"
                        />
                    </div>

                    <h3 style="margin: 0; font-size: 15px; font-weight: 600;">
                        {{ $device->device_name }}
                    </h3>
                    <p style="margin: 2px 0 10px; font-size: 12px; color: #777;">
                        Last updated: {{ $sensor?->time ?? 'N/A' }}
                    </p>

                    <div style="
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 6px;
                        font-size: 13px;
                    ">
                        <div>🔋 <strong>{{ $sensor?->battery ?? 'N/A' }}%</strong></div>
                        <div>🗑️ <strong>{{ $sensor?->capacity ?? 'N/A' }}%</strong></div>
                        <div>📶 <strong>{{ $sensor?->network ?? 'N/A' }}</strong></div>
                        <div>⚙️ <strong>
                            {{ ($sensor?->capacity ?? 0) <= $capacitySetting->empty_to ? 'Empty' :
                            (($sensor?->capacity ?? 0) <= $capacitySetting->half_to ? 'Half' : 'Full') }}
                        </strong></div>
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

            const onMouseMove = (e) => {
                let newX = e.clientX - container.getBoundingClientRect().left - offsetX;
                let newY = e.clientY - container.getBoundingClientRect().top - offsetY;

                // Keep marker inside container
                newX = Math.max(0, Math.min(newX, container.offsetWidth - marker.offsetWidth));
                newY = Math.max(0, Math.min(newY, container.offsetHeight - marker.offsetHeight));

                this.x = newX;
                this.y = newY;

                marker.style.left = `${this.x}px`;
                marker.style.top = `${this.y}px`;
            };

            const onMouseUp = () => {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                // Livewire update
                @this.updatePosition({{ $asset->id }}, this.x, this.y);
            };

            marker.addEventListener('mousedown', (e) => {
                e.preventDefault();
                offsetX = e.clientX - marker.getBoundingClientRect().left;
                offsetY = e.clientY - marker.getBoundingClientRect().top;

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        }
    }
}

</script>