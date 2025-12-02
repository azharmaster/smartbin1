@extends('layouts.app')
@section('content_title', 'Attendance Records')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">All Users Attendance</h4>
    </div>

    <div class="card-body">
        @if ($attendances->isEmpty())
            <div class="alert alert-info text-center">
                No attendance records yet.
            </div>
        @endif

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $index => $a)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $a->user->name ?? '-' }}</td>
                            <td>{{ $a->clock_in ?? '-' }}</td>
                            <td>{{ $a->clock_out ?? '-' }}</td>
                            <td>{{ $a->date }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
