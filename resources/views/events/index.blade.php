@extends('layouts.app')
@section('content_title', 'Data Event')
@section('content')

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h5 class="mb-0">Events List</h5>
        <div class="ms-auto">
            <!-- Add Event Button -->
            <button class="btn btn-primary" data-toggle="modal" data-target="#createEventModal">
                <i class="fas fa-plus"></i> Add Event
            </button>
        </div>
    </div>

    <div class="card-body">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- EVENTS TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
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
                            <!-- VIEW -->
                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#showEventModal{{ $event->id }}">
                                <i class="far fa-eye"></i>
                            </button>

                            <!-- EDIT -->
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
                        </td>
                    </tr>

                    {{-- SHOW MODAL --}}
                    <div class="modal fade" id="showEventModal{{ $event->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            @include('events.show', ['event' => $event])
                        </div>
                    </div>

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

@endsection
