@props([
    'id' => null, 
    'assets' => [], 
    'categories' => []
])

<div>
    <button type="button" 
        class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formDevices{{ $id ?? '' }}">

    <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
    {{ $id ? '' : 'Add' }}

</button>


    <div class="modal fade" id="formDevices{{ $id ?? '' }}">
        <form method="POST" action="{{ route('devices.store') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">

            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Form Edit Asset' : 'Form Add Asset' }}</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        {{-- Device Name --}}
                        <div class="form-group">
                            <label>Device Name</label>
                            <input type="text" class="form-control" name="device_name"
                                   value="{{ $device_name ?? '' }}">
                        </div>

                        {{-- Assets (Dropdown of floor_name) --}}
                        <div class="form-group">
                            <label>Assets</label>
                            <select name="asset_id" class="form-control">
                                <option value="">-- Select Assets --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}"
                                        {{ (isset($asset_id) && $asset_id == $asset->id) ? 'selected' : '' }}>
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
</div>
