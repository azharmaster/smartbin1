@extends('layouts.app')
@section('content_title', 'Floor Management')
@section('content')

<!-- Floating Help Button -->
<button type="button" data-bs-toggle="modal" data-bs-target="#floorHelpModal" style="
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
    title="Floor Guide">
    ?
</button>


<div class="card card-success card-outline">
 
    <div class="card-header d-flex align-items-center">
         <p class="mb-0"><i class="fas fa-table"></i> Floors List</p>

        <div class="ms-auto">
           @if(auth()->user()->role == 1)
            <x-floor.form-floor />
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
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable datatable-buttons">
              <thead>
                 <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Picture</th>
                        @if(auth()->user()->role == 1)<th>Option</th>@endif
                    </tr>
              </thead>
              <tbody>
         
                    @foreach ($floors as $index => $floor)
                    <tr>
                        <td>{{  $index + 1  }}</td>
                        <td>{{ $floor->floor_name }}</td>
                        <td>
    @if($floor->picture)
        <img src="{{ url('floor_pictures/' . $floor->picture) }}" 
             width="100" alt="Floor Picture" 
             data-toggle="modal" data-target="#floorImageModal{{ $floor->id }}" 
             style="cursor: pointer;">
        
        <!-- Modal -->
        <div class="modal fade" id="floorImageModal{{ $floor->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-body p-0">
                        <img src="{{ url('floor_pictures/' . $floor->picture) }}" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    @else
        -
    @endif
</td>

                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                            @if(auth()->user()->role == 1)
                                <x-floor.form-floor :id="$floor->id " />&nbsp;
                               <a href="{{ route('floors.destroy', $floor->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt text-white"></i>
                                </a>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floor Help Modal -->
<div class="modal fade" id="floorHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Floor Management – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-info-circle"></i> Purpose</h6>
        <p>
          This page allows you to manage building floors in the system.
          Floors are used to organize and assign bins/assets to their correct locations.
        </p>

        <hr>

        <h6><i class="fas fa-list"></i> Floor Table Overview</h6>
        <ul>
          <li><strong>Name</strong> – The registered floor name (e.g., Ground Floor, Level 1).</li>
          <li><strong>Picture</strong> – Floor layout image (if uploaded).</li>
          <li><strong>Option</strong> – Edit or delete actions (Admin only).</li>
        </ul>

        <hr>

        <h6><i class="fas fa-image"></i> Viewing Floor Picture</h6>
        <ul>
          <li>Click on the floor image thumbnail to enlarge it.</li>
          <li>The image will open in a popup modal for better viewing.</li>
          <li>If no image is uploaded, a dash (-) will be displayed.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-cogs"></i> Available Actions (Admin Only)</h6>
        <ul>
          <li><strong>Add Floor</strong> – Use the add button at the top-right to create a new floor.</li>
          <li><strong>Edit Floor</strong> – Click the edit button to update floor details.</li>
          <li><strong>Delete Floor</strong> – Click the delete button to remove a floor from the system.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Notes</h6>
        <ul>
          <li>Only users with <strong>Admin role</strong> can add, edit, or delete floors.</li>
          <li>Ensure floor names are accurate to avoid confusion when assigning assets.</li>
          <li>Deleting a floor may affect assets assigned to that floor.</li>
        </ul>

      </div>

    </div>
  </div>
</div>
@endsection