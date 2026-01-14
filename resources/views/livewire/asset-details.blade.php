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

    <!-- Asset Image Card (NOW truly beneath map) -->
    <div style="padding: 12px;
                border: 1px solid #ccc;
                border-radius: 10px;
                background-color: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                text-align: center;">

        <h3 style="font-weight: 600; font-size: 16px; margin-bottom: 10px; color: #34495e;">
            Asset Image
        </h3>

        @if($asset->picture)
            <img src="{{ asset('storage/' . $asset->picture) }}"
                 alt="Asset Picture"
                 style="width: 100%;
                        max-height: 220px;
                        object-fit: contain;
                        border-radius: 8px;
                        cursor: pointer;"
                 onclick="window.open(this.src, '_blank')">
        @else
            <div style="padding: 30px; color: #999;">
                <i class="far fa-image" style="font-size: 32px; margin-bottom: 8px;"></i>
                <p style="margin: 0;">No image uploaded</p>
            </div>
        @endif
    </div>

</div>

<!-- Right Column: Asset + Bin + Devices -->
<div style="flex: 1; display: flex; flex-direction: column;">

    <!-- Entire right section in a card -->
    <div style=" border-radius: 12px; background: #00000000; display: flex; flex-direction: column; gap: 12px;">

        <!-- Asset Info Card -->
        <div style="padding: 12px; border: 1px solid #362e2e; border-radius: 10px;">
            <p style="margin: 2px 0;"><strong>Floor:</strong> {{ $asset->floor->floor_name ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Serial No:</strong> {{ $asset->serialNo ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Location:</strong> {{ $asset->location ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Model:</strong> {{ $asset->model ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Date Added:</strong> {{ $asset->created_at?->format('Y-m-d') ?? '-' }}</p>
        </div>

        <!-- Bin + Device Cards -->
        <div style="display: flex; gap: 12px; align-items: stretch;">

            <!-- Bin -->
            <div style="flex: 0 0 320px; display: flex; justify-content: center; align-items: center; background: #dcdbdb; border-radius: 10px; padding: 12px;">
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
            <!-- Device cards -->
            <div style="
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 8px;
                flex: 1; /* take remaining space */
                align-content: start; /* remove extra vertical gap */
            ">
            @php
                $deviceCount = $asset->devices->count();
            @endphp

            @if($deviceCount < 3)
                <div class="ms-auto">
                    <x-device.form-device :assets="$assets" :asset_id="$asset->id" />
                </div>
            @endif
                @foreach($asset->devices as $device)
@php
    $sensor = $device->sensors->sortByDesc('time')->first();
@endphp

<div style="
    position: relative; /* for positioning the edit button */
    padding: 14px;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    border-left: 5px solid
        {{ ($sensor?->capacity ?? 0) <= $capacitySetting->empty_to ? '#1b4f1f' :
           (($sensor?->capacity ?? 0) <= $capacitySetting->half_to ? '#f2c224' : '#e74c3c') }};
">

    <!-- Use the form-device component's button but style it tiny -->
    <x-device.form-device 
        :id="$device->id"
        :assets="$assets"
        :device_name="$device->device_name"
        :asset_id="$asset->id"
        :id_device="$device->id_device"
        class="edit-device-button" />

    <!-- Header -->
    <div style="margin-bottom: 8px;">
        <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #2c3e50;">
            {{ $device->device_name }}
        </h3>
        <span style="font-size: 12px; color: #777;">
            Last updated: {{ $sensor?->time ?? 'N/A' }}
        </span>
    </div>

    <!-- Info rows -->
    <div style="
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        font-size: 14px;
        color: #000;
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