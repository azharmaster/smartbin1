@extends('layouts.app')
@section('content_title', 'Asset Details')

@section('content')
    @livewire('asset-details', ['asset' => $asset_id])
@endsection
