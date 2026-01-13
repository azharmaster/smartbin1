@extends($layout)
@section('content_title', 'Holidays')
@section('content')

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h5 class="mb-0">Holiday List</h5>
        <div class="ms-auto">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fas fa-plus"></i> Add Holiday
            </button>
        </div>
    </div>

    <div class="card-body">

        <!-- Validation Errors -->
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column mb-3">
            @foreach ($errors->all() as $error)
                <small class="text-white my-1">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped dataTable dtr-inline datatable-buttons datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($holidays as $index => $holiday)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $holiday->name }}</td>
                            <td>
                                @if($holiday->end_date)
                                    {{ \Carbon\Carbon::parse($holiday->start_date)->format('Y-m-d') }}
                                    &rarr;
                                    {{ \Carbon\Carbon::parse($holiday->end_date)->format('Y-m-d') }}
                                @else
                                    {{ \Carbon\Carbon::parse($holiday->start_date)->format('Y-m-d') }}
                                @endif
                            </td>
                            <td>{{ $holiday->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <!-- Edit Button triggers modal -->
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editHolidayModal{{ $holiday->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>&nbsp;

                                    <!-- Delete -->
                                    <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editHolidayModal{{ $holiday->id }}" tabindex="-1" aria-labelledby="editHolidayModalLabel{{ $holiday->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('holidays.update', $holiday->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editHolidayModalLabel{{ $holiday->id }}">Edit Holiday</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name{{ $holiday->id }}" class="form-label">Holiday Name</label>
                                                <input type="text" class="form-control" id="name{{ $holiday->id }}" name="name" value="{{ $holiday->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="start_date{{ $holiday->id }}" class="form-label">Start Date</label>
                                                <input type="date" class="form-control" id="start_date{{ $holiday->id }}" name="start_date" value="{{ $holiday->start_date }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="end_date{{ $holiday->id }}" class="form-label">End Date</label>
                                                <input type="date" class="form-control" id="end_date{{ $holiday->id }}" name="end_date" value="{{ $holiday->end_date }}">
                                                <small class="text-muted">Leave empty if single-day holiday</small>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="is_active{{ $holiday->id }}" name="is_active" value="1" {{ $holiday->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active{{ $holiday->id }}">Active</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addHolidayModalLabel">Add Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Holiday Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                        <small class="text-muted">Leave empty if single-day holiday</small>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Holiday</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#holidayTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: true,
        buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#holidayTable_wrapper .col-md-6:eq(0)');
});
</script>
@endpush
