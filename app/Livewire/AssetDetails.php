<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;

class AssetDetails extends Component
{
    public $asset;

public function mount($asset)
{
    // $asset could be an ID (int/string) or a model
    if (is_numeric($asset)) {
        $this->asset = Asset::with(['floor', 'devices.sensors'])->findOrFail($asset);
    } elseif ($asset instanceof Asset) {
        $this->asset = $asset->load(['floor', 'devices.sensors']);
    } else {
        throw new \Exception('Invalid asset provided');
    }
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
