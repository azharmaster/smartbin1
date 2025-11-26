@extends('layouts.staffapp')
@section('content_title', 'My Tasks')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">My Tasks</h4>
    </div>


<div class="card-body">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped dataTable dtr-inline">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Asset</th>
                    <th>Floor</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $index => $task)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $task->description }}</td>

                    <!-- Asset Column -->
                    <td>
                        @if($task->asset_id)
                            {{ $task->asset->asset_name }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <!-- Floor Column -->
                    <td>
                        @if($task->floor_id)
                            {{ $task->floor->floor_name }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <!-- Status Column -->
                    <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>

                    <!-- Actions / Dropdown Column -->
                    <td>
                        <form action="{{ route('staff.tasks.updateStatus', $task) }}" method="POST" class="d-flex">
                            @csrf
                            <select name="status" class="form-control form-control-sm">
                                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="reject" {{ $task->status == 'reject' ? 'selected' : '' }}>Reject</option>
                                <option value="in progress" {{ $task->status == 'in progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm ml-1">Update</button>
                        </form>
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
                                <p><strong>Description:</strong> {{ $task->description }}</p>
                                <p><strong>Asset:</strong> {{ $task->asset->asset_name ?? '-' }}</p>
                                <p><strong>Floor:</strong> {{ $task->floor->floor_name ?? '-' }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($task->status) }}</p>
                                <p><strong>Notes:</strong> {{ $task->notes ?? '-' }}</p>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach

                @if($tasks->isEmpty())
                <tr>
                    <td colspan="6" class="text-center">No tasks found.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>


</div>
@endsection
