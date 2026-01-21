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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" class="d-flex">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control form-control-sm me-2"
                    placeholder="Search Device ID or Network...">
                <button type="submit" class="btn btn-sm btn-success">Search</button>
            </form>

            <form method="GET" class="d-flex">
                <label class="me-2">Rows per page:</label>
                <select name="perPage" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" {{ request('perPage', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Device ID</th>
                        <th>Battery</th>
                        <th>Capacity</th>
                        <th>Time</th>
                        <th>Network</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sensors as $index => $sensor)
                    <tr>
                        <!-- Correct numbering across pages -->
                        <td>{{ $sensors->firstItem() + $index }}</td>
                        <td>{{ $sensor->device_id }}</td>
                        <td>{{ $sensor->battery }}%</td>
                        <td>{{ $sensor->capacity }}%</td>
                        <td>{{ $sensor->time }}</td>
                        <td>{{ $sensor->network }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    <div class="mt-3 d-flex justify-content-end">
        {{ $sensors->links('pagination::bootstrap-5') }}
    </div>

    </div>
</div>

@endsection
