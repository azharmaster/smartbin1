<div>
   <button type="button" class="  {{ $id ? 'btn btn-default' : 'btn btn-primary' }}" data-toggle="modal" data-target="#formFloor{{ $id ?? '' }}">
                  {{ $id ? 'Edit' : 'Add' }}
                </button>

                    <div class="modal fade" id="formFloor{{ $id ?? '' }}">
                         <form method="POST" action="{{ route('floors.store') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $id ?? '' }}">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">{{ $id ? 'Form Edit Floor' : 'Form Add Floor' }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
             
           
                <div class="form-group">
                  <label for="floor_name">Floor Name</label>
                  <input type="text" class="form-control" name="floor_name" id="floor_name" value="{{ $floor_name ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="picture">Picture</label>
                    <input type="text" class="form-control" name="picture" id="picture" value="{{ $picture ?? '' }}">
                </div>


              
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      </form>
</div>