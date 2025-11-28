@extends('layouts.app')
@section('content_title', 'Asset Details')

@section('content')
    @if(isset($asset_id))
        {{-- Render the Livewire component --}}
        @livewire('asset-details', ['asset' => $asset_id])
    @else
        <p style="color:red;">Asset ID not provided.</p>
    @endif
@endsection
