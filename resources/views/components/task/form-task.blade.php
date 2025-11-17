<form action="{{ $id ? route('tasks.update', $id) : route('tasks.store') }}" method="POST">
    @csrf
    @if($id)
        @method('PUT')
    @endif

    <div class="form-group">
        <label>User</label>
        <select name="userID" class="form-control" required>
            @foreach($users as $user)
            <option value="{{ $user->id }}" {{ $userID == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Asset</label>
        <select name="assetID" class="form-control" required>
            @foreach($assets as $asset)
            <option value="{{ $asset->id }}" {{ $assetID == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Description</label>
        <input type="text" name="description" value="{{ $description ?? '' }}" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">{{ $id ? 'Update Task' : 'Create Task' }}</button>
</form>
