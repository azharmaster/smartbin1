<a href="javascript:void(0)"
   class="text-decoration-none device-link"
   onclick="openAssetDetails('{{ route('master-data.assets.details', ['asset' => $device->asset->id]) }}')">

    <div class="device-card {{ $cardClass }}" data-status="{{ $status }}">
        <div class="d-flex justify-content-between align-items-start">
            <div class="fw-bold fs-4 text-white">
                {{ $device->device_name }}
            </div>
            <div class="badge {{ $badgeClass }}">
                {{ $badge }}
            </div>
        </div>

        <div class="mt-1 text-white">
            <i class="fas fa-map-marker-alt"></i>
            {{ $device->asset->floor->floor_name ?? 'Unknown' }}
        </div>

<div class="mt-2">
    <!-- Progress bar with capacity next to it -->
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="progress flex-grow-1 me-2" style="height: 12px; border-radius: 999px;">
            <div class="progress-bar {{ $barClass }}"
                 style="width: {{ $device->latestSensor->capacity ?? 0 }}%;">
            </div>
        </div>
        <div class="text-white small fw-bold" style="padding-left: 5px;">
            {{ $device->latestSensor->capacity ?? 0 }}%
        </div>
    </div>
</div>

    </div>
</a>
