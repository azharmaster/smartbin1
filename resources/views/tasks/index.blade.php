@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h1>Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="btn btn-success">+ Add Task</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Asset</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->user->name ?? 'N/A' }}</td>
                        <td>{{ $task->asset->name ?? 'N/A' }}</td>
                        <td>{{ $task->description }}</td>
                        <td>
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary btn-sm">Edit</a>
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this task?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if($tasks->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center">No tasks found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
