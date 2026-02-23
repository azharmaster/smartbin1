@props([
    'id' => null,
    'assets' => [],
    'device_name' => null,
    'asset_id' => null,
    'id_device' => null,
    'serialno' => null,
    'simcard' => null,
])

@php
    $isEdit = $id ? true : false;
    $modalId = $isEdit ? "formDevices{$id}" : "formDevicesNew";
@endphp

{{-- Button --}}
<button type="button"
    class="btn  {{ $isEdit ? 'btn-outline-secondary' : 'btn-primary' }} {{ $attributes->get('class') }}"
    data-toggle="modal"
    data-target="#{{ $modalId }}">
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
                    {{-- Device ID --}}
                    <div class="form-group">
                        <label>Device ID</label>

                        @if($isEdit)
                            <input type="text"
                                   class="form-control"
                                   value="{{ $id_device }}"
                                   readonly>

                            <input type="hidden"
                                   name="id_device"
                                   value="{{ $id_device }}">
                        @else
                            <input type="text"
                                   name="id_device"
                                   class="form-control"
                                   value="{{ $id_device }}">
                        @endif
                    </div>

                    {{-- Serial Number --}}
                    <div class="form-group">
                        <label>Serial Number</label>
                        <input type="text"
                               name="serialno"
                               class="form-control"
                               value="{{ $serialno }}">
                    </div>

                    {{-- SIM Card --}}
                    <div class="form-group">
                        <label>SIM Card</label>
                        <input type="text"
                               name="simcard"
                               class="form-control"
                               value="{{ $simcard }}">
                    </div>

                    {{-- Device Name --}}
                    <div class="form-group">
                        <label>Device Name</label>
                        <select name="device_name" class="form-control">
                            <option value="GLASS" {{ $device_name === 'GLASS' ? 'selected' : '' }}>
                                GLASS
                            </option>
                            <option value="PAPER" {{ $device_name === 'PAPER' ? 'selected' : '' }}>
                                PAPER
                            </option>
                            <option value="GENERAL" {{ $device_name === 'GENERAL' ? 'selected' : '' }}>
                                GENERAL
                            </option>
                        </select>
                    </div>

                    {{-- Assets --}}
                    <div class="form-group">
                        <label>Assets</label>
                        <select name="asset_id" class="form-control">
                            <option value="">-- Select Asset --</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}"
                                    {{ $asset_id == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->asset_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
