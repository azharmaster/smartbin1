@extends('layouts.app')
@section('content_title', 'Set Capacity')
@section('content')

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <!-- Floating Help Button -->
<button type="button" data-toggle="modal" data-target="#capacityHelpModal" style="
        position: fixed;
        top: 90px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #2563eb;
        color: #fff;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        z-index: 999;
    "
    title="Set Capacity Guide"
>
    ?
</button>



        <div class="card card-success card-outline">
            <div class="card-header text-center bg-white border-bottom-0">
                <h5 class="mb-1 fw-bold">Bin Capacity Settings</h5>
                <small class="text-muted">Configure Empty, Half-Full & Full thresholds</small>
            </div>

            <div class="card-body">

                {{-- Success alert --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(isset($capacity))

                <form action="{{ route('capacity.update', $capacity->id) }}" method="POST" id="capacityForm">
                    @csrf
                    @method('PUT')

                    <!-- EMPTY SLIDER (GREEN) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-success">Empty Range</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range"
                                   name="empty_to"
                                   id="empty_to"
                                   class="form-range slider-green flex-grow-1"
                                   min="0" max="100"
                                   value="{{ $capacity->empty_to }}">
                            <div class="d-flex flex-column gap-1">
                                <button type="button" class="btn btn-success btn-sm" id="empty_plus">+</button>
                                <button type="button" class="btn btn-success btn-sm" id="empty_minus">-</button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">0</small>
                            <strong id="empty_display" class="text-success">{{ $capacity->empty_to }}</strong>
                            <span id="empty_percent" class="badge bg-success ms-2"></span>
                        </div>
                        <small class="text-muted">Example: 0 - 39</small>
                    </div>

                    <!-- HALF FULL SLIDER (YELLOW) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-warning">Half-Full Range</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range"
                                   name="half_to"
                                   id="half_to"
                                   class="form-range slider-yellow flex-grow-1"
                                   min="0" max="100"
                                   value="{{ $capacity->half_to }}">
                            <div class="d-flex flex-column gap-1">
                                <button type="button" class="btn btn-warning btn-sm" id="half_plus">+</button>
                                <button type="button" class="btn btn-warning btn-sm" id="half_minus">-</button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <strong id="half_from_display">{{ $capacity->empty_to + 1 }}</strong>
                            <strong id="half_to_display">{{ $capacity->half_to }}</strong>
                            <span id="half_percent" class="badge bg-warning text-dark ms-2"></span>
                        </div>
                        <small class="text-muted">Example: 40 - 79</small>
                    </div>

                    <!-- FULL SLIDER (RED) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-danger">Full Range</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range"
                                   name="full_to"
                                   id="full_to"
                                   class="form-range slider-red flex-grow-1"
                                   min="0" max="100"
                                   value="100"
                                   readonly>
                            <div class="d-flex flex-column gap-1">
                                <button type="button" class="btn btn-danger btn-sm" disabled>+</button>
                                <button type="button" class="btn btn-danger btn-sm" disabled>-</button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <strong id="full_from_display" class="text-danger">{{ $capacity->half_to + 1 }}</strong>
                            <strong class="text-danger">100</strong>
                            <span id="full_percent" class="badge bg-danger ms-2"></span>
                        </div>
                        <small class="text-muted">Example: 80 - 100</small>
                    </div>

                    <!-- Save Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-3">
                            <i class="fas fa-save"></i> Save Capacity Settings
                        </button>
                    </div>

                </form>

                @else
                    <div class="alert alert-warning text-center rounded-3">
                        No capacity configuration found.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

<!-- Set Capacity Help Modal -->
<div class="modal fade" id="capacityHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Set Bin Capacity – User Guide</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6>🛠️ Purpose</h6>
        <p>
          The <strong>Set Capacity</strong> page is used to define how full a bin is
          based on percentage values.  
          These rules apply to <strong>every bin</strong> in the system.
        </p>

        <hr>

        <h6>📊 Capacity Levels</h6>
        <ul>
          <li>
            <strong style="color:#2ecc71;">0 – 39%</strong> → Empty  
            <br>
            <small>The bin is considered empty and does not require action.</small>
          </li>

          <li class="mt-2">
            <strong style="color:#f1c40f;">40 – 79%</strong> → Half Full  
            <br>
            <small>The bin is partially filled and should be monitored.</small>
          </li>

          <li class="mt-2">
            <strong style="color:#e74c3c;">80 – 100%</strong> → Full  
            <br>
            <small>The bin is full and requires immediate attention.</small>
          </li>
        </ul>

        <hr>

        <h6>🧭 How to Use This Page</h6>
        <ol>
          <li>Set the percentage range for <strong>Empty</strong>, <strong>Half Full</strong>, and <strong>Full</strong>.</li>
          <li>Make sure the ranges do not overlap.</li>
          <li>Save the configuration.</li>
        </ol>

        <p>
          Once saved, the system will automatically determine each bin’s status
          and update the dashboard indicators.
        </p>

        <hr>

        <h6>⚠️ Important Notes</h6>
        <ul>
          <li>These settings affect <strong>all bins</strong>.</li>
          <li>Wrong ranges may cause incorrect bin status.</li>
          <li>Always keep values between <strong>0 – 100%</strong>.</li>
        </ul>

      </div>

    </div>
  </div>
</div>



@push('styles')
<style>
/* Modern slider with colored track */
input[type=range] {
  -webkit-appearance: none;
  width: 100%;
  height: 12px;
  border-radius: 6px;
  background: transparent;
  transition: background 0.3s ease;
}
input[type=range]:focus { outline: none; }

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  border: 2px solid #888;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  transition: border 0.2s ease, box-shadow 0.2s ease;
}
input[type=range]:active::-webkit-slider-thumb {
  border-color: #555;
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
}

input[type=range]::-moz-range-thumb {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  border: 2px solid #888;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* Remove default track background */
input[type=range]::-webkit-slider-runnable-track {
  height: 12px;
  border-radius: 6px;
  background: transparent;
}
input[type=range]::-moz-range-track {
  height: 12px;
  border-radius: 6px;
  background: transparent;
}

/* Button styling */
button.btn-sm {
    width: 36px;
    height: 24px;
    font-weight: bold;
    padding: 0;
}
</style>
@endpush

<script>
function openHelp() {
    $('#capacityHelpModal').modal('show');
}
</script>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emptyInput = document.getElementById('empty_to');
    const halfInput = document.getElementById('half_to');
    const fullInput = document.getElementById('full_to');

    const emptyDisplay = document.getElementById('empty_display');
    const emptyPercent = document.getElementById('empty_percent');

    const halfFromDisplay = document.getElementById('half_from_display');
    const halfToDisplay = document.getElementById('half_to_display');
    const halfPercent = document.getElementById('half_percent');

    const fullFromDisplay = document.getElementById('full_from_display');
    const fullPercent = document.getElementById('full_percent');

    const emptyPlus = document.getElementById('empty_plus');
    const emptyMinus = document.getElementById('empty_minus');
    const halfPlus = document.getElementById('half_plus');
    const halfMinus = document.getElementById('half_minus');

    const form = document.getElementById('capacityForm');

    function updateValues(){
        let empty = parseInt(emptyInput.value) || 0;
        let half = parseInt(halfInput.value) || 0;

        if(half <= empty){
            half = empty + 1;
            halfInput.value = half;
        }

        // Update numbers
        emptyDisplay.textContent = empty;
        halfFromDisplay.textContent = empty + 1;
        halfToDisplay.textContent = half;
        fullFromDisplay.textContent = half + 1;

        // Update percentages
        emptyPercent.textContent = `${empty}%`;
        halfPercent.textContent = `${half - empty}%`;
        fullPercent.textContent = `${100 - half}%`;

        // Colored track
        emptyInput.style.background = `linear-gradient(to right, #28a745 0%, #28a745 ${empty}%, #d3d3d3 ${empty}% 100%)`;
        halfInput.style.background = `linear-gradient(to right, #d3d3d3 0%, #d3d3d3 ${empty}%, #ffc107 ${empty}% , #ffc107 ${half}%, #d3d3d3 ${half}% 100%)`;
        fullInput.style.background = `linear-gradient(to right, #d3d3d3 0%, #d3d3d3 ${half}%, #dc3545 ${half}% , #dc3545 100%)`;
    }

    // Slider events
    emptyInput.addEventListener('input', updateValues);
    halfInput.addEventListener('input', updateValues);

    // Buttons events
    emptyPlus.addEventListener('click', () => { emptyInput.value = Math.min(parseInt(emptyInput.value)+1, 99); updateValues(); });
    emptyMinus.addEventListener('click', () => { emptyInput.value = Math.max(parseInt(emptyInput.value)-1, 0); updateValues(); });
    halfPlus.addEventListener('click', () => { halfInput.value = Math.min(parseInt(halfInput.value)+1, 99); updateValues(); });
    halfMinus.addEventListener('click', () => { halfInput.value = Math.max(parseInt(halfInput.value)-1, parseInt(emptyInput.value)+1); updateValues(); });

    // Prevent Enter from submitting
    form.addEventListener('keydown', e => { if(e.key === 'Enter' && e.target.tagName === 'INPUT'){ e.preventDefault(); } });

    // Initialize
    updateValues();
});
</script>
@endpush
