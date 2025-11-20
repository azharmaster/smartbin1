<div style="max-width: 1200px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; gap: 20px;">

    <!-- Left Column: Map Card -->
    <div style="flex: 1; min-width: 400px;">
        <div style="border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="floor-map-container" style="position: relative; width: 100%; height: 600px;">
                <img src="{{ asset('floor_pictures/' . $asset->floor->picture) }}" 
                    alt="Floor Map"
                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px; pointer-events: none;"> <!-- disable mouse on image -->
                
                <!-- Marker -->
                <div id="marker"
                    style="
                        position: absolute;
                        width: 24px;
                        height: 24px;
                        background-color: red;
                        border-radius: 50%;
                        cursor: grab;
                        left: {{ $asset->x ?? 0 }}px;
                        top: {{ $asset->y ?? 0 }}px;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    ">
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Asset & Device Details -->
    <div style="flex: 1; display: flex; flex-direction: column; gap: 16px;">

        <!-- Asset Card -->
        <div style="padding: 12px; border: 1px solid #ccc; border-radius: 10px; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
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
            <ul style="list-style: none; padding-left: 0; margin: 0; display: flex; flex-direction: column; gap: 6px;">
                <li><i class="fas fa-battery-three-quarters" style="color:#f39c12;"></i> {{ $device->sensors->first()->battery ?? '-' }}%</li>
                <li><i class="fas fa-trash" style="color:#e74c3c;"></i> {{ $device->sensors->first()->capacity ?? '-' }}%</li>
                <li><i class="fas fa-history" style="color:#3498db;"></i> {{ $device->sensors->first()->time ?? '-' }}</li>
                <li><i class="fas fa-signal" style="color:#2ecc71;"></i> {{ $device->sensors->first()->network ?? '-' }}</li>
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

            let offsetX, offsetY;

            const onMouseMove = e => {
                let newX = e.clientX - container.getBoundingClientRect().left - offsetX;
                let newY = e.clientY - container.getBoundingClientRect().top - offsetY;

                // Keep marker inside container
                newX = Math.max(0, Math.min(newX, container.offsetWidth - marker.offsetWidth));
                newY = Math.max(0, Math.min(newY, container.offsetHeight - marker.offsetHeight));

                this.x = newX;
                this.y = newY;
            };

            const onMouseUp = () => {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                // Update database via Livewire
                @this.updatePosition(this.x, this.y);
            };

            marker.addEventListener('mousedown', e => {
                e.preventDefault();
                offsetX = e.clientX - marker.getBoundingClientRect().left + container.getBoundingClientRect().left;
                offsetY = e.clientY - marker.getBoundingClientRect().top + container.getBoundingClientRect().top;

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        }
    }
}
</script>