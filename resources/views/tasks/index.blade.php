@extends($layout)
@section('content_title', 'Assign Task')
@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Tasks</h4>
    </div>

    <div class="card-body">

        {{-- Display Errors --}}
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
                <small class="text-white my-1">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        {{-- Buttons above table, right-aligned --}}
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#assignTaskModal">
                Assign Task
            </button>
        </div>

        {{-- Tasks Table --}}
        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Asset</th>
                        <th>Floor</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Sort tasks by latest first
                        $tasksDesc = $tasks->sortByDesc('created_at');

                        // Build numbering based on latest first
                        $taskNumbers = [];
                        $counter = 1;
                        foreach ($tasksDesc as $t) {
                            $taskNumbers[$t->id] = $counter++;
                        }
                    @endphp

                    {{-- Display latest tasks at the top --}}
                    @foreach ($tasksDesc as $task)
                    <tr>
                        <td>{{ $taskNumbers[$task->id] }}</td>
                        <td>{{ $task->user->name ?? 'N/A' }}</td>
                        <td>{{ $task->asset->asset_name ?? 'N/A' }}</td>
                        <td>{{ $task->floor->floor_name ?? 'N/A' }}</td>
                        <td>{{ $task->description }}</td>

                        <!-- Status Column with badge -->
                        <td>
                            @php
                                $status = $task->status;
                                $badgeClass = match($status) {
                                    'pending' => 'bg-warning',
                                    'in_progress' => 'bg-info',
                                    'completed' => 'bg-success',
                                    'reject' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        <td>{{ $task->notes ?? '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center">

                                {{-- Edit Task --}}
                                <x-task.form-task 
                                    :id="$task->id"
                                    :users="$users"
                                    :assets="$assets"
                                    :floors="$floors"
                                    :user_id="$task->user_id"
                                    :asset_id="$task->asset_id"
                                    :floor_id="$task->floor_id"
                                    :description="$task->description"
                                    :notes="$task->notes"
                                    :status="$task->status"
                                />

                                {{-- View Task --}}
                                <button class="btn btn-info btn-sm mx-1" data-toggle="modal"
                                    data-target="#viewTaskModal{{ $task->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Delete Task --}}
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="ms-1">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this task?')">
                                        <i class="fas fa-trash-alt"></i>
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
                                    <p><strong>Floor:</strong> {{ $task->floor->floor_name ?? 'N/A' }}</p>
                                    <p><strong>Description:</strong> {{ $task->description }}</p>
                                    <p><strong>Status:</strong>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </p>
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
                        <td colspan="8" class="text-center">No tasks found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Assign Task Modal --}}
<div class="modal fade" id="assignTaskModal">
    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Assign Task to Staff</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    {{-- Staff --}}
                    <div class="form-group">
                        <label>Staff</label>
                        <select name="user_id" class="form-control" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Floor --}}
                    <div class="form-group">
                        <label>Floor</label>
                        <select name="floor_id" class="form-control" required>
                            @foreach($floors as $floor)
                                <option value="{{ $floor->id }}">{{ $floor->floor_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Asset --}}
                    <div class="form-group">
                        <label>Asset</label>
                        <select name="asset_id" class="form-control" required>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->asset_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" required>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Assign Task</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection
