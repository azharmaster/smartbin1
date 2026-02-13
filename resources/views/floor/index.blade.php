@extends('layouts.app')
@section('content_title', 'Floor')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Floor</h4>
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
            <x-floor.form-floor />
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable datatable-buttons">
              <thead>
                 <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Picture</th>
                        <th>Option</th>
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
                                <x-floor.form-floor :id="$floor->id " />&nbsp;
                               <a href="{{ route('floors.destroy', $floor->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
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