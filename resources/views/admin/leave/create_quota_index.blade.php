@extends('layouts.app')
@section('content_title', 'Users Leave Quota')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Users Leave Quota</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addQuotaModal">
            <i class="fas fa-plus"></i> Add Quota
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Year</th>
                        <th>Annual Leave</th>
                        <th>MC</th>
                        <th>Hospitality</th>
                        <th>Emergency Leave</th>
                        <th>Used Days</th>
                        <th>Remaining Days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotas as $index => $quota)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $quota->user->name }}</td>
                        <td>{{ $quota->year }}</td>
                        <td>{{ $quota->annual_leave ?? 0 }}</td>
                        <td>{{ $quota->mc ?? 0 }}</td>
                        <td>{{ $quota->hospitality ?? 0 }}</td>
                        <td>{{ $quota->emergency_leave ?? 0 }}</td>
                        <td>{{ $quota->used_days ?? 0 }}</td>
                        <td>{{ ($quota->annual_leave ?? 0 + $quota->mc ?? 0 + $quota->hospitality ?? 0 + $quota->emergency_leave ?? 0) - ($quota->used_days ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal to Add / Edit Leave Quota -->
<div class="modal fade" id="addQuotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.leave.quota.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Leave Quota</h5>

                <!-- Bootstrap 4 close button -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="user_id" class="form-label">User</label>

                    <!-- Bootstrap 4 uses form-control, not form-select -->
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>

                </div>

                <div class="mb-3">
                    <label for="year" class="form-label">Year</label>
                    <input type="number" name="year" id="year" class="form-control" value="{{ date('Y') }}" required>
                </div>

                <div class="mb-3">
                    <label for="annual_leave" class="form-label">Annual Leave (days)</label>
                    <input type="number" name="annual_leave" id="annual_leave" class="form-control" min="0" value="0">
                </div>

                <div class="mb-3">
                    <label for="mc" class="form-label">MC (days)</label>
                    <input type="number" name="mc" id="mc" class="form-control" min="0" value="0">
                </div>

                <div class="mb-3">
                    <label for="hospitality" class="form-label">Hospitality Leave (days)</label>
                    <input type="number" name="hospitality" id="hospitality" class="form-control" min="0" value="0">
                </div>

                <div class="mb-3">
                    <label for="emergency_leave" class="form-label">Emergency Leave (days)</label>
                    <input type="number" name="emergency_leave" id="emergency_leave" class="form-control" min="0" value="0">
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Quota</button>

                <!-- Bootstrap 4 dismiss -->
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

            </div>
        </form>
    </div>
</div>
@endsection
