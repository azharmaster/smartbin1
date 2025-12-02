@extends('layouts.staffapp')
@section('content_title', 'My Leave')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Your Leave History</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#applyLeaveModal">
            Apply Leave
        </button>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="table1">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>From</th>
                    <th>Until</th>
                    <th>Use</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $leave)
                <tr>
                    <td>{{ ucfirst($leave->type) }}</td>
                    <td>{{ $leave->start_date }}</td>
                    <td>{{ $leave->end_date }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $leave->use)) }}</td>
                    <td>{{ $leave->reason }}</td>
                    <td>{{ ucfirst($leave->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" role="dialog" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('staff.leave.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="applyLeaveModalLabel">Apply for Leave</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="halfday">Half-day</option>
                    <option value="fullday">Full-day</option>
                </select>
            </div>
            <div class="form-group">
                <label>From</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Until</label>
                <input type="date" name="end_date" class="form-control">
            </div>
            <div class="form-group">
                <label>Use</label>
                <select name="leave_use" class="form-control" required>
                    @php
                        $leaveTypes = [
                            'mc' => 'MC',
                            'emergency_leave' => 'Emergency Leave',
                            'annual_leave' => 'Annual Leave',
                            'hospitality' => 'Hospitality',
                        ];
                        // $quota is passed from controller
                    @endphp

                    @foreach($leaveTypes as $key => $label)
                        @php
                            // Calculate remaining per leave type separately
                            switch ($key) {
                                case 'mc':
                                    $used = $quota->used_mc ?? 0;
                                    break;
                                case 'annual_leave':
                                    $used = $quota->used_annual ?? 0;
                                    break;
                                case 'hospitality':
                                    $used = $quota->used_hospitality ?? 0;
                                    break;
                                case 'emergency_leave':
                                    $used = $quota->used_emergency ?? 0;
                                    break;
                                default:
                                    $used = 0;
                            }
                            $remaining = max(0, ($quota->$key ?? 0) - $used);
                        @endphp
                        <option value="{{ $key }}" {{ $remaining <= 0 ? 'disabled' : '' }}>
                            {{ $label }} ({{ $remaining }}/{{ $quota->$key ?? 0 }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" class="form-control" rows="3" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit Leave</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
