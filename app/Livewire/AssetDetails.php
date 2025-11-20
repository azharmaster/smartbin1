<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;

class AssetDetails extends Component
{
    public $asset;

    public function mount($asset)
    {
        // If $asset is an ID, load the full Asset
        $this->asset = Asset::with(['floor', 'devices.sensors'])->findOrFail($asset);
    }

public function updatePosition($assetId, $x, $y)
{
    $asset = Asset::find($assetId);
    if ($asset) {
        $asset->x = $x;
        $asset->y = $y;
        $asset->save();
    }

    $this->asset->refresh();
}

    public function render()
    {
        return view('livewire.asset-details');
    }
}