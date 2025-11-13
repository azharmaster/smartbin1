<div>
   <button type="button" class="  {{ $id ? 'btn btn-default' : 'btn btn-primary' }}" data-toggle="modal" data-target="#formKategori{{ $id ?? '' }}">
                  {{ $id ? 'Edit' : 'Add' }}
                </button>

                    <div class="modal fade" id="formKategori{{ $id ?? '' }}">
                         <form method="POST" action="{{ route('master-data.kategori.store') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $id ?? '' }}">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">{{ $id ? 'Form Edit Kategori' : 'Form Add Kategori' }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
             
           
                <div class="form-group">
                  <label for="nama_kategori">Nama Kategori</label>
                  <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" value="{{ $nama_kategori ?? '' }}">
                </div>
                <div class="form-group">
                  <label for="deskripsi">Deskripsi</label>
                  <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3">{{ $deskripsi ?? '' }}</textarea>
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