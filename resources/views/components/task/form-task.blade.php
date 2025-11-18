@props([
    'id' => null,
    'users' => [],
    'assets' => [],
    'user_id' => null,    {{-- updated --}}
    'asset_id' => null,   {{-- updated --}}
    'description' => null,
    'readonly' => false,  {{-- optional for read-only --}}
])

<div>
    {{-- Button: same style as asset form --}}
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

                        {{-- User Dropdown --}}
                        <div class="form-group">
                            <label>User</label>
                            <select name="user_id" class="form-control" required {{ $readonly ? 'disabled' : '' }}>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Asset Dropdown --}}
                        <div class="form-group">
                            <label>Asset</label>
                            <select name="asset_id" class="form-control" required {{ $readonly ? 'disabled' : '' }}>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}"
                                        {{ $asset_id == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->asset_name }}
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
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Close
                        </button>
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
