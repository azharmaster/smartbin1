@extends('layouts.app')
@section('content_title', 'Dashboard')

@section('content')
<div class="card">
    <div class="card-body">
        WElcome t o pos appliceation {{ auth()->user()->name }}
    </div>
</div>
@endsection