<a href="javascript:void(0)"
   class="text-decoration-none device-link"
   onclick="openAssetDetails('{{ route('master-data.assets.details', $asset->id) }}')">

    <div class="device-card asset-device-card">

<div class="asset-card card mb-3 p-3" style="background-color: #2a2a2a;">
    {{-- Asset header --}}
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="fw-bold fs-5 text-white">
            {{ $asset->asset_name }}
        </div>

        <span class="badge bg-dark">
            {{ $asset->devices->count() }} Sensors
        </span>
    </div>

    <div class="text-white small mb-2">
        <i class="fas fa-map-marker-alt"></i>
        {{ $asset->floor->floor_name ?? 'Unknown' }}
    </div>
</div>

        {{-- SENSORS LIST --}}
        <div class="mt-2 d-flex flex-column gap-2">

@foreach($assetsWithDevices as $asset)
    <div class="device-card asset-card">
        <div class="fw-bold text-dark">{{ $asset->asset_name }}</div>

        @foreach($asset->devices as $device)
            <div class="mt-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="progress flex-grow-1 me-2" style="height: 12px; border-radius: 999px;">
                        <div class="progress-bar {{ $device->barClass }}"
                             style="width: {{ $device->latestSensor->capacity ?? 0 }}%;"></div>
                    </div>
                    <div class="text-white small fw-bold ms-2">
                        {{ $device->device_name }}: {{ $device->latestSensor->capacity ?? 0 }}%
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

        </div>

    </div>
</a>
