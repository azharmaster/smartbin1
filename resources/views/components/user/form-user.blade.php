@props(['id' => null, 'name' => null, 'email' => null, 'role' => null, 'phone' => null])

<div>
    <button type="button" 
        class="{{ $id ? 'btn btn-outline-secondary' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formUser{{ $id ?? '' }}">
        <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
        {{ $id ? '' : 'Add' }}
    </button>

<div class="modal fade" id="formUser{{ $id ?? '' }}">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @if($id)
            @method('PUT')
        @endif
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

                <!-- PHONE -->
                <div class="form-group">
                  <label for="phone">Phone | Ex: 0101234567</label>
                  <input type="text" class="form-control" name="phone" id="phone" value="{{ $phone ?? '' }}">
                </div>

                <!-- ROLE -->
                <div class="form-group">
                  <label for="role">Role</label>
                  <select name="role" class="form-control">
                      <option value="1" {{ isset($role) && $role == 1 ? 'selected' : '' }}>Admin</option>
                      <option value="4" {{ isset($role) && $role == 4 ? 'selected' : '' }}>Supervisor</option>
                      <option value="3" {{ isset($role) && $role == 3 ? 'selected' : '' }}>Client</option>
                      <!-- Add other roles if needed -->
                  </select>
                </div>

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
