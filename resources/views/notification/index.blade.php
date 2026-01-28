@extends('layouts.app') {{-- your admin layout --}}
@section('content_title', 'Notification Logs')
@section('content')

<div class="container-fluid">

    {{-- Filter Section --}}
    <div class="mb-3 d-flex gap-2 flex-wrap align-items-center">
        <form action="{{ route('notifications.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
            <select name="filter" class="form-select form-select-sm">
                <option value="">-- Select Period --</option>
                <option value="day" {{ request('filter') == 'day' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>This Year</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </form>

        @if(request('filter'))
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-secondary">Clear Filter</a>
        @endif
    </div>

    {{-- Notification Timeline --}}
    <div class="card mb-4">
        <div class="card-header smartbin-gradient text-white">
            <h5 class="mb-0">
                <i class="fas fa-inbox"></i> All Notifications
                <span class="badge badge-light">{{ $notifications->total() }}</span>
            </h5>
        </div>

        <div class="card-body p-3">
            <div class="notification-timeline">
                @forelse($notifications as $log)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>

                        <div class="timeline-content">
                            <button
                                class="timeline-button btn btn-link p-0"
                                data-bs-toggle="collapse"
                                data-bs-target="#notif{{ $log->id }}">
                                <i class="fas fa-history"></i> {{ $log->sent_at->format('Y-m-d H:i:s') }}
                            </button>

                            <div id="notif{{ $log->id }}" class="collapse mt-2">
                                <pre class="mb-0 text-sm">{{ $log->message }}</pre>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-3">
                        No notifications found for this period.
                    </div>
                @endforelse
            </div>
        </div>

        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Optional styling for timeline --}}
<style>
.notification-timeline {
    position: relative;
    padding-left: 20px;
    border-left: 2px solid #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-dot {
    position: absolute;
    left: -7px;
    top: 5px;
    width: 14px;
    height: 14px;
    background-color: #17a2b8;
    border-radius: 50%;
}

.timeline-content {
    margin-left: 10px;
}

.timeline-button {
    background: none;
    border: none;
    color: #0d6efd;
    cursor: pointer;
    font-weight: 500;
    padding: 0;
}

.timeline-button:hover {
    text-decoration: underline;
}
</style>

@endsection
