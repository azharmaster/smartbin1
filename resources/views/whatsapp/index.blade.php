@extends('layouts.app')
@section('content_title', 'WhatsApp Notification')
@section('content')

{{-- Floating Help Button --}}
<button type="button" onclick="openWhatsAppHelp()" style="
        position: fixed;
        top: 90px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #25D366;
        color: #fff;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        z-index: 999;
    "
    title="WhatsApp Notification Guide"
>
    ?
</button>

{{-- Custom CSS for toggle --}}
<style>
/* Custom ON/OFF toggle switch */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #0d6efd; /* Blue when ON */
}

input:checked + .slider:before {
  transform: translateX(24px);
}
</style>

<div class="row">

    <!-- LEFT COLUMN: General Full Bin Notification -->
<div class="col-md-6">

    <div class="card card-success card-outline mb-4">
        <div class="card-header text-center">
            <h5 class="mb-0 fw-bold">Full Bin Notification</h5>
            <small class="text-muted">Manage WhatsApp alert settings</small>
        </div>

        <div class="card-body">

            {{-- Display Success Message --}}
            @if(session('success'))
                <div class="alert alert-success text-center">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Check if notification exists --}}
            @if(isset($notification))

            {{-- Form to update full bin notification --}}
            <form action="{{ route('whatsapp.update', $notification->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Notification Title -->
                <div class="mb-4">
                    <label class="form-label text-muted">Notification</label>
                    <div class="form-control-plaintext fw-semibold">
                        Full Bin Alert
                    </div>
                </div>

                <!-- Current Status -->
                <div class="mb-4">
                    <label class="form-label text-muted">Current Status</label><br>
                    @if($notification->is_active)
                        <span class="badge bg-success px-3 py-2">ON</span>
                    @else
                        <span class="badge bg-danger px-3 py-2">OFF</span>
                    @endif
                </div>

                <hr>

                <!-- Default Every Day (Hidden Dates) -->
                <input type="hidden" name="start_date" value="">
                <input type="hidden" name="end_date" value="">

                <!-- Start Time -->
                <div class="mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" 
                           name="start_time" 
                           class="form-control"
                           value="{{ old('start_time', $notification->start_time ?? '') }}">
                </div>

                <!-- End Time -->
                <div class="mb-4">
                    <label class="form-label">End Time</label>
                    <input type="time" 
                           name="end_time" 
                           class="form-control"
                           value="{{ old('end_time', $notification->end_time ?? '') }}">
                </div>

                <!-- ON / OFF Switch (Custom Style) -->
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">Notification Status</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">OFF</span>
                        <label class="switch m-0">
                            <input type="checkbox" name="is_active" value="1" {{ $notification->is_active ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                        <span class="text-muted small">ON</span>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>

            </form>

            @else
                <div class="alert alert-warning text-center">
                    No notification settings found.
                </div>
            @endif

        </div>
    </div>

</div>


    <!-- RIGHT COLUMN: Bins & Devices Notification -->
    <div class="col-md-6">

        <div class="card card-success card-outline mb-4">
            <div class="card-header text-center">
                <h5 class="mb-0 fw-bold">Bins Notification</h5>
                <small class="text-muted">Click a bin to manage its devices</small>
            </div>

            <div class="card-body">

                {{-- 🔍 Search Bar (Name & Location) --}}
                <div class="mb-3">
                    <input type="text" 
                           id="binSearch" 
                           class="form-control" 
                           placeholder="Search by bin name or location...">
                </div>

                @if($bins ?? false)
                <div id="binList">
                    @foreach($bins as $bin)
                        {{-- Bin row with custom toggle --}}
                        <div class="bin-item d-flex justify-content-between align-items-center border p-2 mb-2 rounded"
                             data-name="{{ strtolower($bin->asset_name) }}"
                             data-location="{{ strtolower($bin->location ?? '') }}">
                            
                          {{-- Bin Name opens modal --}}
                        <span class="fw-semibold d-flex align-items-start gap-2"
                            style="cursor:pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#binModal{{ $bin->id }}">

                            {{-- Clickable Button --}}
                            <button type="button" class="btn btn-sm btn-outline-success p-1">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Bin Info --}}
                            <span>
                                {{ $bin->asset_name }}

                                @if($bin->off_devices_count > 0)
                                    <small class="text-danger fw-semibold ms-1">
                                        ({{ $bin->off_devices_count }}
                                        sensor{{ $bin->off_devices_count > 1 ? 's' : '' }} off)
                                    </small>
                                @else
                                    <small class="text-success ms-1">
                                        (All sensors on)
                                    </small>
                                @endif
                                @if(!empty($bin->location))
                                    <small class="text-muted d-block">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $bin->location }}
                                    </small>
                                @endif
                            </span>

                        </span>




                            {{-- Bin ON/OFF toggle using custom style --}}
                            <form action="{{ route('whatsapp.bin.toggle', $bin->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PUT')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small">OFF</span>
                                    <label class="switch m-0">
                                        <input type="checkbox" name="is_active" value="1" {{ $bin->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="text-muted small">ON</span>
                                </div>
                            </form>
                        </div>

                        {{-- Modal for devices --}}
                        <div class="modal fade" id="binModal{{ $bin->id }}" tabindex="-1" aria-labelledby="binModalLabel{{ $bin->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">
                                    
                                    {{-- Modal Header --}}
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="binModalLabel{{ $bin->id }}">Devices in {{ $bin->asset_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    {{-- Modal Body: Devices list --}}
                                    <div class="modal-body">
                                        @foreach($bin->devices as $device)
                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2 px-2">
                                                <span>{{ $device->device_name }}</span>
                                                {{-- Device toggle using custom style --}}
                                                <form action="{{ route('whatsapp.device.toggle', $device->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="text-muted small">OFF</span>
                                                        <label class="switch m-0">
                                                            <input type="checkbox" name="is_active" value="1" {{ $device->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <span class="text-muted small">ON</span>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Modal Footer --}}
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    @endforeach
                    </div>
                @else
                    <div id="noResults" class="text-center text-muted mt-3 d-none">
                        No bins found
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>

{{-- 🔎 Client-side Search Script --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const input = document.getElementById('binSearch');
    const bins  = Array.from(document.querySelectorAll('#binList .bin-item'));
    const empty = document.getElementById('noResults');

    if (!input || !bins.length) return;

    input.addEventListener('input', () => {
        const keyword = input.value.trim().toLowerCase();
        let visibleCount = 0;

        bins.forEach(bin => {
            const name = bin.getAttribute('data-name') || '';
            const location = bin.getAttribute('data-location') || '';

            const match = name.includes(keyword) || location.includes(keyword);

            bin.classList.toggle('d-none', !match);

            if (match) visibleCount++;
        });

        empty.classList.toggle('d-none', visibleCount !== 0);
    });

});
</script>

{{-- WhatsApp Help Modal --}}
<div class="modal fade" id="whatsappHelpModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">WhatsApp Notification – User Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>
                    The <strong>WhatsApp Notification</strong> page allows you to monitor your bins and devices and receive alerts via WhatsApp when certain conditions are met. Follow the guide below to understand how to configure and manage notifications.
                </p>

                <h6 class="mt-3 fw-semibold">1. Full Bin Alert</h6>
                <p>
                    This section lets you enable or disable global notifications when any bin reaches full capacity.
                    <ul>
                        <li><strong>Current Status:</strong> Shows whether the notification is <span class="badge bg-success">ON</span> or <span class="badge bg-danger">OFF</span>.</li>
                        <li><strong>Start / End Time:</strong> Define the time range when notifications should be active. Alerts will only be sent within this time window.</li>
                        <li><strong>ON/OFF Switch:</strong> Toggle to enable or disable the notification globally.</li>
                        <li><strong>Save Button:</strong> Apply your changes.</li>
                    </ul>
                </p>

                <h6 class="mt-3 fw-semibold">2. Bin & Device Notifications</h6>
                <p>
                    Each bin can have individual devices (sensors) that can trigger alerts. You can manage their notifications separately.
                    <ul>
                        <li><strong>Bin List:</strong> Shows all bins with a summary of their devices.</li>
                        <li><strong>Eye Icon:</strong> Click to view the devices inside that bin.</li>
                        <li><strong>Device Status:</strong> Each device has an ON/OFF toggle:
                            <ul>
                                <li>OFF: No notifications will be sent for this device.</li>
                                <li>ON: Notifications will be sent for this device.</li>
                            </ul>
                        </li>
                        <li><strong>Bin Toggle:</strong> Enable or disable notifications for all devices in the bin at once.</li>
                        <li><strong>Search Bar:</strong> Quickly find bins by name or location. The list updates as you type.</li>
                        <li><strong>Real-Time Updates:</strong> Any toggle change (bin or device) is saved immediately without needing to submit a form.</li>
                    </ul>
                </p>

                <h6 class="mt-3 fw-semibold">3. Tips for Efficient Notifications</h6>
                <ul>
                    <li>Enable notifications only for active devices to avoid unnecessary alerts.</li>
                    <li>Use the Start / End time to limit alerts to working hours or specific periods.</li>
                    <li>Check the device status regularly to ensure all sensors are online.</li>
                    <li>Use the search bar if you have many bins for quicker management.</li>
                </ul>

                <p class="mb-0 text-muted mt-3">
                    Following these steps ensures you receive accurate and timely WhatsApp alerts for your bins and devices.
                </p>
            </div>
        </div>
    </div>
</div>


{{-- JS to open WhatsApp help modal --}}
@push('scripts')
<script>
function openWhatsAppHelp() {
    var modal = new bootstrap.Modal(document.getElementById('whatsappHelpModal'));
    modal.show();
}
</script>
@endpush

@endsection
