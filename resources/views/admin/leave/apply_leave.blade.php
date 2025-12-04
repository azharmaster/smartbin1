@extends('layouts.app')
@section('content_title', 'Apply Leave')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Apply Leave (Admin)</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.leave.apply.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>User</label>
                <select name="user_id" class="form-control" required>
                    @foreach($users as $user)
                        @php
                            // Check if quotas were passed, otherwise null
                            $quota = $quotas[$user->id] ?? null;
                        @endphp
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="halfday">Half-day</option>
                    <option value="fullday">Full-day</option>
                </select>
            </div>

            <div class="form-group">
                <label>From</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Until</label>
                <input type="date" name="end_date" class="form-control">
            </div>

            <div class="form-group">
                <label>Use</label>
                <select name="use" class="form-control" required>
                    @php
                        $leaveTypes = [
                            'mc' => 'MC',
                            'annual_leave' => 'Annual Leave',
                            'hospitality' => 'Hospitality',
                            'emergency_leave' => 'Emergency Leave',
                        ];
                    @endphp

                    @foreach($leaveTypes as $key => $label)
                        @php
                            // Calculate remaining quota if $quota exists
                            if (isset($quota)) {
                                $used = $quota->$key ?? 0;
                                $remaining = max(0, ($quota->$key ?? 0) - $used);
                            } else {
                                $remaining = 0;
                            }
                        @endphp

                        <option value="{{ $key }}" {{ $remaining <= 0 ? 'disabled' : '' }}>
                            {{ $label }} ({{ $remaining }}/{{ $quota->$key ?? 0 }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" class="form-control" rows="3" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Leave</button>
        </form>
    </div>
</div>

@endsection
