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
        <h5 class="mb-0">User List</h5>

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
                        @if(auth()->user()->role == 1)<td>
                            <div class="d-flex gap-1 align-items-center justify-content-center">

                    
                    <x-user.form-user :id="$user->id" :name="$user->name" :email="$user->email" :phone="$user->phone" :role="$user->role" />
                    

                     <a href="{{ route('users.destroy', $user->id) }}"class="btn btn-danger btn-sm" data-confirm-delete="true">
                    <i class="fas fa-trash-alt text-white"></i></a>
                    
                    @csrf
                     @method('DELETE')
                        </form>

                                {{-- VIEW DETAILS --}}
                                <a href="{{ route('users.details', $user->id) }}" class="btn btn-info btn-sm">
                                    <i class="far fa-eye"></i>
                                </a>
                            </div>
                        </td>@endif
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
              <li>Does not use the web application dashboard.</li>
              <li>Receives bin/alert notifications via <strong>WhatsApp only</strong>.</li>
              <li>Must have a valid phone number registered in the system.</li>
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

        <h6><i class="fas fa-list"></i> User Table Overview</h6>
        <ul>
          <li><strong>Name</strong> – User’s full name.</li>
          <li><strong>Email</strong> – Used for login authentication.</li>
          <li><strong>Phone</strong> – Required for Supervisor WhatsApp notifications.</li>
          <li><strong>Role</strong> – Determines system access level.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-cogs"></i> Available Actions (Admin Only)</h6>
        <ul>
          <li><strong>Add User</strong> – Create a new user account.</li>
          <li><strong>Edit User</strong> – Update user information and role.</li>
          <li><strong>Delete User</strong> – Remove user access from the system.</li>
          <li><strong>View Details</strong> – View complete user information.</li>
        </ul>

        <hr>

        <h6><i class="fas fa-exclamation-circle"></i> Important Notes</h6>
        <ul>
          <li>Ensure Supervisor phone numbers are correct for WhatsApp alerts.</li>
          <li>Assign roles carefully to prevent unauthorized access.</li>
          <li>Only Admin users can manage user accounts.</li>
        </ul>

      </div>

    </div>
  </div>
</div>


@endsection
