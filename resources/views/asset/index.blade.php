@extends($layout)
@section('content_title', 'Asset Management')
@section('content')
<div class="card card-success card-outline">
   <div class="card-header d-flex align-items-center">
    <h5 class="mb-0">Bins List</h5>

    <div class="ms-auto">
    @if(auth()->user()->role == 1)
        <x-asset.form-asset :floors="$floors" />
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

        <div class="d-flex justify-content-end mb-2">
        </div>
        <div class="table-responsive">
             <table class="table table-bordered table-striped dataTable dtr-inline datatable-buttons datatable">
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
                                <img src="{{ asset('uploads/asset/' . $asset->picture) }}"
                                    alt="Asset Image"
                                    class="img-thumbnail"
                                    style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                         <td>
                            <div class="d-flex align-items-center justify-content-center">
                                @if(auth()->user()->role == 1)
                                <a href="{{ route('master-data.assets.destroy', $asset->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt text-white"></i>
                                @endif
                            </a>&nbsp;

                            <a href="{{ route('master-data.assets.details', $asset->id) }}" class="btn btn-info btn-sm">
                                <i class="far fa-eye"></i>
                            </a>&nbsp;

                            <!-- QR Code Button -->
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#qrModal{{ $asset->id }}">
                                <i class="fas fa-qrcode"></i>
                            </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- QR Code Modals -->
@foreach ($assets as $asset)
<div class="modal fade" id="qrModal{{ $asset->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code – {{ $asset->asset_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">

                <!-- QR Container -->
                <div id="qrContainer{{ $asset->id }}">
                    {!! QrCode::size(200)->generate(route('master-data.assets.details', $asset->id)) !!}
                </div>

                <p class="mt-2 text-muted">Scan to view bin details</p>

                <!-- Download Button -->
                <button class="btn btn-success btn-sm mt-2"
                        onclick="downloadQR('qrContainer{{ $asset->id }}', '{{ $asset->asset_name }}')">
                    <i class="fas fa-download"></i> Download QR
                </button>

            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Download QR Script -->
<script>
function downloadQR(containerId, fileName) {
    const svg = document.querySelector(`#${containerId} svg`);
    const serializer = new XMLSerializer();
    const svgStr = serializer.serializeToString(svg);

    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    const img = new Image();

    const svgBlob = new Blob([svgStr], { type: "image/svg+xml;charset=utf-8" });
    const url = URL.createObjectURL(svgBlob);

    img.onload = function () {
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        URL.revokeObjectURL(url);

        const pngUrl = canvas.toDataURL("image/png");
        const a = document.createElement("a");
        a.href = pngUrl;
        a.download = fileName + "_QR.png";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    img.src = url;
}
</script>

@endsection
