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

    <!-- Right Column: Asset & Device Details -->
    <div style="flex: 1; display: flex; flex-direction: column; gap: 16px;">

        <!-- Asset Card -->
        <div style="padding: 12px; border: 1px solid #ccc; border-radius: 10px; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); color: #000000ff"">
            <h2 style="font-weight: 700; font-size: 18px; margin-bottom: 6px; color: #2c3e50;">{{ $asset->asset_name }}</h2>
            <p style="margin: 2px 0;"><strong>Floor:</strong> {{ $asset->floor->floor_name ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Serial No:</strong> {{ $asset->serialNo ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Location:</strong> {{ $asset->location ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Model:</strong> {{ $asset->model ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Maintenance:</strong> {{ $asset->maintenance ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Date Added:</strong> {{ $asset->created_at?->format('Y-m-d') ?? '-' }}</p>
        </div>

@php
use App\Models\CapacitySetting;

// Get capacity settings (assumes only one row)
$capacitySetting = CapacitySetting::first();

// Helper function for color coding
function capacityColor($capacityPercent, $setting) {
    if ($capacityPercent <= $setting->empty_to) return 'green';
    if ($capacityPercent <= $setting->half_to)  return 'yellow';
    return 'red';
}

function fillRatioFromCategory($category) {
    return match ($category) {
        'empty' => 0.15,   // slight visible fill
        'half'  => 0.55,
        'full'  => 0.95,
    };
}

function capacityCategory($capacity, $setting) {
    if ($capacity <= $setting->empty_to) return 'empty';
    if ($capacity <= $setting->half_to)  return 'half';
    return 'full';
}


// Bin corners
$topLeft = [50, 30];
$topRight = [305, 20];
$bottomLeft = [70, 210];
$bottomRight = [300, 210];

// Prepare devices safely
$devices = $asset->devices ?? collect();
$devices = $asset->devices->map(function ($device) {
    $sensor = $device->sensors
        ->sortByDesc('time')
        ->first();

    return [
        'name'     => $device->device_name,
        'capacity' => $sensor?->capacity ?? 0,
        'battery'  => $sensor?->battery,
    ];
});

// Number of compartments = number of devices
$n = $devices->count();

// Linear interpolation helper
function lerp($a, $b, $t) { return $a + ($b - $a) * $t; }

// Build compartment polygons and partial fills
$compartments = [];
for ($i = 0; $i < $n; $i++) {
    $t0 = $i / $n;
    $t1 = ($i + 1) / $n;

    // Compartment corners
    $topCompLeft  = [lerp($topLeft[0], $topRight[0], $t0), lerp($topLeft[1], $topRight[1], $t0)];
    $topCompRight = [lerp($topLeft[0], $topRight[0], $t1), lerp($topLeft[1], $topRight[1], $t1)];
    $bottomCompLeft  = [lerp($bottomLeft[0], $bottomRight[0], $t0), lerp($bottomLeft[1], $bottomRight[1], $t0)];
    $bottomCompRight = [lerp($bottomLeft[0], $bottomRight[0], $t1), lerp($bottomLeft[1], $bottomRight[1], $t1)];

    $device = $devices[$i];

    // capacity %
    $capacityValue = min(100, max(0, $device['capacity']));

    $capacityPercent = min(100, max(0, $device['capacity']));
    $fillRatio = $capacityPercent / 100;

    $color = capacityColor($capacityPercent, $capacitySetting);

    // Interpolate top line for partial fill
    $fillTopLeft = [
        lerp($bottomCompLeft[0], $topCompLeft[0], $fillRatio),
        lerp($bottomCompLeft[1], $topCompLeft[1], $fillRatio),
    ];

    $fillTopRight = [
        lerp($bottomCompRight[0], $topCompRight[0], $fillRatio),
        lerp($bottomCompRight[1], $topCompRight[1], $fillRatio),
    ];

    $compartments[] = [
        'outline' => [$topCompLeft, $topCompRight, $bottomCompRight, $bottomCompLeft],
        'fill'    => [$fillTopLeft, $fillTopRight, $bottomCompRight, $bottomCompLeft],
        'color'   => $color,
    ];
}
@endphp

<!-- Smart Bin Visualization -->
<div style="
    padding: 16px;
    border: 1px solid #ccc;
    border-radius: 10px;
    background: #111;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    justify-content: center;
">
@if($n > 0)
<svg viewBox="0 0 320 240" width="300">
    <!-- Partial fills -->
    @foreach($compartments as $comp)
        <polygon
            points="{{ implode(' ', array_map(fn($p) => $p[0].','.$p[1], $comp['fill'])) }}"
            fill="{{ $comp['color'] }}"
            fill-opacity="0.6"
            stroke="none"
        />
    @endforeach

    <!-- Compartment outlines -->
    @foreach($compartments as $comp)
        <polygon
            points="{{ implode(' ', array_map(fn($p) => $p[0].','.$p[1], $comp['outline'])) }}"
            fill="none"
            stroke="#ffffff"
            stroke-width="1"
        />
    @endforeach

    <!-- Outer bin outline -->
    <polygon
        points="{{ $topLeft[0] }},{{ $topLeft[1] }} {{ $topRight[0] }},{{ $topRight[1] }} {{ $bottomRight[0] }},{{ $bottomRight[1] }} {{ $bottomLeft[0] }},{{ $bottomLeft[1] }}"
        fill="none"
        stroke="#ffffff"
        stroke-width="3"
        stroke-linejoin="round"
    />
</svg>
@else
<p style="color: #fff;">No devices found for this asset.</p>
@endif
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