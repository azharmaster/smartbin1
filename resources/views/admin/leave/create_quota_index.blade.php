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
                        <th>Actions</th>
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
                        <td>
                            @php
                                $remainingAnnual = max(0, ($quota->annual_leave ?? 0) - ($quota->used_annual ?? 0));
                                $remainingMC = max(0, ($quota->mc ?? 0) - ($quota->used_mc ?? 0));
                                $remainingHospitality = max(0, ($quota->hospitality ?? 0) - ($quota->used_hospitality ?? 0));
                                $remainingEmergency = max(0, ($quota->emergency_leave ?? 0) - ($quota->used_emergency ?? 0));
                            @endphp
                            Annual: {{ $remainingAnnual }},
                            MC: {{ $remainingMC }},
                            Hospitality: {{ $remainingHospitality }},
                            Emergency: {{ $remainingEmergency }}
                        </td>
                        <td>
                            <!-- Edit button triggers modal -->
                            <button class="btn btn-sm btn-warning editQuotaBtn"
                                data-id="{{ $quota->id }}"
                                data-user="{{ $quota->user_id }}"
                                data-username="{{ $quota->user->name }}"
                                data-year="{{ $quota->year }}"
                                data-annual="{{ $quota->annual_leave }}"
                                data-mc="{{ $quota->mc }}"
                                data-hospitality="{{ $quota->hospitality }}"
                                data-emergency="{{ $quota->emergency_leave }}"
                                data-toggle="modal" data-target="#editQuotaModal">
                                Edit
                            </button>
                            
                            <!-- Delete button -->
                            <form action="{{ route('admin.leave.quota.destroy', $quota->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this quota?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal to Add Leave Quota -->
<div class="modal fade" id="addQuotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.leave.quota.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Leave Quota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="user_id" class="form-label">User</label>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal to Edit Leave Quota -->
<div class="modal fade" id="editQuotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editQuotaForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Leave Quota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="quota_id" id="edit_quota_id">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label">User</label>
                    <input type="text" id="edit_user_name" class="form-control" disabled>
                </div>
                <div class="mb-3">
                    <label for="edit_year" class="form-label">Year</label>
                    <input type="number" name="year" id="edit_year" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit_annual_leave" class="form-label">Annual Leave (days)</label>
                    <input type="number" name="annual_leave" id="edit_annual_leave" class="form-control" min="0">
                </div>
                <div class="mb-3">
                    <label for="edit_mc" class="form-label">MC (days)</label>
                    <input type="number" name="mc" id="edit_mc" class="form-control" min="0">
                </div>
                <div class="mb-3">
                    <label for="edit_hospitality" class="form-label">Hospitality Leave (days)</label>
                    <input type="number" name="hospitality" id="edit_hospitality" class="form-control" min="0">
                </div>
                <div class="mb-3">
                    <label for="edit_emergency_leave" class="form-label">Emergency Leave (days)</label>
                    <input type="number" name="emergency_leave" id="edit_emergency_leave" class="form-control" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Quota</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Script to fill edit modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editQuotaBtn');
    const form = document.getElementById('editQuotaForm');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            form.action = `/leave/quota/${id}/update`;
            document.getElementById('edit_quota_id').value = id;
            document.getElementById('edit_user_id').value = this.dataset.user;
            document.getElementById('edit_user_name').value = this.dataset.username; // show username
            document.getElementById('edit_year').value = this.dataset.year;
            document.getElementById('edit_annual_leave').value = this.dataset.annual;
            document.getElementById('edit_mc').value = this.dataset.mc;
            document.getElementById('edit_hospitality').value = this.dataset.hospitality;
            document.getElementById('edit_emergency_leave').value = this.dataset.emergency;
        });
    });
});
</script>
@endsection
