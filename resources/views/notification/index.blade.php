@extends('layouts.app')
@section('content_title', 'Notification Logs')
@section('content')

<div class="container-fluid">

   {{-- Filter Section --}}
<div class="mb-3">
    <form action="{{ route('notifications.index') }}" method="GET"
          class="d-flex flex-wrap gap-2 align-items-end">

        {{-- Period Filter --}}
        <div>
            <label class="form-label small mb-1">Quick Filter</label>
            <select name="filter" class="form-select form-select-sm">
                <option value="">-- Select Period --</option>
                <option value="day" {{ request('filter') == 'day' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>This Year</option>
            </select>
        </div>

        {{-- From Date --}}
        <div>
            <label class="form-label small mb-1">From</label>
            <input type="date"
                   name="from_date"
                   class="form-control form-control-sm"
                   value="{{ request('from_date') }}">
        </div>

        {{-- To Date --}}
        <div>
            <label class="form-label small mb-1">To</label>
            <input type="date"
                   name="to_date"
                   class="form-control form-control-sm"
                   value="{{ request('to_date') }}">
        </div>

        {{-- Buttons --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-success">
                Filter
            </button>

            @if(request()->hasAny(['filter','from_date','to_date']))
                <a href="{{ route('notifications.index') }}"
                   class="btn btn-sm btn-secondary">
                    Clear
                </a>
            @endif
        </div>

    </form>
</div>


    {{-- Notifications Table --}}
    <div class="card card-success card-outline mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-inbox me-1"></i> All Notifications
            </h5>
        </div>

        <div class="card-body p-0">
            @if($notifications->count())
                <div class="table-responsive notification-table">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Channel</th>
                                <th>Message Preview</th>
                                <th class="text-center">Sent At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($notifications as $log)
                                <tr>
                                    <td>{{ $loop->iteration + ($notifications->currentPage()-1) * $notifications->perPage() }}</td>
                                    <td class="text-nowrap">{{ ucfirst($log->channel) }}</td>
                                    <td class="message-preview">
                                        {{ $log->message_preview }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        {{ $log->sent_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#message{{ $log->id }}">
                                            View
                                        </button>
                                    </td>
                                </tr>

                                <tr class="collapse" id="message{{ $log->id }}">
                                    <td colspan="5" class="bg-light">
                                        <pre class="mb-0">{{ $log->message_full }}</pre>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-3">
                    No notifications found for this period.
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $notifications->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

{{-- User Page Matching Style + Responsive Fix --}}
<style>
/* Card style same as User page */
.card-success.card-outline {
    border-top: 3px solid #28a745;
    border-radius: 6px;
}

/* Hover effect */
.table-hover tbody tr:hover {
    background-color: #f6fbf8;
}

/* Message formatting */
pre {
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.85rem;
}

/* Keep important columns visible */
.text-nowrap {
    white-space: nowrap;
}

/* Responsive fix */
@media (max-width: 768px) {
    .notification-table {
        overflow-x: auto;
    }

    table {
        min-width: 700px;
    }

    th, td {
        font-size: 0.85rem;
        padding: 0.5rem;
    }

    .message-preview {
        min-width: 250px;
    }
}
</style>

@endsection
