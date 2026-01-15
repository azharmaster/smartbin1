<div class="modal-content">
    <form action="{{ route('events.store') }}" method="POST">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Add Event</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="event_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>PIC Phone</label>
                <input type="text" name="pic_phone" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
    </form>
</div>
