@props([
    'id' => null,
    'user_id' => null,
    'floor_id' => null,
    'shift' => null,
    'date' => null,
    'users' => [],
    'floors' => []
])

<div>
    <!-- Add/Edit Button -->
    <button type="button"
        class="{{ $id ? 'btn btn-default' : 'btn btn-primary' }}"
        data-toggle="modal" data-target="#formSchedule{{ $id ?? '' }}">
        <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus' }}"></i>
        {{ $id ? '' : 'Add' }}
    </button>

    <!-- Modal -->
    <div class="modal fade" id="formSchedule{{ $id ?? '' }}">
        <form method="POST" action="{{ $id ? route('schedules.update', $id) : route('schedules.store') }}">
            @csrf
            @if($id)
                @method('PUT')
            @endif

            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Edit Schedule' : 'Add Schedule' }}</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        {{-- User Dropdown --}}
                        <div class="form-group">
                            <label>User</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">-- Select User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ ($user_id ?? '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Floor Dropdown --}}
                        <div class="form-group">
                            <label>Floor</label>
                            <select name="floor_id" class="form-control" required>
                                <option value="">-- Select Floor --</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ ($floor_id ?? '') == $floor->id ? 'selected' : '' }}>
                                        {{ $floor->floor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Shift --}}
                        <div class="form-group">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control" placeholder="Morning/Evening/Night"
                                value="{{ $shift ?? '' }}" required>
                        </div>

                        {{-- Date --}}
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="{{ $date ?? '' }}" required>
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
