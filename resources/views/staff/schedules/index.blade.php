@extends('layouts.staffapp')
@section('content_title', 'My Schedule')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">My Schedule</h4>
    </div>

    <div class="card-body">
        <table id="table1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Floor</th>
                    <th>Shift</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $s)
                    <tr>
                        <td>{{ $s->floor->floor_name ?? '-' }}</td>
                        <td>{{ $s->start_shift }} - {{ $s->end_shift }}</td>
                        <td>{{ $s->date }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No schedule assigned yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
