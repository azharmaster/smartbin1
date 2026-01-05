@extends('layouts.app')

@section('content')
<div class="card card-success card-outline">
    <div class="card-header">
        <h5 class="mb-0">Full Bin Notifications</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Notification</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- Assuming $notification is passed from controller --}}
                @if(isset($notification))
                <tr data-widget="expandable-table" aria-expanded="false">
                    <td>1</td>
                    <td>Full Bin Alert</td>
                    <td>
                        @if($notification->is_active)
                            <span class="badge bg-success">ON</span>
                        @else
                            <span class="badge bg-danger">OFF</span>
                        @endif
                    </td>
                    <td>{{ $notification->start_time ? $notification->start_time->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $notification->end_time ? $notification->end_time->format('Y-m-d H:i') : '-' }}</td>
                    <td>
                       <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editNotificationModal">
    <i class="fas fa-bell"></i> 
</button>
                    </td>
                </tr>
                @else
                <tr>
                    <td colspan="6" class="text-center">No notification found.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Notification Modal -->
@if(isset($notification))
<div class="modal fade" id="editNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('whatsapp.update', $notification->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Notification Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                       
                    <div class="mb-3">
                        <label>Start Date & Time</label>
                        <input type="datetime-local" name="start_time" class="form-control"
                               value="{{ $notification->start_time ? $notification->start_time->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label>End Date & Time</label>
                        <input type="datetime-local" name="end_time" class="form-control"
                               value="{{ $notification->end_time ? $notification->end_time->format('Y-m-d\TH:i') : '' }}">
                    </div>

                    <div class="mb-3 d-flex align-items-center gap-3">
                    <span class="fw-semibold">OFF</span>

                    <div class="form-check form-switch m-0">
                    <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="is_active" 
                    value="1"
                    {{ $notification->is_active ? 'checked' : '' }}
                    >
                    </div>

                   <span class="fw-semibold">ON</span>
                   </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
