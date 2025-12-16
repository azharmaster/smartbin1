<!-- @extends('layouts.nosidebaradmin') -->
@section('content_title', 'Bin Details')
@section('content')

<div class="bin-details-container">

    {{-- Asset Info --}}
    <div class="card asset-card mb-4 text-dark">
        <div class="card-header asset-card-header">
            <h4 class="card-title">Asset Information: {{ $asset->asset_name ?? '-' }}</h4>
        </div>
        <div class="card-body asset-card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> {{ $asset->asset_name ?? '-' }}</p>
                    <p><strong>Floor:</strong> {{ $asset->floor->floor_name ?? '-' }}</p>
                    <p><strong>Serial No:</strong> {{ $asset->serialNo ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Description:</strong> {{ $asset->description ?? '-' }}</p>
                    <p><strong>Model:</strong> {{ $asset->model ?? '-' }}</p>
                    <p><strong>Maintenance:</strong> {{ $asset->maintenance ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Devices --}}
    <div class="devices-wrapper row">
        @forelse($devices as $device)
        @php
            $capacity = $device->latestSensor->capacity ?? 0;
            if($capacity > 85){
                $statusClass = 'full';
                $statusText = 'FULL';
            } elseif($capacity > 40){
                $statusClass = 'half';
                $statusText = 'HALF';
            } else {
                $statusClass = 'empty';
                $statusText = 'EMPTY';
            }
        @endphp
        <div class="col-md-4 mb-3 text-dark">
            <div class="device-card {{ $statusClass }}">
                <div class="device-header">
                    <h5 class="device-name">{{ $device->device_name ?? '-' }}</h5>
                    <span class="badge">{{ $statusText }}</span>
                </div>
                <p><strong>Capacity:</strong> {{ $capacity }}%</p>
                <p><strong>Battery:</strong> {{ $device->latestSensor->battery ?? '-' }}%</p>
                <p><strong>Floor:</strong> {{ $device->asset->floor->floor_name ?? '-' }}</p>
            </div>
        </div>
        @empty
        <div class="col-12">
            <p>No devices found.</p>
        </div>
        @endforelse
    </div>

</div>

@endsection

@section('styles')
<style>
/* Container */
.bin-details-container {
    padding: 20px;
}

/* Asset Card */
.asset-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.asset-card-header {
    background-color: #d71414ff;
    color: #fff;
    font-weight: 600;
}
.asset-card-body p {
    margin: 5px 0;
}

/* Devices Cards */
.device-card {
    border-radius: 12px;
    padding: 15px;
    min-height: 160px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    background-color: #f8f9fa; /* light gray background for readability */
    color: #212529; /* dark text */
}
.device-card .device-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.device-card .device-name {
    font-weight: 600;
}
.device-card .badge {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 5px 8px;
    border-radius: 5px;
    color: #fff;
}

/* Status Colors for badge */
.device-card.full .badge {
    background-color: #d71414; /* red */
}
.device-card.half .badge {
    background-color: #f5a623; /* orange */
}
.device-card.empty .badge {
    background-color: #7a7a7a; /* gray */
}

.device-card p {
    margin: 3px 0;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .device-card {
        min-height: 140px;
    }
}
</style>
@endsection
