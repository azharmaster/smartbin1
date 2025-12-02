@props([
'id' => null,
'title' => '',
'description' => '',
'asset_id' => null,
'assets' => [],
])

<div>
    <button type="button" 
        class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formComplaint{{ $id ?? '' }}">
        <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
        {{ $id ? '' : 'Submit' }}
    </button>


<div class="modal fade" id="formComplaint{{ $id ?? '' }}">
    <form method="POST" 
          action="{{ $id ? route('complaints.update', $id) : route('complaints.store') }}">
        @csrf
        @if($id)
            @method('PUT')
        @endif

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">{{ $id ? 'Edit Complaint' : 'Submit Complaint' }}</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    {{-- Asset --}}
                    <div class="form-group">
                        <label>Asset</label>
                        <select name="asset_id" class="form-control" required>
                            <option value="">-- Select Asset --</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}"
                                    {{ $asset_id == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->asset_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Title --}}
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" 
                            value="{{ $title }}" required>
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" required>{{ $description }}</textarea>
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
