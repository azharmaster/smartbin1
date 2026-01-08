@extends('layouts.app')
@section('content_title', 'Data User')
@section('content')

<div class="card card-success card-outline">
    <div class="card-header">
        <h5 class="mb-0">User</h5>
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

        {{-- USERS TABLE --}}
        <div class="table-responsive">
            <table id="table1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Option</th>
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
                            <div class="d-flex gap-1 align-items-start">

                                {{-- PENCIL EDIT FORM --}}
                                <x-user.form-user :id="$user->id" :name="$user->name" :email="$user->email" :phone="$user->phone" :role="$user->role" />

                                {{-- DELETE USER --}}
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </form>

                                {{-- VIEW DETAILS --}}
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

@endsection
