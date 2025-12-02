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
        <x-complaint.form-complaint 
            :assets="$assets" />
        </div>

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset Name</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Option</th>
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
                            <div class="d-flex align-items-center justify-content-center">
                                <x-complaint.form-complaint 
                                    :id="$complaint->id"
                                    :title="$complaint->title"
                                    :description="$complaint->description"
                                    :asset_id="$complaint->asset_id"
                                    :assets="$assets" />&nbsp;
                                <form action="{{ route('complaints.destroy', $complaint->id) }}" method="POST" 
                                    onsubmit="return confirm('Are you sure you want to delete this complaint?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm-delete="true">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
