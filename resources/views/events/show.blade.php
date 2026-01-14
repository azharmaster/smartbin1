<div class="modal-content">
    {{-- Modal Header --}}
    <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Event Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    {{-- Modal Body --}}
    <div class="modal-body">
        <div class="mb-3 d-flex">
            <strong style="width: 120px;">Event Name:</strong>
            <span>{{ $event->event_name }}</span>
        </div>

        <div class="mb-3 d-flex">
            <strong style="width: 120px;">Location:</strong>
            <span>{{ $event->location }}</span>
        </div>

        <div class="mb-3 d-flex">
            <strong style="width: 120px;">PIC Phone:</strong>
            <span>{{ $event->pic_phone }}</span>
        </div>

        <div class="mb-3 d-flex">
            <strong style="width: 120px;">Start Date:</strong>
            <span>{{ $event->start_date->format('Y-m-d') }}</span>
        </div>

        <div class="mb-3 d-flex">
            <strong style="width: 120px;">End Date:</strong>
            <span>{{ $event->end_date->format('Y-m-d') }}</span>
        </div>

        <div class="mb-3 d-flex">
            <strong style="width: 120px;">Start Time:</strong>
            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}</span>
        </div>

        <div class="mb-3 d-flex">
            <strong style="width: 120px;">End Time:</strong>
            <span>{{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}</span>
        </div>
    </div>

    {{-- Modal Footer --}}
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
    </div>
</div>
