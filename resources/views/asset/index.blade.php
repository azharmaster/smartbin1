@extends($layout)
@section('content_title', 'Asset')
@section('content')
<div class="card card-success card-outline">
    <div class="card-header">
        <h5 class="mb-0">Assets</h5>
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
            <x-asset.form-asset :floors="$floors" />
        </div>
        <div class="table-responsive">
             <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name </th>
                        <th>Floor </th>
                        <th>Serial No </th>
                        <th>Location </th>
                        <th>Model </th>
                        <th>Picture</th>
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
                        <td>{{ $asset->location }}</td>
                        <td>{{ $asset->model }}</td>
                        <td class="text-center">
                            @if($asset->picture)
                                <img src="{{ asset('storage/' . $asset->picture) }}"
                                    alt="Asset Image"
                                    class="img-thumbnail"
                                    style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                         <td>
                            <div class="d-flex align-items-center justify-content-center">
                               <x-asset.form-asset :id="$asset->id" :floors="$floors" :picture="$asset->picture"/>&nbsp;
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
