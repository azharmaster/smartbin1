@extends('layouts.app')
@section('content_title', 'Data User')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">User</h4>
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
                        <th>name</th>
                        <th>Email</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                    <tr>
                        <td>{{  $index + 1  }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <x-user.form-user :id="$user->id " />&nbsp;
                               <a href="{{ route('users.destroy', $user->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
    <i class="fas fa-trash-alt text-white"></i>
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
@endsection