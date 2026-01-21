@extends($layout)
@section('content_title', 'Sensor')
@section('content')

<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
        <h5 class="mb-0">Sensor Data</h5>

        <div class="ms-auto">
            {{-- keep empty for future button --}}
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable datatable-buttons">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Device ID</th>
                        <th>Battery</th>
                        <th>Capacity</th>
                        <th>Time</th>
                        <th>Network</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sensors as $index => $sensor)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sensor->device_id }}</td>
                        <td>{{ $sensor->battery }}</td>
                        <td>{{ $sensor->capacity }}</td>
                        <td>{{ $sensor->time }}</td>
                        <td>{{ $sensor->network }}</td>
                        <td>
                            <div class="d-flex gap-1 align-items-center justify-content-center">
                                <a href="{{ route('sensors.destroy', $sensor->id) }}"
                                   data-confirm-delete="true"
                                   class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt text-white"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
