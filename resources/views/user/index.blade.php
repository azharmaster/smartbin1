@extends('layouts.app')
@section('content_title', 'Data User')
@section('content')

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

@endsection
