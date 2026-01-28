@extends('layouts.app')
@section('content_title', 'Notification Logs')
@section('content')

<div class="container-fluid">

    {{-- Filter Section --}}
    <div class="mb-3 d-flex gap-2 flex-wrap align-items-center">
        <form action="{{ route('notifications.index') }}" method="GET" class="d-flex gap-2 align-items-center">
            <select name="filter" class="form-select form-select-sm">
                <option value="">-- Select Period --</option>
                <option value="day" {{ request('filter') == 'day' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>This Year</option>
            </select>

            <button type="submit" class="btn btn-sm btn-success">Filter</button>

            @if(request('filter'))
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-secondary ms-auto">
                    Clear
                </a>
            @endif
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
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-success">
                            <tr>
                                <th style="width:50px;">#</th>
                                <th style="width:120px;">Channel</th>
                                <th>Message Preview</th>
                                <th style="width:180px;" class="text-center">Sent At</th>
                                <th style="width:130px;" class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($notifications as $log)
                                {{-- Main Row --}}
                                <tr>
                                    <td>{{ $loop->iteration + ($notifications->currentPage()-1) * $notifications->perPage() }}</td>
                                    <td>{{ ucfirst($log->channel) }}</td>
                                    <td style="white-space: normal; word-break: break-word;">
                                        {{ $log->message_preview }}
                                    </td>
                                    <td class="text-center">
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

                                {{-- Expanded Message --}}
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

{{-- User Page Matching Style --}}
<style>
.card-success.card-outline {
    border-top: 3px solid #28a745;
    border-radius: 6px;
}

.table-hover tbody tr:hover {
    background-color: #f6fbf8;
}

pre {
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.85rem;
}
</style>

@endsection
