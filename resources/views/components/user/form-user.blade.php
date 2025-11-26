<div>
   <button type="button" 
        class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formUser{{ $id ?? '' }}">

    <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
    {{ $id ? '' : 'Add' }}

</button>


<div class="modal fade" id="formUser{{ $id ?? '' }}">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <input type="hidden" name="id" value="{{ $id ?? '' }}">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">{{ $id ? 'Form Edit User' : 'Form Add User' }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">


                <!-- NAME -->
                <div class="form-group">
                  <label for="name">Nama</label>
                  <input type="text" class="form-control" name="name" id="name" value="{{ $name ?? '' }}">
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="email" value="{{ $email ?? '' }}">
                </div>

                <!-- ROLE -->
                <div class="form-group">
                  <label for="role">Role</label>
                  <select name="role" class="form-control">
                      <option value="1" {{ isset($role) && $role == 1 ? 'selected' : '' }}>Admin</option>
                      <option value="2" {{ isset($role) && $role == 2 ? 'selected' : '' }}>Staff</option>
                  </select>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" name="password">
                </div>
                <small class="text-muted">
                    {{ $id ? 'Leave blank if you do not want to change the password.' : '' }}
                </small>


            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </div>
        </div>
      </form>
</div>
</div>
