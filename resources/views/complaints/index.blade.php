@extends('layouts.app')
@section('content_title', 'Complaint')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Complaint</h4>
    </div>
    <div class="card-body">

        {{-- Success Message Container --}}
        <div id="successMessage" class="alert alert-success d-none"></div>

        {{-- Display Errors --}}
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-end mb-2">
            <x-complaint.form-complaint :assets="$assets" />
        </div>

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset Name</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($complaints as $index => $complaint)
                    <tr id="complaintRow{{ $complaint->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $complaint->asset->asset_name ?? '-' }}</td>
                        <td>{{ $complaint->title }}</td>
                        <td>{{ $complaint->description }}</td>
                        <td>
                            @php
                                $status = $complaint->status ?? 'pending';
                            @endphp
                            <span class="badge 
                                {{ $status === 'completed' ? 'bg-success' : ($status === 'in_progress' ? 'bg-info' : 'bg-warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="assignTaskDropdown{{ $complaint->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-tasks"></i> Assign Task
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="assignTaskDropdown{{ $complaint->id }}">
                                    @foreach($staffs as $staff)
                                    <li>
                                        <a href="#" class="dropdown-item assign-complaint" 
                                           data-complaint-id="{{ $complaint->id }}" 
                                           data-staff-id="{{ $staff->id }}">
                                            {{ $staff->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $complaints->links() }}
            </div>
        </div>
    </div>
</div>

{{-- AJAX Script --}}
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = "{{ csrf_token() }}";

    document.querySelectorAll('.assign-complaint').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();

            if (!confirm('Assign this complaint to this staff?')) return;

            const complaintId = this.dataset.complaintId;
            const staffId = this.dataset.staffId;

            fetch("{{ route('staff.tasks.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    complaint_id: complaintId,
                    staff_id: staffId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const successMessage = document.getElementById('successMessage');
                    successMessage.textContent = data.success;
                    successMessage.classList.remove('d-none');
                    setTimeout(() => { successMessage.classList.add('d-none'); }, 3000);
                }
            })
            .catch(error => {
                alert('Error assigning task. Try again.');
                console.error(error);
            });
        });
    });
});
</script>
@endsection

@endsection
