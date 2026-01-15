@extends($layout)
@section('content_title', 'Sensors')
@section('content')
<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h6 class="mb-0">Sensors List</h6>

        <div class="ms-auto">
        <x-device.form-device :assets="$assets" />
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
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $index => $device)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $device->asset->asset_name ?? '-' }}</td>
                        <td>{{ $device->id_device }}</td>
                        <td>{{ $device->device_name }}</td>
                        <td>
                            @if(auth()->user()->role == 1)
                            <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                                <x-device.form-device
                                    :id="$device->id"
                                    :assets="$assets"
                                    :device_name="$device->device_name"
                                    :asset_id="$device->asset_id"
                                    :id_device="$device->id_device"
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
@endsection
