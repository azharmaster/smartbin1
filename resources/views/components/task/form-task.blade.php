@props([
    'id' => null,
    'users' => [],
    'assets' => [],
    'floors' => [],
    'user_id' => null,    
    'asset_id' => null,
    'floor_id' => null,
    'description' => null,
    'status' => 'pending',
    'notes' => null,
    'readonly' => false,
    'minimal' => false,  // new prop
])

<div>
    {{-- Button --}}
    <button type="button" 
            class="{{ $id ? 'btn btn-default btn-sm mx-1' : 'btn btn-primary' }}" 
            data-toggle="modal" 
            data-target="#formTask{{ $id ?? '' }}">
        {{ $id ? 'Edit' : 'Add' }}
    </button>

    {{-- Modal --}}
    <div class="modal fade" id="formTask{{ $id ?? '' }}">
        <form method="POST" action="{{ $id ? route('tasks.update', $id) : route('tasks.store') }}">
            @csrf
            @if($id)
                @method('PUT')
            @endif

            <div class="modal-dialog">
                <div class="modal-content">

                    {{-- Header --}}
                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Edit Task' : 'Add Task' }}</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body">

                        @if(!$minimal)
                        {{-- User --}}
                        <div class="form-group">
                            <label>User</label>
                            <select name="user_id" class="form-control" required {{ $readonly ? 'disabled' : '' }}>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Asset --}}
                        <div class="form-group">
                            <label>Asset</label>
                            <select name="asset_id" class="form-control" required {{ $readonly ? 'disabled' : '' }}>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ $asset_id == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->asset_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Floor --}}
                        <div class="form-group">
                            <label>Floor</label>
                            <select name="floor_id" class="form-control" required {{ $readonly ? 'disabled' : '' }}>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ $floor_id == $floor->id ? 'selected' : '' }}>
                                        {{ $floor->floor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" 
                                   name="description" 
                                   class="form-control" 
                                   value="{{ $description ?? '' }}"
                                   required
                                   {{ $readonly ? 'readonly' : '' }}>
                        </div>

                        @if(!$minimal)
                        {{-- Notes --}}
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" {{ $readonly ? 'readonly' : '' }}>{{ $notes ?? '' }}</textarea>
                        </div>

                        {{-- Status --}}
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" {{ $readonly ? 'disabled' : '' }}>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="accepted" {{ $status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        @endif

                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        @if(!$readonly)
                            <button type="submit" class="btn btn-primary">
                                {{ $id ? 'Save changes' : 'Create Task' }}
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
