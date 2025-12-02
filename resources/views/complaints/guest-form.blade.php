@extends('layouts.guestapp')

@section('content')
<style>
    .guest-form-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }

    .guest-form-box {
        width: 100%;
        max-width: 450px;
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
</style>

<div class="guest-form-wrapper">
    <div class="guest-form-box">

        <h3 class="mb-3 text-center">Submit a Complaint</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('complaint.guest.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Asset</label>
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
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Submit Complaint</button>
        </form>

    </div>
</div>
@endsection
