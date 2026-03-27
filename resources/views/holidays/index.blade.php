@extends($layout)
@section('content_title', 'Holidays & Events')
@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#holidaysHelpModal" style="
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #faa70c;
        color: #fff;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        z-index: 999;
    "
    title="Holidays & Events Guide"
>
    ?
</button>

<div class="row row-equal-height">
    <!-- HOLIDAYS LEFT COLUMN -->
    <div class="col-md-6">
        <div class="card card-success card-outline mb-4 flex-fill">
            <div class="card-header d-flex align-items-center">
                 <p class="mb-0"><i class="fas fa-table"></i> Holiday List</p>
                <div class="ml-auto">
                    @if(auth()->user()->role == 1)
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                        <i class="fas fa-plus"></i> Add Holiday
                    </button>
                    @endif
                </div>
            </div>

            <div class="card-body scrollable">

                <!-- Validation Errors -->
                @if ($errors->any())
                <div class="alert alert-danger d-flex flex-column mb-3">
                    @foreach ($errors->all() as $error)
                        <small class="text-white my-1">{{ $error }}</small>
                    @endforeach
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped dataTable dtr-inline datatable-buttons datatable" id="holidayTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Dates</th>
                                <th>Option</th>
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
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button
                                                type="button"
                                                class="btn btn-info btn-sm summary-trigger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#itemSummaryModal"
                                                data-type="Holiday"
                                                data-name="{{ $holiday->name }}"
                                                data-start-date="{{ optional($holiday->start_date)->format('Y-m-d') }}"
                                                data-end-date="{{ optional($holiday->end_date)->format('Y-m-d') }}"
                                                data-status="{{ $holiday->is_active ? 'Active' : 'Inactive' }}"
                                                data-summary="{{ $holiday->end_date ? 'Holiday berlangsung dari ' . $holiday->start_date->format('Y-m-d') . ' hingga ' . $holiday->end_date->format('Y-m-d') . '.' : 'Holiday berlangsung pada ' . $holiday->start_date->format('Y-m-d') . '.' }}"
                                                title="View Summary"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(auth()->user()->role == 1)
                                            <!-- Edit Button triggers modal -->
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editHolidayModal{{ $holiday->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Delete -->
                                            <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endif

                                            <!-- Notification Toggle -->
                                            <form action="{{ route('holidays.toggle', $holiday->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-notification {{ $holiday->is_active ? 'btn-secondary' : 'btn-success' }}
                                                {{ auth()->user()->role != 1 ? 'disabled' : '' }}">
                                                    {{ $holiday->is_active ? 'OFF' : 'ON' }}
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
    </div>

    <!-- EVENTS RIGHT COLUMN -->
<div class="col-md-6">
    <div class="card card-success card-outline mb-4 flex-fill">
        <div class="card-header d-flex align-items-center">
             <p class="mb-0"><i class="fas fa-table"></i> Events List</p>
            <div class="ms-auto">
            @if(auth()->user()->role == 1)
                <!-- ✅ Bootstrap 4 syntax -->
                <button class="btn btn-primary" data-toggle="modal" data-target="#createEventModal">
                    <i class="fas fa-plus"></i> Add Event
                </button>
            @endif
            </div>
        </div>

        <div class="card-body scrollable">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- EVENTS TABLE --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped dataTable dtr-inline datatable-buttons datatable" id="eventsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Event Name</th>
                            <th>PIC Phone</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Option</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $index => $event)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $event->event_name }}</td>
                            <td>{{ $event->pic_phone }}</td>
                            <td>{{ $event->location }}</td>
                            <td>{{ \Carbon\Carbon::parse($event->start_date)->format('Y-m-d') }}</td>
                            <td>{{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('Y-m-d') : '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-info btn-sm summary-trigger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#itemSummaryModal"
                                        data-type="Event"
                                        data-name="{{ $event->event_name }}"
                                        data-start-date="{{ optional($event->start_date)->format('Y-m-d') }}"
                                        data-end-date="{{ optional($event->end_date)->format('Y-m-d') }}"
                                        data-status="{{ $event->is_active ? 'Active' : 'Inactive' }}"
                                        data-location="{{ $event->location }}"
                                        data-pic-phone="{{ $event->pic_phone }}"
                                        data-summary="{{ $event->end_date ? 'Event berlangsung dari ' . $event->start_date->format('Y-m-d') . ' hingga ' . $event->end_date->format('Y-m-d') . ' di ' . $event->location . '.' : 'Event berlangsung pada ' . $event->start_date->format('Y-m-d') . ' di ' . $event->location . '.' }}"
                                        title="View Summary"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <!-- EDIT -->
                                    @if(auth()->user()->role == 1)
                                    <!-- ✅ Bootstrap 4 syntax -->
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editEventModal{{ $event->id }}">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    <!-- DELETE -->
                                    <form action="{{ route('events.destroy', $event->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <!-- Notification Toggle -->
                                    <form action="{{ route('events.toggle', $event->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-notification {{ $event->is_active ? 'btn-secondary' : 'btn-success' }}
                                            {{ auth()->user()->role != 1 ? 'disabled' : '' }}">
                                            {{ $event->is_active ? 'OFF' : 'ON' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- EDIT MODAL --}}
                        <div class="modal fade" id="editEventModal{{ $event->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                @include('events.edit', ['event' => $event])
                            </div>
                        </div>

                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog">
            @include('events.create')
        </div>
    </div>
</div>

<div class="modal fade" id="itemSummaryModal" tabindex="-1" aria-labelledby="itemSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemSummaryModalLabel">Holiday / Event Summary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="mb-2"><strong>Type:</strong> <span id="summaryType">-</span></div>
                <div class="mb-2"><strong>Name:</strong> <span id="summaryName">-</span></div>
                <div class="mb-2"><strong>Start Date:</strong> <span id="summaryStartDate">-</span></div>
                <div class="mb-2"><strong>End Date:</strong> <span id="summaryEndDate">-</span></div>
            </div>
            <div class="col-md-6">
                <div class="mb-2 summary-location-row d-none"><strong>Location:</strong> <span id="summaryLocation">-</span></div>
                <div class="mb-2 summary-pic-row d-none"><strong>PIC Phone:</strong> <span id="summaryPicPhone">-</span></div>
                <div class="mb-2"><strong>Status:</strong> <span id="summaryStatus">-</span></div>
                <div class="mb-2"><strong>Summary:</strong> <span id="summaryText" class="text-muted">-</span></div>
            </div>
        </div>

        <div id="summaryLoading" class="text-center py-4 d-none">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted">Loading bin summary...</div>
        </div>

        <div id="summaryError" class="alert alert-danger d-none mb-3"></div>

        <div id="summaryContent" class="d-none">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Number of Times Each Bin Became Full</div>
                            <div class="h4 mb-0" id="metricTotalFullEvents">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Average Time for Bin to Become Full (Hours)</div>
                            <div class="h4 mb-0" id="metricAvgFillTime">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Average Bin Clear Time (Hours)</div>
                            <div class="h4 mb-0" id="metricAvgClearTime">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Cleaning History / Active Bins</div>
                            <div class="h4 mb-0"><span id="metricTotalCleaning">0</span> / <span id="metricActiveBins">0</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">Top Bins During This Period</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bin</th>
                                            <th>Times Full</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topBinsTableBody">
                                        <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">Cleaning History</div>
                        <div class="card-body p-0" style="max-height: 260px; overflow-y: auto;">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bin</th>
                                            <th>Device</th>
                                            <th>Cleaned At</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cleaningHistoryTableBody">
                                        <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Bin Analytics</div>
                <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Bin</th>
                                    <th>Times Full</th>
                                    <th>Avg Fill Time (Hours)</th>
                                    <th>Avg Clear Time (Hours)</th>
                                </tr>
                            </thead>
                            <tbody id="binAnalyticsTableBody">
                                <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Holidays & Events Help Modal -->
<div class="modal fade" id="holidaysHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Holidays & Events – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-tools"></i> Purpose</h6>
        <p>
          This page allows you to manage <strong>holidays</strong> and <strong>events</strong> in the system.
          You can add, edit, delete, and toggle notifications for each item.
        </p>

        <hr>

        <h6><i class="fas fa-calendar-day"></i> Holidays Section (Left)</h6>
        <ul>
          <li>View all holidays in a table with start and end dates.</li>
          <li>Use the <strong>+</strong> button to add a new holiday.</li>
          <li>Click the <strong>Edit</strong> button to update an existing holiday.</li>
          <li>Use <strong>Delete</strong> to remove a holiday.</li>
          <li>Toggle <strong>ON/OFF</strong> to enable or disable holiday notifications.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-calendar-alt"></i> Events Section (Right)</h6>
        <ul>
          <li>View all events in a table with details such as PIC phone and location.</li>
          <li>Use the <strong>+</strong> button to add a new event.</li>
          <li>Click the <strong>Edit</strong> button to update an existing event.</li>
          <li>Use <strong>Delete</strong> to remove an event.</li>
          <li>Toggle <strong>ON/OFF</strong> to enable or disable event notifications.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>Changes are applied immediately when toggling ON/OFF.</li>
          <li>Always ensure correct dates and active status when adding or editing.</li>
          <li>This guide helps users navigate and manage holidays and events efficiently.</li>
        </ul>

      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Initialize Holiday Table
    if (! $.fn.DataTable.isDataTable('#holidayTable')) {
        $('#holidayTable').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: true,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
            destroy: true
        }).buttons().container().appendTo('#holidayTable_wrapper .col-md-6:eq(0)');
    }

    // Initialize Events Table
    if (! $.fn.DataTable.isDataTable('#eventsTable')) {
        $('#eventsTable').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: true,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
            destroy: true
        }).buttons().container().appendTo('#eventsTable_wrapper .col-md-6:eq(0)');
    }

    function renderRows(items, emptyColspan, renderer, targetSelector) {
        if (!items || items.length === 0) {
            $(targetSelector).html(`<tr><td colspan="${emptyColspan}" class="text-center text-muted">No data found for this period.</td></tr>`);
            return;
        }

        $(targetSelector).html(items.map(renderer).join(''));
    }

    $(document).on('click', '.summary-trigger', function () {
        const button = $(this);
        const type = button.data('type') || '-';
        const isEvent = type === 'Event';
        const startDate = button.data('start-date') || '-';
        const endDate = button.data('end-date') || startDate;

        $('#summaryType').text(type);
        $('#summaryName').text(button.data('name') || '-');
        $('#summaryStartDate').text(startDate);
        $('#summaryEndDate').text(endDate);
        $('#summaryLocation').text(button.data('location') || '-');
        $('#summaryPicPhone').text(button.data('pic-phone') || '-');
        $('#summaryStatus').text(button.data('status') || '-');
        $('#summaryText').text(button.data('summary') || '-');

        $('.summary-location-row').toggleClass('d-none', !isEvent);
        $('.summary-pic-row').toggleClass('d-none', !isEvent);

        $('#summaryError').addClass('d-none').text('');
        $('#summaryContent').addClass('d-none');
        $('#summaryLoading').removeClass('d-none');

        $.ajax({
            url: '{{ route('holidays.binSummary') }}',
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function (response) {
                $('#metricTotalFullEvents').text(response.summary_metrics.total_full_events ?? 0);
                $('#metricAvgFillTime').text(response.summary_metrics.avg_fill_time ?? 0);
                $('#metricAvgClearTime').text(response.summary_metrics.avg_clear_time ?? 0);
                $('#metricTotalCleaning').text(response.summary_metrics.total_cleaning ?? 0);
                $('#metricActiveBins').text(response.summary_metrics.total_active_bins ?? 0);

                renderRows(
                    response.top_bins,
                    2,
                    function (item) {
                        return `<tr><td>${item.asset_name}</td><td>${item.times_full}</td></tr>`;
                    },
                    '#topBinsTableBody'
                );

                renderRows(
                    response.cleaning_logs,
                    3,
                    function (item) {
                        return `<tr><td>${item.asset_name}</td><td>${item.device_name}</td><td>${item.cleaned_at}</td></tr>`;
                    },
                    '#cleaningHistoryTableBody'
                );

                renderRows(
                    response.bin_analytics,
                    4,
                    function (item) {
                        return `<tr>
                            <td>${item.asset_name}</td>
                            <td>${item.times_full}</td>
                            <td>${item.avg_fill_time}</td>
                            <td>${item.avg_clear_time}</td>
                        </tr>`;
                    },
                    '#binAnalyticsTableBody'
                );

                $('#summaryLoading').addClass('d-none');
                $('#summaryContent').removeClass('d-none');
            },
            error: function () {
                $('#summaryLoading').addClass('d-none');
                $('#summaryError').removeClass('d-none').text('Failed to load bin summary for the selected holiday/event period.');
            }
        });
    });
});
</script>
@endpush
