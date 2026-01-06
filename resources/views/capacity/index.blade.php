@extends('layouts.app')
@section('content_title', 'Set Capacity')
@section('content')

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <div class="card card-success card-outline">
            <div class="card-header text-center">
                <h5 class="mb-0 fw-bold">Bin Capacity Settings</h5>
                <small class="text-muted">Configure Empty, Half-Full & Full thresholds</small>
            </div>

            <div class="card-body">

                {{-- Success alert --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(isset($capacity))

                <form action="{{ route('capacity.update', $capacity->id) }}" method="POST" id="capacityForm">
                    @csrf
                    @method('PUT')

                    <!-- EMPTY -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Set Empty Range</label>
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <input type="number" class="form-control" value="1" readonly>
                            </div>
                            <div class="col">
                                <input type="number" 
                                       name="empty_to" 
                                       id="empty_to"
                                       class="form-control"
                                       min="1" max="99"
                                       value="{{ $capacity->empty_to }}">
                            </div>
                        </div>
                        <small class="text-muted">Example: 1 - 39</small>
                    </div>

                    <!-- HALF FULL -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Set Half-Full Range</label>
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <input type="number" 
                                       id="half_from"
                                       class="form-control"
                                       value="{{ $capacity->empty_to + 1 }}" 
                                       readonly>
                            </div>
                            <div class="col">
                                <input type="number" 
                                       name="half_to" 
                                       id="half_to"
                                       class="form-control"
                                       min="1" max="99"
                                       value="{{ $capacity->half_to }}">
                            </div>
                        </div>
                        <small class="text-muted">Example: 40 - 79</small>
                    </div>

                    <!-- FULL -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Set Full Range</label>
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <input type="number" 
                                       id="full_from"
                                       class="form-control"
                                       value="{{ $capacity->half_to + 1 }}" 
                                       readonly>
                            </div>
                            <div class="col">
                                <input type="number" 
                                       id="full_to"
                                       class="form-control"
                                       value="100" readonly>
                            </div>
                        </div>
                        <small class="text-muted">Example: 80 - 100</small>
                    </div>

                    <!-- Save Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> Save Capacity Settings
                        </button>
                    </div>

                </form>

                @else
                    <div class="alert alert-warning text-center">
                        No capacity configuration found.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emptyToInput = document.getElementById('empty_to');
    const halfFromInput = document.getElementById('half_from');
    const halfToInput = document.getElementById('half_to');
    const fullFromInput = document.getElementById('full_from');
    const fullToInput = document.getElementById('full_to');

    const form = document.getElementById('capacityForm');

    // Prevent form submission on Enter key inside inputs
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
            e.preventDefault();
        }
    });

    function liveUpdate() {
        const emptyTo = parseInt(emptyToInput.value) || 0;
        let halfTo = parseInt(halfToInput.value) || 0;

        // Ensure half-to is always greater than empty-to
        if (halfTo <= emptyTo) {
            halfTo = emptyTo + 1;
            halfToInput.value = halfTo;
        }

        // Update Half-Full "From" and Full "From" live
        halfFromInput.value = emptyTo + 1;
        fullFromInput.value = halfTo + 1;

        // Full "To" always 100
        fullToInput.value = 100;
    }

    // Listen to input events for live preview before saving
    emptyToInput.addEventListener('input', liveUpdate);
    halfToInput.addEventListener('input', liveUpdate);

    // Initialize on page load
    liveUpdate();
});
</script>
@endpush
