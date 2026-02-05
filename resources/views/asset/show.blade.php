@extends('layouts.app')

@section('content')
<div class="container">
    <h3>{{ $asset->name }}</h3>
    <p><strong>Floor:</strong> {{ $asset->floor }}</p>
    <p><strong>Serial Number:</strong> {{ $asset->serial_number }}</p>
    <p><strong>Location:</strong> {{ $asset->location }}</p>
    <p><strong>Model:</strong> {{ $asset->model }}</p>

    <!-- QR Code (optional: shows QR of itself) -->
    <div>
        {!! QrCode::size(150)->generate(route('assets.show', $asset->id)) !!}
    </div>
</div>
@endsection
