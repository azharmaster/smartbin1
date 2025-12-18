@extends($layout)
@section('content_title', 'Asset')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Assets</h4>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-end mb-2">
            <x-asset.form-asset :floors="$floors" :categories="$categories" />
        </div>
        <div class="table-responsive">
             <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name </th>
                        <th>Floor </th>
                        <th>Serial No </th>
                        <th>Description </th>
                        <th>Model </th>
                        <th>Maintenance </th>
                        <th>Category </th>
                        <th>Option </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $index => $asset)
                    <tr>
                        <td>{{  $index + 1  }}</td>
                        <td>{{ $asset->asset_name }}</td>
                        <td>{{ $asset->floor->floor_name ?? '—' }}</td>
                        <td>{{ $asset->serialNo }}</td>
                        <td>{{ $asset->description }}</td>
                        <td>{{ $asset->model }}</td>
                        <td>{{ $asset->maintenance }}</td>
                        <td>{{ $asset->category }}</td>
                         <td>
                            <div class="d-flex align-items-center justify-content-center">
                               <x-asset.form-asset :id="$asset->id" :floors="$floors" :categories="$categories" />&nbsp;
                                <a href="{{ route('master-data.assets.destroy', $asset->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
    <i class="fas fa-trash-alt text-white"></i>
</a>&nbsp;
<a href="{{ route('master-data.assets.details', $asset->id) }}" class="btn btn-info btn-sm">
    <i class="far fa-eye"></i>
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