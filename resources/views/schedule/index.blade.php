@extends('layouts.app')
@section('content_title', 'Schedule')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Schedules</h4>
    </div>
    <div class="card-body">

        <!-- Validation Errors -->
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <!-- Add Schedule Button -->
        <div class="d-flex justify-content-end mb-2">
            <x-schedule.form-schedule :users="$users" :floors="$floors" />
        </div>

        <!-- Schedule Table -->
        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Floor</th>
                        <th>Shift Time</th>
                        <th>Date</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $index => $schedule)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $schedule->user->name ?? '—' }}</td>
                        <td>{{ $schedule->floor->floor_name ?? '—' }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($schedule->start_shift)->format('H:i') }}
                            -
                            {{ \Carbon\Carbon::parse($schedule->end_shift)->format('H:i') }}
                        </td>
                        <td>{{ \Carbon\Carbon::parse($schedule->date)->format('Y-m-d') }}</td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">

                                <!-- Edit Modal Button -->
                                <x-schedule.form-schedule 
                                    :id="$schedule->id" 
                                    :users="$users" 
                                    :floors="$floors" />

                                <!-- Delete Button -->
                                <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this schedule?')">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($schedules->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center">No schedules found.</td>
                    </tr>
                    @endif

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
