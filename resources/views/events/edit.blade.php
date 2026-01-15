<div class="modal-content">
    <form action="{{ route('events.update', $event->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-header">
            <h5 class="modal-title">Edit Event</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">
            {{-- Event Name --}}
            <div class="form-group mb-3">
                <label for="event_name_{{ $event->id }}">Event Name</label>
                <input type="text" id="event_name_{{ $event->id }}" name="event_name" value="{{ $event->event_name }}" class="form-control" required>
            </div>

            {{-- PIC Phone --}}
            <div class="form-group mb-3">
                <label for="pic_phone_{{ $event->id }}">PIC Phone</label>
                <input type="text" id="pic_phone_{{ $event->id }}" name="pic_phone" value="{{ $event->pic_phone }}" class="form-control" required>
            </div>

            {{-- Location --}}
            <div class="form-group mb-3">
                <label for="location_{{ $event->id }}">Location</label>
                <input type="text" id="location_{{ $event->id }}" name="location" value="{{ $event->location }}" class="form-control" required>
            </div>

            {{-- Start Date & End Date --}}
            <div class="form-row mb-3">
                <div class="col">
                    <label for="start_date_{{ $event->id }}">Start Date</label>
                    <input type="date" id="start_date_{{ $event->id }}" name="start_date" value="{{ $event->start_date }}" class="form-control" required>
                </div>
                <div class="col">
                    <label for="end_date_{{ $event->id }}">End Date</label>
                    <input type="date" id="end_date_{{ $event->id }}" name="end_date" value="{{ $event->end_date }}" class="form-control">
                </div>
            </div>

            {{-- Removed Start Time & End Time --}}
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Event</button>
        </div>
    </form>
</div>
