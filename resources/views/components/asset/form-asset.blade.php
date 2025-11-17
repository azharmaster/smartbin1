@props([
    'id' => null, 
    'floors' => [], 
    'categories' => []
])

<div>
    <button type="button" class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}" 
            data-toggle="modal" data-target="#formAsset{{ $id ?? '' }}">
        {{ $id ? 'Edit' : 'Add' }}
    </button>

    <div class="modal fade" id="formAsset{{ $id ?? '' }}">
        <form method="POST" action="{{ route('master-data.assets.store') }}">
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

                        {{-- Asset Name --}}
                        <div class="form-group">
                            <label>Asset Name</label>
                            <input type="text" class="form-control" name="asset_name"
                                   value="{{ $asset_name ?? '' }}">
                        </div>

                        {{-- Floor (Dropdown of floor_name) --}}
                        <div class="form-group">
                            <label>Floor</label>
                            <select name="floor_id" class="form-control">
                                <option value="">-- Select Floor --</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}"
                                        {{ (isset($floor_id) && $floor_id == $floor->id) ? 'selected' : '' }}>
                                        {{ $floor->floor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Serial No --}}
                        <div class="form-group">
                            <label>Serial Number</label>
                            <input type="text" class="form-control" name="serialNo"
                                   value="{{ $serialNo ?? '' }}">
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ $description ?? '' }}</textarea>
                        </div>

                        {{-- Model --}}
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" class="form-control" name="model"
                                   value="{{ $model ?? '' }}">
                        </div>

                        {{-- Maintenance Date --}}
                        <div class="form-group">
                            <label>Maintenance Date</label>
                            <input type="datetime-local" class="form-control" name="maintenance"
                                   value="{{ isset($maintenance) ? date('Y-m-d\TH:i', strtotime($maintenance)) : '' }}">
                        </div>

                        {{-- Category (Dropdown of unique previous categories) --}}
                        <div class="form-group">
    <label>Category</label>
    <select name="category" class="form-control">
        <option value="">-- Select Category --</option>
        @foreach($categories as $cat)
            <option value="{{ $cat }}"
                {{ (isset($category) && $category == $cat) ? 'selected' : '' }}>
                {{ $cat }}
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
