<form method="POST" action="{{ $id ? route('users.update') : route('users.store') }}">
    @csrf
    @if($id)
        @method('PUT')
        <input type="hidden" name="id" value="{{ $id }}">
    @endif

    <div class="d-flex gap-1 align-items-center">
        <input type="text" name="name" value="{{ $name }}" class="form-control form-control-sm" placeholder="Name" required>
        <input type="email" name="email" value="{{ $email }}" class="form-control form-control-sm" placeholder="Email" required>
        <input type="text" name="phone" value="{{ $phone }}" class="form-control form-control-sm" placeholder="Phone">
        <select name="role" class="form-control form-control-sm">
            <option value="1" {{ $role == 1 ? 'selected' : '' }}>Admin</option>
            <option value="4" {{ $role == 4 ? 'selected' : '' }}>Supervisor</option>
        </select>
        <button type="submit" class="btn btn-success btn-sm">Save</button>
    </div>
</form>
