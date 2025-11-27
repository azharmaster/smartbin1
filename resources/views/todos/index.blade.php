@extends('layouts.app')
@section('content_title', 'To-Do')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">To-Do</h4>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-end mb-2">
            <x-todo.form-todo />
        </div>

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>To-do</th>
                        <th>Status</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($todos as $index => $todo)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $todo->todo }}</td>
                        <td>{{ $todo->status }}</td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <x-todo.form-todo :id="$todo->id" />&nbsp;
                                <form action="{{ route('todos.destroy', $todo->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm-delete="true">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
