@extends('layouts.nosidebaradmin')



@section('content')

{{-- back button --}}
<div class="container-fluid mt-3">
    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>&nbsp;

<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 20px;
}

.status-card {
    border-radius: 14px;
    padding: 20px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 130px;
    transition: 0.2s ease-in-out;
}

.status-card:hover {
    box-shadow: 0 5px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.status-title {
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 10px;
    line-height: 1.3;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.status-content {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.status-icon {
    font-size: 32px;
}

.status-number {
    font-size: 32px;
    font-weight: bold;
}

.card-total { background-color: rgba(255, 255, 255, 0.1); }
.card-full { background-color: rgba(255, 255, 255, 0.1); }
.card-half { background-color: rgba(255, 255, 255, 0.1); }
.card-empty { background-color: rgba(255, 255, 255, 0.1); }
.card-undetected { background-color: rgba(255, 255, 255, 0.1); }
.card-primary { background-color: rgba(255, 255, 255, 0.1); }

/* Container: tighter spacing */
.full-devices-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 14px; /* smaller gap */
    margin-left: 5px;
}

/* FULL DEVICE CARD */
.full-device-card {
    background-color: #6f060687;
    border: 2px solid #ff4d4d;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(255, 0, 0, 0.5);
    transition: transform 0.2s, box-shadow 0.2s;
}

.full-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 18px rgba(255, 0, 0, 0.7);
}

/* FULL STATUS – Pulsing badge */
.full-status {
    background-color: #FF0000;
    padding: 0.4em 0.9em;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 0 8px #FF0000, 0 0 12px #FF4d4d, 0 0 18px #FF6666;
    animation: pulse 1.5s infinite;
}

/* PULSE ANIMATION */
@keyframes pulse {
    0% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
    50% { box-shadow: 0 0 10px #CC0000, 0 0 14px #D93333, 0 0 20px #E06666; transform: scale(1.05); }
    100% { box-shadow: 0 0 6px #CC0000, 0 0 10px #D93333, 0 0 14px #E06666; transform: scale(1); }
}

/* HALF DEVICE CARD (similar style but yellow, no pulse) */
.half-device-card {
    background-color: #8f6f0587; /* translucent yellow-brown */
    border: 2px solid #f7d24a;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(255, 208, 0, 0.45);
    transition: transform 0.2s, box-shadow 0.2s;
}

.half-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 14px rgba(255, 208, 0, 0.6);
}

/* HALF STATUS – No pulse */
.half-status {
    background-color: #FFD700;
    padding: 0.4em 0.9em;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 0 8px #FFD70090;
}

/* HALF DEVICE CARD (similar style but yellow, no pulse) */
.empty-device-card {
    background-color: #4cd9633a; /* translucent yellow-brown */
    border: 2px solid #4ab65eff;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.empty-device-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 14px rgba(41, 56, 43, 0.2);
}

/* EMPTY STATUS – No pulse */
.empty-status {
    background-color: #4cd964;
    padding: 0.4em 0.9em;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 0 8px #4cd96381;
}

/* Title size */
.full-device-card .fw-bold.fs-4,
.half-device-card .fw-bold.fs-4, 
.empty-device-card .fw-bold.fs-4, {
    font-size: 1.3rem;
    font-weight: bold;
}


.floor-frame {
    margin-top: 40px;
    width: 600px;
    max-width: 100%;
    padding: 20px;
    border-radius: 12px;
    border: 2px solid #ddd;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    background-color: #fff;
}

.floor-frame select {
    width: 100%;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}

.floor-frame img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #aaa;
    transition: transform 0.2s, box-shadow 0.2s;
}

.floor-frame img:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.bins-container {
    margin-top: 40px;
    width: 100%;
    max-width: 100%;
    border: 2px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    background-color: #fff;
    padding: 20px;
}

.bins-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.bins-header h2 {
    font-size: 1.4rem;
}

.bin-count {
    font-weight: bold;
}
/*
.collapse-btn {
    border: none;
    background: transparent;
    cursor: pointer;
}
*/
.bins-list {
    max-height: 600px;
    overflow-y: auto;
}

.bin-card {
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #f5f5f5;
}

.bin-card.warning { background-color: #f8d7da; }
.bin-card.normal { background-color: #d4edda; }

.bin-progress {
    width: 100%;
    background-color: #e9ecef;
    height: 10px;
    border-radius: 6px;
    margin-top: 5px;
}

.bin-progress-bar {
    height: 10px;
    border-radius: 6px;
}

.progress-warning { background-color: #e74c3c; }
.progress-normal { background-color: #7ccc63; }

.bin-details {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
}

#mapAndList {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 25px;
    margin-top: 40px;
}

.floor-frame {
    width: 45%;
}

.bins-container {
    width: 55%;
}

.map-card-body {
    overflow: hidden;
    height: 650px; /* initial height */
    transition: height 0.3s ease; /* smooth animation */
}

.devices-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* ✅ 4 in a row */
    gap: 16px;
}

/* shared card sizing */
.device-card {
    padding: 16px;
    border-radius: 12px;
    min-height: 130px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* hover effect */
.device-link:hover .device-card {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
}

@media (max-width: 1200px) {
    .devices-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .devices-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .devices-grid {
        grid-template-columns: 1fr;
    }
}

#deviceFilter {
    border-radius: 10px;
    min-width: 100px;
    min-height: 10px;
}

#deviceFilter:focus {
    box-shadow: 0 0 0 0.15rem rgba(108, 117, 125, 0.25); /* subtle */
}

/* Organized Bins Grid */
.bins-organized-grid {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.bin-section {
    transition: all 0.3s ease;
}

.bin-section-header {
    transition: all 0.2s ease;
}

.bin-section-header:hover {
    filter: brightness(1.1);
}

.bin-section-header .badge {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.compartment-card {
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.compartment-card:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.badge-sm {
    font-size: 0.65rem;
    padding: 0.25em 0.5em;
}

/* Filter visibility */
.bin-section[data-bin-status="full"] {
    display: block;
}

.bin-section[data-bin-status="half"] {
    display: block;
}

.bin-section[data-bin-status="empty"] {
    display: block;
}

.bin-section[data-bin-status="undetected"] {
    display: block;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if($lastUpdated)
    <div class="mb-2 text-muted text-right" style="font-size: 13px;">
        Last Updated:
        <strong>{{ \Carbon\Carbon::parse($lastUpdated)->format('d M Y, h:i A') }}</strong>
    </div>
@endif

<div class="dashboard-cards mb-4">
    @php
        $statuses = [
            ['title' => 'Total Sensors', 'count' => $totalDevices, 'icon' => 'fa-satellite-dish', 'class' => 'card-total'],
            ['title' => 'Full Sensors', 'count' => $fullDevices, 'icon' => 'fa-trash', 'class' => 'card-full'],
            ['title' => 'Half-Full Sensors', 'count' => $halfDevices, 'icon' => 'fa-exclamation-triangle', 'class' => 'card-half'],
            ['title' => 'Empty Sensors', 'count' => $emptyDevices, 'icon' => 'fa-recycle', 'class' => 'card-empty'],
            ['title' => 'Undetected', 'count' => $undetectedDevices, 'icon' => 'fa-minus-circle', 'class' => 'card-undetected'],
        ];
    @endphp

    @foreach ($statuses as $status)
        <div class="status-card {{ $status['class'] }}" style="color: #4cd964;">
            <div class="status-title">{{ $status['title'] }}</div>
            <div class="status-content" style="color: #fff">
                <i class="fas {{ $status['icon'] }} status-icon"></i>
                <span class="status-number">{{ $status['count'] }}</span>
            </div>
        </div>
    @endforeach
</div>

<div id="static-device-grid">
    <div class="card mb-4" style="background-color: transparent;">
        <div class="p-3 scroll-body" style="overflow-y: auto;">

            {{-- FILTER DROPDOWN --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-warehouse"></i> Bins by Compartment</h5>
                <select id="deviceFilter" class="form-select form-select-md w-auto px-2">
                    <option value="all">All Bins</option>
                    <option value="critical">Critical First</option>
                    <option value="full">Full Only</option>
                    <option value="half">Half Full Only</option>
                    <option value="empty">Empty Only</option>
                </select>
            </div>

            {{-- ORGANIZED BIN SECTIONS --}}
            <div class="bins-organized-grid">
                @forelse($groupedDevices as $binName => $compartments)
                    @php
                        // Determine overall bin status
                        $hasFull = false;
                        $hasHalf = false;
                        $hasEmpty = false;
                        $hasUndetected = false;
                        
                        foreach($compartments as $compartment => $devices) {
                            foreach($devices as $device) {
                                $status = null;
                                if (!$device->latestSensor || !is_numeric($device->latestSensor->capacity)) {
                                    $hasUndetected = true;
                                } elseif ($device->latestSensor->capacity > $device->asset->capacitySetting->half_to) {
                                    $hasFull = true;
                                } elseif ($device->latestSensor->capacity > $device->asset->capacitySetting->empty_to) {
                                    $hasHalf = true;
                                } else {
                                    $hasEmpty = true;
                                }
                            }
                        }
                        
                        if ($hasFull) $binStatus = 'full';
                        elseif ($hasHalf) $binStatus = 'half';
                        elseif ($hasUndetected) $binStatus = 'undetected';
                        else $binStatus = 'empty';
                        
                        $binClass = $binStatus === 'full' ? 'border-danger' : ($binStatus === 'half' ? 'border-warning' : 'border-success');
                        $firstDevice = $compartments->flatten()->first();
                        $floorName = $firstDevice?->asset?->floor?->floor_name ?? 'Unknown Floor';
                    @endphp
                    
                    <div class="bin-section mb-3" data-bin-status="{{ $binStatus }}">
                        <!-- Bin Header -->
                        <div class="bin-section-header p-3 rounded-top {{ $binClass }}" 
                             style="background: linear-gradient(135deg, {{ $binStatus === 'full' ? '#dc3545' : ($binStatus === 'half' ? '#ffc107' : '#28a745') }}, #fff); 
                                    color: {{ $binStatus === 'half' ? '#000' : '#fff' }};
                                    cursor: pointer;"
                             data-bs-toggle="collapse"
                             data-bs-target="#binSection{{ str_replace(' ', '', $binName) }}"
                             role="button">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-trash-alt"></i> {{ $binName }}
                                    </h6>
                                    <small style="opacity: 0.9;">
                                        <i class="fas fa-map-marker-alt"></i> {{ $floorName }}
                                        <span class="ms-2">
                                            <i class="fas fa-microchip"></i> {{ $compartments->flatten()->count() }} compartments
                                        </span>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $binStatus === 'full' ? 'bg-danger' : ($binStatus === 'half' ? 'bg-warning text-dark' : 'bg-success') }} px-3 py-2">
                                        @if($binStatus === 'full')
                                            <i class="fas fa-exclamation-triangle"></i> CRITICAL
                                        @elseif($binStatus === 'half')
                                            <i class="fas fa-exclamation-circle"></i> MODERATE
                                        @else
                                            <i class="fas fa-check-circle"></i> NORMAL
                                        @endif
                                    </span>
                                    <i class="fas fa-chevron-down ms-2"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Compartments List -->
                        <div class="collapse show" id="binSection{{ str_replace(' ', '', $binName) }}">
                            <div class="bin-section-body p-2 border border-top-0 rounded-bottom" style="background: #f8f9fa;">
                                @foreach($compartments as $compartmentName => $devices)
                                    @php
                                        $compDevice = $devices->first();
                                        $latest = $compDevice?->latestSensor;
                                        $capacity = $latest?->capacity ?? 0;
                                        $battery = $latest?->battery_percentage ?? null;
                                        
                                        if ($capacity > $compDevice?->asset?->capacitySetting?->half_to) {
                                            $compStatus = 'full';
                                            $compBadge = 'bg-danger';
                                            $compBarClass = 'bg-danger';
                                        } elseif ($capacity > $compDevice?->asset?->capacitySetting?->empty_to) {
                                            $compStatus = 'half';
                                            $compBadge = 'bg-warning';
                                            $compBarClass = 'bg-warning';
                                        } else {
                                            $compStatus = 'empty';
                                            $compBadge = 'bg-success';
                                            $compBarClass = 'bg-success';
                                        }
                                    @endphp
                                    
                                    <div class="compartment-card p-2 mb-2 rounded" 
                                         style="background: #fff; border-left: 4px solid {{ $compStatus === 'full' ? '#dc3545' : ($compStatus === 'half' ? '#ffc107' : '#28a745') }};">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i class="fas fa-recycle" style="color: #6c757d;"></i>
                                                    <span class="fw-semibold">{{ $compartmentName }}</span>
                                                    <span class="badge {{ $compBadge }} badge-sm">{{ strtoupper($compStatus) }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-3" style="font-size: 0.75rem; color: #6c757d;">
                                                    @if($battery !== null)
                                                    <span>
                                                        <i class="fas fa-battery-three-quarters" style="color: {{ $battery <= 20 ? '#dc3545' : ($battery <= 50 ? '#fd7e14' : '#28a745') }};"></i>
                                                        {{ $battery }}%
                                                    </span>
                                                    @endif
                                                    <span>
                                                        <i class="fas fa-clock"></i>
                                                        {{ $latest?->created_at ? $latest->created_at->diffForHumans() : 'No data' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-end" style="min-width: 80px;">
                                                <div class="fw-bold" style="font-size: 1.1rem;">{{ number_format($capacity, 0) }}%</div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar {{ $compBarClass }}" 
                                                         style="width: {{ $capacity }}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No bins found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function openAssetDetails(url) {
    window.open(url, '_blank');
}

document.addEventListener('DOMContentLoaded', () => {
    const filterSelect = document.getElementById('deviceFilter');
    let currentFilter = 'all';

    // FILTER FUNCTION - Filter bins by status
    function applyFilter(filter = 'all') {
        currentFilter = filter;
        
        document.querySelectorAll('.bin-section').forEach(section => {
            const binStatus = section.getAttribute('data-bin-status');
            
            let show = false;
            
            if (filter === 'all') {
                show = true;
            } else if (filter === 'critical') {
                // Show full first, then half, then others
                show = true;
            } else if (filter === 'full') {
                show = binStatus === 'full';
            } else if (filter === 'half') {
                show = binStatus === 'half';
            } else if (filter === 'empty') {
                show = binStatus === 'empty' || binStatus === 'undetected';
            }
            
            section.style.display = show ? 'block' : 'none';
        });
        
        // Sort for critical filter
        if (filter === 'critical') {
            const container = document.querySelector('.bins-organized-grid');
            const sections = Array.from(container.querySelectorAll('.bin-section'));
            
            sections.sort((a, b) => {
                const statusA = a.getAttribute('data-bin-status');
                const statusB = b.getAttribute('data-bin-status');
                
                const priority = { 'full': 1, 'half': 2, 'undetected': 3, 'empty': 4 };
                return (priority[statusA] || 4) - (priority[statusB] || 4);
            });
            
            sections.forEach(section => container.appendChild(section));
        }
    }

    // INITIAL FILTER
    applyFilter(currentFilter);
    filterSelect?.addEventListener('change', () => applyFilter(filterSelect.value));
});
</script>

@endsection
