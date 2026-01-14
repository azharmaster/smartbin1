@props([
    'id' => null, 
    'assets' => [], 
    'device_name' => null,
    'asset_id' => null,
    'id_device' => null,
])

<!-- SINGLE BUTTON, position controlled via $attributes -->
<button type="button" 
    class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }} {{ $attributes->get('class') }}"
    data-toggle="modal" 
    data-target="#formDevices{{ $id ?? '' }}"
    style="
        position: absolute;
        top: 6px;
        right: 6px;
        padding: 2px 6px;
        font-size: 10px;
        border-radius: 6px;
    ">
    <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
    {{ $id ? '' : 'Add' }}
</button>

<!-- Modal (kept intact) -->
<div class="modal fade" id="formDevices{{ $id ?? '' }}">
    <form method="POST" 
          action="{{ $id ? route('devices.update', $id) : route('devices.store') }}">
        @csrf
        @if($id)
            @method('PUT')
        @endif

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">{{ $id ? 'Form Edit Device' : 'Form Add Device' }}</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Device ID</label>
                        <input type="text" class="form-control" name="id_device"
                               value="{{ $id_device }}">
                    </div>

                    <div class="form-group">
                        <label>Device Name</label>
                        <input type="text" class="form-control" name="device_name"
                               value="{{ $device_name }}">
                    </div>

                    <div class="form-group">
                        <label>Assets</label>
                        <select name="asset_id" class="form-control">
                            <option value="">-- Select Assets --</option>
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
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

            </div>
        </div>
    </form>
</div>
