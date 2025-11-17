@extends('layouts.app')
@section('content_title', 'Tasks')
@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Tasks</h4>
    </div>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            + Add Task
        </button>
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


<!-- 🔥 Move modal INSIDE the content section -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addTaskModalLabel">Add Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
          <form action="{{ route('tasks.store') }}" method="POST">
              @csrf

              <div class="form-group mb-3">
                  <label>User</label>
                  <select name="userID" class="form-control" required>
                      @foreach($users as $user)
                      <option value="{{ $user->id }}">{{ $user->name }}</option>
                      @endforeach
                  </select>
              </div>

              <div class="form-group mb-3">
                  <label>Asset</label>
                  <select name="assetID" class="form-control" required>
                      @foreach($assets as $asset)
                      <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                      @endforeach
                  </select>
              </div>

              <div class="form-group mb-3">
                  <label>Description</label>
                  <input type="text" name="description" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-primary">Create Task</button>
          </form>
      </div>

    </div>
  </div>
</div>

@endsection
