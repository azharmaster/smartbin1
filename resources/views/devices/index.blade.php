@extends($layout)
@section('content_title', 'Sensors')
@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#devicesHelpModal" style="
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #faa70c;
        color: #fff;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        z-index: 999;
    "
    title="Devices Guide"
>
    ?
</button>

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h6 class="mb-0">Sensors List</h6>

        <div class="ms-auto">
        @if(auth()->user()->role == 1)
        <x-device.form-device :assets="$assets" />
        @endif
        </div>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        @if(auth()->user()->role == 1)
            
        @endif

        <div class="table-responsive">
        <table class="table table-bordered table-striped dataTable dtr-inline datatable-buttons datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset Name</th>
                        <th>Sensor ID</th>
                        <th>Sensor Name</th>
                        <th>Serial Number</th>
                        <th>Sim Card</th>
                        @if(auth()->user()->role == 1)<th>Option</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $index => $device)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $device->asset->asset_name ?? '-' }}</td>
                        <td>{{ $device->id_device }}</td>
                        <td>{{ $device->device_name }}</td>
                        <td>{{ $device->serialno }}</td>
                        <td>{{ $device->simcard }}</td>
                        <td>
                            @if(auth()->user()->role == 1)
                            <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                            @if(auth()->user()->role == 1)
                                <x-device.form-device
                                    :id="$device->id"
                                    :assets="$assets"
                                    :device_name="$device->device_name"
                                    :asset_id="$device->asset_id"
                                    :id_device="$device->id_device"
                                    :serialno="$device->serialno"
                                    :simcard="$device->simcard"
                                />
                                &nbsp;

                                <form action="{{ route('devices.destroy', $device->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this device?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </form>
                            @endif
                            </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Devices Help Modal -->
<div class="modal fade" id="devicesHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Sensors / Devices – User Guide</h5>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-tools"></i> Purpose</h6>
        <p>
          The <strong>Sensors</strong> page allows you to manage all devices connected to your assets.
          You can add new sensors, edit existing ones, and delete sensors when necessary.
        </p>

        <hr>

        <h6><i class="fas fa-microchip"></i> Table Overview</h6>
        <ul>
          <li><strong>#</strong>: Serial number of the device.</li>
          <li><strong>Asset Name</strong>: The asset to which the device belongs.</li>
          <li><strong>Sensor ID</strong>: Unique identifier of the device.</li>
          <li><strong>Sensor Name</strong>: Name assigned to the device.</li>
          <li><strong>Option</strong>: Actions available for the device (Edit / Delete).</li>
        </ul>

        <hr>

        <h6><i class="fas fa-plus-circle"></i> Adding a Device</h6>
        <ul>
          <li>Click the <strong>+ Add Device</strong> button at the top-right.</li>
          <li>Fill in the asset, device name, and sensor ID.</li>
          <li>Save the device to add it to the table.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-edit"></i> Editing a Device</h6>
        <ul>
          <li>Click the <strong>Edit</strong> button in the <strong>Option</strong> column.</li>
          <li>Update the necessary fields in the modal popup.</li>
          <li>Save changes to update the table.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-trash-alt"></i> Deleting a Device</h6>
        <ul>
          <li>Click the <strong>Delete</strong> button in the <strong>Option</strong> column.</li>
          <li>Confirm the deletion in the popup prompt.</li>
          <li>The device will be removed from the table and the database.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>Only users with the appropriate role can edit or delete devices.</li>
          <li>Ensure sensor IDs are unique for each asset.</li>
          <li>Changes are applied immediately to the system.</li>
        </ul>

      </div>

    </div>
  </div>
</div>

@endsection
