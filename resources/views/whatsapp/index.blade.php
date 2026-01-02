@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">WhatsApp Notifications</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            Create New Notification
        </button>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Last Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $index => $notif)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $notif->title }}</td>
                        <td>{{ Str::limit($notif->message, 50) }}</td>
                        <td>
                            @if($notif->is_active)
                                <span class="badge bg-success">ON</span>
                            @else
                                <span class="badge bg-danger">OFF</span>
                            @endif
                        </td>
                        <td>{{ $notif->start_time ? $notif->start_time->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $notif->end_time ? $notif->end_time->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $notif->last_sent_at ? $notif->last_sent_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $notif->id }}">
                                    <i class="far fa-edit"></i>
                                </button>

                                <!-- Delete Form -->
                                <form action="{{ route('whatsapp.destroy', $notif->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this notification?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>

                                <!-- Send Now Form -->
                                <form action="{{ route('whatsapp.send', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info">
                                        <i class="far fa-paper-plane"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $notif->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('whatsapp.update', $notif->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Notification</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $notif->title }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Message</label>
                                            <textarea name="message" class="form-control" rows="4" required>{{ $notif->message }}</textarea>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $notif->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">Active (ON)</label>
                                        </div>
                                        <div class="mb-3">
                                            <label>Start Time</label>
                                            <input type="datetime-local" name="start_time" class="form-control"
                                                   value="{{ $notif->start_time ? $notif->start_time->format('Y-m-d\TH:i') : '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>End Time</label>
                                            <input type="datetime-local" name="end_time" class="form-control"
                                                   value="{{ $notif->end_time ? $notif->end_time->format('Y-m-d\TH:i') : '' }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('whatsapp.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Active (ON)</label>
                    </div>
                    <div class="mb-3">
                        <label>Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
