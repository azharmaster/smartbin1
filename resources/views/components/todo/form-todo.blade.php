@props([
'id' => null,
'todo' => null,
'status' => 'pending'
])

<div>
    <button type="button" 
        class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formTodo{{ $id ?? 'new' }}">
        <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
        {{ $id ? '' : 'Add' }}
    </button>

<div class="modal fade" id="formTodo{{ $id ?? 'new' }}">
    <form method="POST" action="{{ $id ? route('todos.update', $id) : route('todos.store') }}">
        @csrf
        @if($id)
            @method('PUT')
        @endif

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">{{ $id ? 'Edit To-do' : 'Add To-do' }}</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- To-do --}}
                    <div class="form-group">
                        <label>To-do</label>
                        <input type="text" class="form-control" name="todo"
                               value="{{ $todo ?? '' }}" required>
                    </div>

                    {{-- Status (only for edit) --}}
                    @if($id)
                    <div class="form-group mt-2">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="done" {{ $status === 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    @endif

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
