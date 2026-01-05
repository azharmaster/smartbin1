@extends($layout)
@section('content_title', 'Devices')
@section('content')
<div class="card card-success card-outline">
    <div class="card-header">
        <h5 class="mb-0">Devices</h5>
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
        <div class="d-flex justify-content-end mb-2">
            <x-device.form-device :assets="$assets" />
        </div>
        @endif

        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset Name</th>
                        <th>Device Name</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $index => $device)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $device->asset->asset_name ?? '-' }}</td>
                        <td>{{ $device->device_name }}</td>
                        <td>
                            @if(auth()->user()->role == 1)
                            <div class="d-flex align-items-center justify-content-center">
                                <x-device.form-device 
                                    :id="$device->id" 
                                    :device_name="$device->device_name" 
                                    :asset_id="$device->asset_id" 
                                    :assets="$assets"/>

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
