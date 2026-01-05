@extends('layouts.app')
@section('content_title', 'Data User')
@section('content')
<div class="card card-success card-outline">
    <div class="card-header ">
        <h5 class="mb-0 ">User</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-end mb-2">
            <x-user.form-user />
        </div>
        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped dataTable dtr-inline">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th> {{-- Added Phone --}}
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                    <tr>
                        <td>{{  $index + 1  }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td> {{-- Display phone --}}
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <x-user.form-user :id="$user->id" :name="$user->name" :email="$user->email" :role="$user->role" :phone="$user->phone" />&nbsp;
                                <a href="{{ route('users.destroy', $user->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt text-white"></i>
                                </a>&nbsp;
                                <a href="{{ route('users.details', $user->id) }}" class="btn btn-info btn-sm">
                                    <i class="far fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- SmartBin Gradient Style -->
<style>
.smartbin-gradient {
    background: linear-gradient(135deg, #1b5e20, #4bb352ff);
}
</style>

@endsection
