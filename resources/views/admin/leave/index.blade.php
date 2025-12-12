@extends('layouts.app')
@section('content_title', 'Leave Requests')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Leave Requests</h4>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>Until</th>
                        <th>Reason</th>
                        <th>Use</th>
                        <th>Status</th>
                        <th>Date Applied</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $index => $leave)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $leave->user->name }}</td>
                        <td>{{ ucfirst($leave->type) }}</td>
                        <td>{{ $leave->start_date }}</td>
                        <td>{{ $leave->end_date ?? '-' }}</td>
                        <td>{{ $leave->reason }}</td>
                        <td>{{ $leave->use ? ucwords(str_replace('_', ' ', $leave->use)) : '-' }}</td>
                        <td>
                            @if(strtolower($leave->status) == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif(strtolower($leave->status) == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td>{{ $leave->created_at->format('Y-m-d') }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" 
                                    data-toggle="modal" 
                                    data-target="#leaveModal{{ $leave->id }}">
                                View
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modals -->
        @foreach($leaves as $leave)
        <div class="modal fade" id="leaveModal{{ $leave->id }}" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel{{ $leave->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveModalLabel{{ $leave->id }}">
                            Leave Details - {{ $leave->user->name }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Type:</strong> {{ ucfirst($leave->type) }}</p>
                        <p><strong>From:</strong> {{ $leave->start_date }}</p>
                        <p><strong>Until:</strong> {{ $leave->end_date ?? '-' }}</p>
                        <p><strong>Total Days:</strong> 
                            @if($leave->type == 'halfday') 
                                0.5 
                            @else 
                                {{ $leave->end_date ? ((\Carbon\Carbon::parse($leave->end_date)->diffInDays(\Carbon\Carbon::parse($leave->start_date))) + 1) : 1 }} 
                            @endif
                        </p>
                        <p><strong>Reason:</strong> {{ $leave->reason }}</p>
                        <p><strong>Use:</strong> {{ $leave->use ?? '-' }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($leave->status) }}</p>
                        <p><strong>Applied On:</strong> {{ $leave->created_at->format('Y-m-d') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection
