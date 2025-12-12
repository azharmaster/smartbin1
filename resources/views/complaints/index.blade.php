@extends('layouts.app')
@section('content_title', 'Complaint')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Complaint</h4>
    </div>
    <div class="card-body">
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
                    <tr>
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
                                        <form action="{{ route('staff.tasks.store') }}" method="POST" class="m-0 p-0">
                                            @csrf
                                            <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
                                            <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                            <button type="submit" class="dropdown-item">{{ $staff->name }}</button>
                                        </form>
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
@endsection
