<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;

class AssetDetails extends Component
{
    public Asset $asset;

    public function mount(Asset $asset)
    {
        // Asset is already loaded by controller
        $this->asset = $asset->load(['floor', 'devices.sensors']);
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
