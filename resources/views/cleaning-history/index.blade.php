@extends('layouts.app')
@section('content_title', 'Cleaning History')

@section('content')

<style>
.filter-card {
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    align-items: flex-end;
    gap: 15px;
    flex-wrap: wrap;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-size: 13px;
    font-weight: 500;
    color: #555;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 20px;
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #1f6423;
}

.stat-label {
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

.table-container {
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.table-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead {
    background: #1f6423;
    color: white;
}

.table th,
.table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}

.empty-state i {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 15px;
}

.badge-success {
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

/* Pagination */
.pagination {
    justify-content: center;
    margin: 0;
}

.pagination .page-item .page-link {
    color: #1f6423;
    border: 1px solid #dee2e6;
    margin: 0 2px;
    border-radius: 4px;
}

.pagination .page-item.active .page-link {
    background: #1f6423;
    border-color: #1f6423;
    color: white;
}

.pagination .page-item:hover .page-link {
    background: #f8f9fa;
    color: #1f6423;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background: white;
}
</style>

<div class="container-fluid">
    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('cleaning-history.index') }}" class="filter-form">
            <div class="form-group">
                <label for="date_from">Date From:</label>
                <input type="date" id="date_from" name="date_from" class="form-control"
                       value="{{ $dateFrom }}" max="{{ date('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label for="date_to">Date To:</label>
                <input type="date" id="date_to" name="date_to" class="form-control"
                       value="{{ $dateTo }}" max="{{ date('Y-m-d') }}">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>

            <div class="form-group">
                <a href="{{ route('cleaning-history.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>

            <div class="form-group">
                <a href="{{ route('cleaning-history.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number">{{ $cleaningLogs->count() }}</div>
            <div class="stat-label">Total Cleaning Events</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $cleaningLogs->unique('asset_name')->count() }}</div>
            <div class="stat-label">Unique Bins Cleaned</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1 }}</div>
            <div class="stat-label">Days in Range</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <span class="table-title">
                <i class="fas fa-history"></i> Cleaning History
                ({{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }})
            </span>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Asset Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Ago</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cleaningLogs as $index => $log)
                        <tr>
                            <td>{{ $cleaningLogs->firstItem() + $index }}</td>
                            <td><strong>{{ $log->asset_name }}</strong></td>
                            <td>{{ $log->cleaned_at->format('d/m/Y') }}</td>
                            <td>{{ $log->cleaned_at->format('h:i A') }}</td>
                            <td>{{ $log->cleaned_at->diffForHumans() }}</td>
                            <td><span class="badge-success">Cleaned</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-broom"></i>
                                <p>No cleaning records found for the selected date range.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-3">
            {{ $cleaningLogs->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Auto-submit when date changes (optional)
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('change', function() {
        // Optional: auto-submit on date change
        // this.form.submit();
    });
});
</script>
@endsection
