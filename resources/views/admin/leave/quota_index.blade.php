<!-- Add Leave Quota Modal -->
<div class="modal fade" id="addQuotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.leave.quota.store') }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Leave Quota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label>User</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                </div>

                <div class="mb-3">
                    <label>Annual Leave</label>
                    <input type="number" name="annual_leave" class="form-control" min="0" value="0">
                </div>

                <div class="mb-3">
                    <label>MC</label>
                    <input type="number" name="mc" class="form-control" min="0" value="0">
                </div>

                <div class="mb-3">
                    <label>Hospitality Leave</label>
                    <input type="number" name="hospitality" class="form-control" min="0" value="0">
                </div>

                <div class="mb-3">
                    <label>Emergency Leave</label>
                    <input type="number" name="emergency_leave" class="form-control" min="0" value="0">
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>

        </form>
    </div>
</div>
