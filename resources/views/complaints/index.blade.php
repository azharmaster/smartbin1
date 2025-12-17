@extends('layouts.app')
@section('content_title', 'Complaint')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                        <td>
                            @php
                                $status = $complaint->status ?? 'pending';
                                $badgeClass = match($status) {
                                    'completed' => 'bg-success',
                                    'in_progress' => 'bg-primary',
                                    'assigned' => 'bg-info', // new color
                                    'pending' => 'bg-warning',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>
                        <td>
                        @if($complaint->status === 'assigned' && $complaint->assignedStaff)
                            <span class="badge bg-success">{{ $complaint->assignedStaff->name }}</span>
                        @else
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" 
                                        id="assignTaskDropdown{{ $complaint->id }}" data-bs-toggle="dropdown" aria-expanded="false">
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
                        @endif
                    </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
    {{ $complaints->links('pagination::bootstrap-5') }}
</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    const target = e.target.closest('.assign-complaint');
    if (!target) return;

    e.preventDefault();

    const complaintId = target.dataset.complaintId;
    const staffId = target.dataset.staffId;

    const dropdownBtn = document.getElementById('assignTaskDropdown' + complaintId);
    dropdownBtn.disabled = true;

    fetch(`/complaints/${complaintId}/assign`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ staff_id: staffId })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            Swal.fire({
                icon: 'success',
                title: 'Assigned!',
                text: `Complaint assigned to ${data.assigned_to}`,
                timer: 2000,
                showConfirmButton: false
            });

            // Replace dropdown with assigned staff badge
            const row = document.getElementById('complaintRow' + complaintId);
            row.querySelector('td:last-child').innerHTML = `<span class="badge bg-success">${data.assigned_to}</span>`;

            // Update status badge
            const statusBadge = row.querySelector('td:nth-child(5) .badge');
            statusBadge.className = 'badge bg-info';
            statusBadge.textContent = 'Assigned';
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: data.message || 'This complaint is already assigned'
            });
            dropdownBtn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong'
        });
        dropdownBtn.disabled = false;
    });
});
</script>
@endsection
