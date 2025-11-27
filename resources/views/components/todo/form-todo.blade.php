@props([
    'id' => null, 
])

<div>
    <button type="button" 
        class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formTodo{{ $id ?? '' }}">

    <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
    {{ $id ? '' : 'Add' }}

</button>


    <div class="modal fade" id="formTodo{{ $id ?? '' }}">
        <form method="POST" action="{{ route('todos.store') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">

            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Form Edit To-do' : 'Form To-do' }}</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        {{-- To-do --}}
                        <div class="form-group">
                            <label>To-do</label>
                            <input type="text" class="form-control" name="todo"
                                   value="{{ $todo ?? '' }}">
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
