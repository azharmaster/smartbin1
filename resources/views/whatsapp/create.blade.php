@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create New WhatsApp Notification</h1>

    <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary mb-3">Back to List</a>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('whatsapp.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea name="message" id="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
        </div>

        <!-- <div class="mb-3">
            <label for="target" class="form-label">Target Phone Number</label>
            <input type="text" name="target" id="target" class="form-control" value="{{ old('target') }}" required>
        </div> -->

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active (ON)</label>
        </div>

        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="datetime-local" name="start_time" id="start_time" class="form-control" 
                   value="{{ old('start_time') }}">
        </div>

        <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="datetime-local" name="end_time" id="end_time" class="form-control" 
                   value="{{ old('end_time') }}">
        </div>

        <button type="submit" class="btn btn-primary">Create Notification</button>
    </form>
</div>
@endsection
