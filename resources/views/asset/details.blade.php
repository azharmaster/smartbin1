@extends($layout)
@section('content_title', $asset->asset_name)

@section('content')
    {{-- Render the Livewire component, passing the full asset --}}
    @livewire('asset-details', ['asset' => $asset])
@endsection