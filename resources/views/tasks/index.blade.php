@extends('layouts.app')
@section('content_title', 'Tasks')
@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Tasks</h4>
    </div>

    <div class="card-body">

        {{-- Show errors --}}
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
                <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        {{-- Add Task Button (top right like Assets page) --}}
        <div class="d-flex justify-content-end mb-2">
            <x-task.form-task :users="$users" :assets="$assets" />
        </div>

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Asset</th>
                        <th>Description</th>
                        <th>Option</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($tasks as $index => $task)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $task->user->name ?? 'N/A' }}</td>
                        <td>{{ $task->asset->asset_name ?? 'N/A' }}</td>
                        <td>{{ $task->description }}</td>

                        <td>
                            <div class="d-flex align-items-center justify-content-center">

                                {{-- Edit Task Button (opens same reusable modal component) --}}
                                <x-task.form-task 
                                    :id="$task->id"
                                    :users="$users"
                                    :assets="$assets"
                                    :description="$task->description"
                                    :user_id="$task->user_id"
                                    :asset_id="$task->asset_id"
                                />

                                {{-- View Modal Button --}}
                                <button 
                                    class="btn btn-info btn-sm mx-1"
                                    data-toggle="modal"
                                    data-target="#viewTaskModal{{ $task->id }}">
                                    <i class="fas fa-eye text-white"></i>
                                </button>

                                {{-- Delete --}}
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mx-1"
                                        onclick="return confirm('Delete this task?')">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- View Task Modal --}}
                    <div class="modal fade" id="viewTaskModal{{ $task->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Task Details</h4>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <p><strong>ID:</strong> {{ $task->id }}</p>
                                    <p><strong>User:</strong> {{ $task->user->name ?? 'N/A' }}</p>
                                    <p><strong>Asset:</strong> {{ $task->asset->asset_name ?? 'N/A' }}</p>
                                    <p><strong>Description:</strong> {{ $task->description }}</p>
                                </div>

                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>

                            </div>
                        </div>
                    </div>

                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
