@extends('layouts.guestapp')

@section('content')
<div class="container mt-4">
    <h2>Submit a Complaint</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('complaint.guest.submit') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Asset</label>
            <select name="asset_id" class="form-control" required>
                <option value="">-- Select Asset --</option>
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}">
                        {{ $asset->asset_name ?? 'Asset #' . $asset->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Complaint</button>
    </form>
</div>
@endsection
