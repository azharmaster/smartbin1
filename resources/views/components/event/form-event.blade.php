@props(['id' => null, 'event_name' => '', 'pic_phone' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'start_time' => '', 'end_time' => ''])

<!-- Modal -->
<div class="modal fade" id="formEventModal{{ $id ?? '' }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">{{ $id ? 'Edit Event' : 'Add Event' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ $id ? route('events.update', $id) : route('events.store') }}" method="POST">
                @csrf
                @if($id)
                    @method('PUT')
                @endif

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Event Name</label>
                            <input type="text" name="event_name" class="form-control" value="{{ old('event_name', $event_name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">PIC Phone</label>
                            <input type="text" name="pic_phone" class="form-control" value="{{ old('pic_phone', $pic_phone) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $location) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $start_date) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $end_date) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $start_time) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $end_time) }}" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">{{ $id ? 'Update' : 'Add Event' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
