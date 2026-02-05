@props([
    'id' => null,
    'assets' => [],
    'device_name' => null,
    'asset_id' => null,
    'id_device' => null,
])

@php
    $isEdit = $id ? true : false;
    $modalId = $isEdit ? "formDevices{$id}" : "formDevicesNew";
@endphp

{{-- Button --}}
<button type="button"
    class="btn btn-sm {{ $isEdit ? 'btn-outline-secondary' : 'btn-secondary' }} {{ $attributes->get('class') }}"
    data-toggle="modal"
    data-target="#{{ $modalId }}"
    style="padding: 2px 6px; font-size: 10px; border-radius: 6px;">
    <i class="fas {{ $isEdit ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
    {{ $isEdit ? '' : 'Add' }}
</button>

{{-- Modal --}}
<div class="modal fade" id="{{ $modalId }}">
    <form method="POST"
          action="{{ $isEdit ? route('devices.update', $id) : route('devices.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <div class="modal-dialog">
            <div class="modal-content text-left">
                <div class="modal-header">
                    <h4 class="modal-title">{{ $isEdit ? 'Edit Device' : 'Add Device' }}</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Device ID</label>

                        @if($isEdit)
                            {{-- Show but lock --}}
                            <input type="text"
                                class="form-control"
                                value="{{ $id_device }}"
                                readonly>

                            {{-- Hidden input so value is still submitted --}}
                            <input type="hidden"
                                name="id_device"
                                value="{{ $id_device }}">
                        @else
                            {{-- Editable when adding --}}
                            <input type="text"
                                name="id_device"
                                class="form-control"
                                value="{{ $id_device }}">
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Device Name</label>
                        <input type="text" name="device_name" class="form-control" value="{{ $device_name }}">
                    </div>
                    <div class="form-group">
                        <label>Assets</label>
                        <select name="asset_id" class="form-control">
                            <option value="">-- Select Asset --</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ $asset_id == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->asset_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </form>
</div>
