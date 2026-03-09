@extends('layouts.app')
@section('content_title', 'Data User')
@section('content')

<button type="button" data-bs-toggle="modal" data-bs-target="#userHelpModal" style="
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
    title="User Management Guide">
    ?
</button>


<div class="card card-success card-outline">
    <div class="card-header d-flex align-items-center">
         <p class="mb-0"><i class="fas fa-table"></i> User List</p>

        <div class="ms-auto">
            @if(auth()->user()->role == 1)
            <x-user.form-user/>
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

        {{-- USERS TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable datatable-buttons">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Notify</th>
                        <th>Last Active</th>
                        @if(auth()->user()->role == 1)<th>Option</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>
                            @if($user->role == 1)
                                <span class="badge bg-primary">Admin</span>
                            @elseif($user->role == 4)
                                <span class="badge bg-info">Supervisor</span>
                            @else
                                <span class="badge bg-secondary">Client</span>
                            @endif    
                        </td>

                        {{-- WhatsApp Toggle Column --}}
                        <td class="text-center">
                            @if(auth()->user()->role == 1)
                                <form action="{{ route('users.toggleWhatsapp', $user->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('PATCH')

                                    <label class="custom-switch">
                                        <input type="checkbox"
                                              onchange="this.form.submit()"
                                              {{ $user->whatsapp_notify ? 'checked' : '' }}
                                              {{ empty($user->phone) ? 'disabled title=Phone required' : '' }}>
                                        <span class="slider">
                                            <span class="switch-text switch-off">OFF</span>
                                            <span class="switch-text switch-on">ON</span>
                                        </span>
                                    </label>
                                </form>
                            @else
                                @if($user->whatsapp_notify)
                                    <span class="badge bg-success">ON</span>
                                @else
                                    <span class="badge bg-secondary">OFF</span>
                                @endif
                            @endif
                        </td>

                        {{-- ✅ LAST ACTIVE COLUMN --}}
                        <td class="text-center">
                            @if($user->last_active)

                                @if($user->last_active >= now()->subMinutes(5))
                                    <span class="badge bg-success">Online</span>
                                @else
                                    <span class="badge bg-secondary">
                                        {{ \Carbon\Carbon::parse($user->last_active)->diffForHumans() }}
                                    </span>
                                @endif

                            @else
                                <span class="badge bg-light text-dark">Never</span>
                            @endif
                        </td>

                        @if(auth()->user()->role == 1)
                        <td>
                            <div class="d-flex gap-1 align-items-center justify-content-center">

                                <x-user.form-user 
                                    :id="$user->id" 
                                    :name="$user->name" 
                                    :email="$user->email" 
                                    :phone="$user->phone" 
                                    :role="$user->role" 
                                />

                                <a href="{{ route('users.destroy', $user->id) }}" 
                                   class="btn btn-danger btn-sm" 
                                   data-confirm-delete="true">
                                    <i class="fas fa-trash-alt text-white"></i>
                                </a>

                                @csrf
                                @method('DELETE')

                                {{-- VIEW DETAILS --}}
                                <a href="{{ route('users.details', $user->id) }}" 
                                   class="btn btn-info btn-sm">
                                    <i class="far fa-eye"></i>
                                </a>
                            </div>
                        </td>
                        @endif

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- User Management Help Modal -->
<div class="modal fade" id="userHelpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">User Management – User Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="font-size: 14px;">

        <h6><i class="fas fa-info-circle"></i> Purpose</h6>
        <p>
          This page allows Admin users to manage system users, including
          creating, editing, viewing, and deleting accounts.
        </p>

        <hr>

        <h6><i class="fas fa-users"></i> User Roles Explanation</h6>
        <ul>
          <li>
            <strong>Admin</strong>
            <ul>
              <li>Full system access.</li>
              <li>Can manage users, floors, assets, and system settings.</li>
              <li>Can add, edit, delete, and view all records.</li>
            </ul>
          </li>

          <li>
            <strong>Supervisor</strong>
            <ul>
              <li>Receives bin/alert notifications via <strong>WhatsApp</strong> if toggle is ON.</li>
              <li>Must have a valid phone number registered in the system.</li>
              <li>Can log in to the system.</li>
              <li>Can view the dashboard and monitoring data only.</li>
              <li>Cannot manage users, floors, or assets.</li>
            </ul>
          </li>

          <li>
            <strong>Client</strong>
            <ul>
              <li>Can log in to the system.</li>
              <li>Can view the dashboard and monitoring data only.</li>
              <li>Cannot manage users, floors, or assets.</li>
            </ul>
          </li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Important Notes</h6>
        <ul>
          <li>Users will only receive WhatsApp alerts if the toggle is set to ON.</li>
          <li>Phone number must be filled to enable notifications.</li>
          <li>Only Admin users can manage user accounts.</li>
        </ul>

      </div>

    </div>
  </div>
</div>

<style>
.custom-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 28px;
}

.custom-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0;
    right: 0; bottom: 0;
    background-color: #ccc;
    border-radius: 14px;
    transition: 0.4s;
}

.slider::before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.4s;
}

.custom-switch input:checked + .slider {
    background-color: #28a745;
}

.custom-switch input:checked + .slider::before {
    transform: translateX(32px);
}

.switch-text {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    font-weight: bold;
    color: white;
    pointer-events: none;
}

.switch-off {
    left: 6px;
}

.switch-on {
    right: 6px;
}
</style>

@endsection