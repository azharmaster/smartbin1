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
            <p style="margin: 2px 0;"><strong>Description:</strong> {{ $asset->description ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Model:</strong> {{ $asset->model ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Maintenance:</strong> {{ $asset->maintenance ?? '-' }}</p>
            <p style="margin: 2px 0;"><strong>Date Added:</strong> {{ $asset->created_at?->format('Y-m-d') ?? '-' }}</p>
        </div>

        <!-- Devices / Sensors Cards -->
        @foreach($asset->devices as $device)
        <div style="padding: 12px; border: 1px solid #ccc; border-radius: 10px; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <h3 style="font-weight: 600; font-size: 16px; margin-bottom: 6px; color: #34495e;">{{ $device->device_name }}</h3>
            <ul style="list-style: none; padding-left: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; color: #000000ff">
                <li><i class="fas fa-battery-three-quarters" style="color:#f39c12;"></i> {{ $device->sensors->first()->battery ?? 'N/A' }}%</li>
                <li><i class="fas fa-trash" style="color:#e74c3c;"></i> {{ $device->sensors->first()->capacity ?? 'N/A' }}%</li>
                <li><i class="fas fa-history" style="color:#3498db;"></i> {{ $device->sensors->first()->time ?? 'N/A' }}</li>
                <li><i class="fas fa-signal" style="color:#2ecc71;"></i> {{ $device->sensors->first()->network ?? 'N/A' }}</li>
            </ul>
        </div>
        @endforeach

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