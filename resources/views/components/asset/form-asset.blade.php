@props([
    'id' => null, 
    'floors' => [],
    'picture' => null
])

<div>
    <button type="button" 
        class="btn  {{ $id ? 'btn-outline-secondary' : 'btn-primary' }}"
        data-toggle="modal" data-target="#formAsset{{ $id ?? '' }}">

        <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
        {{ $id ? '' : 'Add' }}

    </button>

    <div class="modal fade" id="formAsset{{ $id ?? '' }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <form method="POST" action="{{ route('master-data.assets.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $id ?? '' }}">

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

                        {{-- Location (was Description) --}}
                        <div class="form-group">
                            <label>Location</label>
                            <textarea class="form-control" name="location" rows="3">{{ $location ?? '' }}</textarea>
                        </div>

                        {{-- Model --}}
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" class="form-control" name="model"
                                   value="{{ $model ?? '' }}">
                        </div>

                        {{-- Latitude --}}
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="number"
                                step="any"
                                class="form-control"
                                name="latitude"
                                value="{{ $latitude ?? '' }}">
                        </div>

                        {{-- Longitude --}}
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="number"
                                step="any"
                                class="form-control"
                                name="longitude"
                                value="{{ $longitude ?? '' }}">
                        </div>

                        {{-- Asset Picture --}}
                        <div class="form-group">
                            <label>Asset Picture</label>

                            <div id="dropZone{{ $id ?? 'new' }}"
                                 class="border rounded p-3 text-center"
                                 style="cursor:pointer; background:#f8f9fa;">

                                <input type="file"
                                       id="pictureInput{{ $id ?? 'new' }}"
                                       name="picture"
                                       class="d-none"
                                       accept="image/*">

                                <img id="imagePreview{{ $id ?? 'new' }}"
                                     src="{{ isset($picture) && $picture 
                                            ? asset(str_starts_with($picture, 'uploads/') 
                                                ? $picture 
                                                : 'uploads/asset/'.$picture) 
                                            : '' }}"
                                     class="img-thumbnail {{ isset($picture) && $picture ? '' : 'd-none' }}"
                                     style="max-height:120px;">

                                <p id="dropText{{ $id ?? 'new' }}"
                                   class="mb-0 text-muted {{ isset($picture) && $picture ? 'd-none' : '' }}">
                                    Click or drag image here
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const id = '{{ $id ?? "new" }}';
    const dropZone = document.getElementById('dropZone' + id);
    const fileInput = document.getElementById('pictureInput' + id);
    const preview = document.getElementById('imagePreview' + id);
    const text = document.getElementById('dropText' + id);

    if (!dropZone) return;

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('border-success');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-success');
    });

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-success');

        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showPreview(fileInput.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            showPreview(fileInput.files[0]);
        }
    });

    function showPreview(file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
        text.classList.add('d-none');
    }

    // ==========================
    // Fix duplicate modal-backdrop flicker on mobile
    // ==========================
    const modalEl = document.getElementById('formAsset' + id);
    if (modalEl) {
        $(modalEl).on('show.bs.modal', function () {
            // remove any extra backdrop before opening
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        });
    }

});
</script>
