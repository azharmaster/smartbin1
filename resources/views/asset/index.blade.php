@extends($layout)
@section('content_title', 'Asset Management')
@section('content')

<style>
    .asset-status-switcher {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 6px;
        border-radius: 16px;
        background: #f3f7f5;
        border: 1px solid #dbe7e0;
    }

    .asset-status-switcher .status-button {
        border: 0;
        border-radius: 12px;
        padding: 10px 16px;
        font-weight: 700;
        color: #51606c;
        background: transparent;
        transition: all .2s ease;
        box-shadow: none;
    }

    .asset-status-switcher .status-button .badge {
        margin-left: 8px;
        border-radius: 999px;
        padding: 5px 9px;
    }

    .asset-status-switcher .status-button.active {
        color: #fff;
        background: linear-gradient(135deg, #17966a 0%, #127d59 100%);
        box-shadow: 0 10px 20px rgba(18, 125, 89, 0.22);
    }

    .asset-status-switcher .status-button.active .badge {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #fff !important;
    }

    .asset-status-switcher .status-button:not(.active):hover {
        background: #e8f0eb;
        color: #22313f;
    }
</style>

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#holidaysHelpModal" style="
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
    title="Asset Management"
>
    ?
</button>

<div class="card card-success card-outline">
   <div class="card-header d-flex align-items-center">
    <p class="mb-0"><i class="fas fa-table"></i> Bins List</p>

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

        <div class="asset-status-switcher mb-3" id="assetStatusTabs" role="tablist">
            <a class="status-button active" id="active-assets-tab" data-toggle="tab" href="#active-assets" role="tab" aria-controls="active-assets" aria-selected="true">
                Active
                <span class="badge badge-success">{{ $activeAssets->count() }}</span>
            </a>
            <a class="status-button" id="inactive-assets-tab" data-toggle="tab" href="#inactive-assets" role="tab" aria-controls="inactive-assets" aria-selected="false">
                Inactive
                <span class="badge badge-secondary">{{ $inactiveAssets->count() }}</span>
            </a>
        </div>

        <div class="tab-content" id="assetStatusTabsContent">
            <div class="tab-pane fade show active" id="active-assets" role="tabpanel" aria-labelledby="active-assets-tab">
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
                                <th>Status </th>
                                <th>Latitude </th>
                                <th>Longitude </th>
                                <th>Picture</th>
                                <th>Option </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeAssets as $index => $asset)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>{{ $asset->floor->floor_name ?? '—' }}</td>
                                <td>{{ $asset->serialNo }}</td>
                                <td>{{ $asset->location }}</td>
                                <td>{{ $asset->model }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success">Active</span>
                                </td>
                                <td>{{ $asset->latitude ?? '—' }}</td>
                                <td>{{ $asset->longitude ?? '—' }}</td>
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
                                        </a>&nbsp;
                                        @endif

                                        <a href="{{ route('master-data.assets.details', $asset->id) }}" class="btn btn-info btn-sm">
                                            <i class="far fa-eye"></i>
                                        </a>&nbsp;

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

            <div class="tab-pane fade" id="inactive-assets" role="tabpanel" aria-labelledby="inactive-assets-tab">
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
                                <th>Status </th>
                                <th>Latitude </th>
                                <th>Longitude </th>
                                <th>Picture</th>
                                <th>Option </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inactiveAssets as $index => $asset)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $asset->asset_name }}</td>
                                <td>{{ $asset->floor->floor_name ?? '—' }}</td>
                                <td>{{ $asset->serialNo }}</td>
                                <td>{{ $asset->location }}</td>
                                <td>{{ $asset->model }}</td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">Inactive</span>
                                </td>
                                <td>{{ $asset->latitude ?? '—' }}</td>
                                <td>{{ $asset->longitude ?? '—' }}</td>
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
                                        </a>&nbsp;
                                        @endif

                                        <a href="{{ route('master-data.assets.details', $asset->id) }}" class="btn btn-info btn-sm">
                                            <i class="far fa-eye"></i>
                                        </a>&nbsp;

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
    </div>
</div>

<!-- QR Code Modals -->
@foreach ($assets as $asset)
<div class="modal fade" id="qrModal{{ $asset->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code - {{ $asset->asset_name }}</h5>
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

document.addEventListener('DOMContentLoaded', function () {
    const statusButtons = document.querySelectorAll('.asset-status-switcher .status-button');

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
        statusButtons.forEach((button) => button.classList.remove('active'));
        event.target.classList.add('active');
    });
});
</script>

<!-- Asset Management Help Modal -->
<div class="modal fade" id="holidaysHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Asset Management - User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-info-circle"></i> Purpose</h6>
        <p>
          This page allows you to manage all registered <strong>bins/assets</strong> in the system.
          You can view asset details, generate QR codes, and (Admin only) add or delete assets.
        </p>

        <hr>

        <h6><i class="fas fa-list"></i> Bins List Table</h6>
        <ul>
          <li><strong>Name</strong> - The registered name of the bin/asset.</li>
          <li><strong>Floor</strong> - The assigned floor location.</li>
          <li><strong>Serial No</strong> - Unique serial number of the device.</li>
          <li><strong>Location</strong> - Physical placement details.</li>
          <li><strong>Model</strong> - Bin/device model type.</li>
          <li><strong>Picture</strong> - Thumbnail image of the asset (if uploaded).</li>
        </ul>

        <hr>

        <h6><i class="fas fa-cogs"></i> Available Actions</h6>
        <ul>
          <li>
            <strong>Add Asset (Admin Only)</strong> - Use the <strong>Add</strong> button at the top-right to register a new bin.
          </li>
          <li>
            <strong>View Details</strong> - Click the <i class="far fa-eye"></i> button to see full asset information.
          </li>
          <li>
            <strong>Delete Asset (Admin Only)</strong> - Click the <i class="fas fa-trash-alt"></i> button to remove an asset from the system.
          </li>
          <li>
            <strong>Generate QR Code</strong> - Click the <i class="fas fa-qrcode"></i> button to open the QR code modal.
          </li>
        </ul>

        <hr>

        <h6><i class="fas fa-qrcode"></i> QR Code Function</h6>
        <ul>
          <li>Each asset has a unique QR code.</li>
          <li>Scanning the QR code will open the asset's detail page.</li>
          <li>You can download the QR code as a PNG image using the <strong>Download QR</strong> button.</li>
          <li>QR codes can be printed and attached to the physical bin for quick access.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>Only users with <strong>Admin role</strong> can add or delete assets.</li>
          <li>Ensure serial numbers and locations are entered correctly during registration.</li>
          <li>Deleting an asset may affect related monitoring or historical data.</li>
        </ul>

      </div>

    </div>
  </div>
</div>
@endsection
