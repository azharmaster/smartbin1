@extends('layouts.app')
@section('content_title', 'WhatsApp Notification')
@section('content')

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <div class="card card-success card-outline">
            <div class="card-header text-center">
                <h5 class="mb-0 fw-bold">Full Bin Notification</h5>
                <small class="text-muted">Manage WhatsApp alert settings</small>
            </div>

            <div class="card-body">

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success text-center">
                        {{ session('success') }}
                    </div>
                @endif

                @if(isset($notification))

                <form action="{{ route('whatsapp.update', $notification->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Notification -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Notification</label>
                        <div class="form-control-plaintext fw-semibold">
                            Full Bin Alert
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Current Status</label><br>
                        @if($notification->is_active)
                            <span class="badge bg-success px-3 py-2">ON</span>
                        @else
                            <span class="badge bg-danger px-3 py-2">OFF</span>
                        @endif
                    </div>

                    <hr>

                    <!-- Start Date -->
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" 
                               name="start_date" 
                               class="form-control"
                               value="{{ old('start_date', $notification->start_date ? $notification->start_date->format('Y-m-d') : '') }}">
                    </div>

                    <!-- End Date -->
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" 
                               name="end_date" 
                               class="form-control"
                               value="{{ old('end_date', $notification->end_date ? $notification->end_date->format('Y-m-d') : '') }}">
                    </div>

                    <!-- Start Time -->
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" 
                               name="start_time" 
                               class="form-control"
                               value="{{ old('start_time', $notification->start_time ?? '') }}">
                    </div>

                    <!-- End Time -->
                    <div class="mb-4">
                        <label class="form-label">End Time</label>
                        <input type="time" 
                               name="end_time" 
                               class="form-control"
                               value="{{ old('end_time', $notification->end_time ?? '') }}">
                    </div>

                    <!-- ON / OFF Switch -->
                    <div class="mb-4 d-flex align-items-center justify-content-between p-3 border rounded bg-light">
                        <span class="fw-semibold">Notification Status</span>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">OFF</span>
                            <div class="form-check form-switch m-0">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="is_active" 
                                    value="1"
                                    {{ $notification->is_active ? 'checked' : '' }}>
                            </div>
                            <span class="text-muted small">ON</span>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>

                </form>

                @else
                    <div class="alert alert-warning text-center">
                        No notification settings found.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection
