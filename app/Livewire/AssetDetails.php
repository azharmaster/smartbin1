<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\CapacitySetting;
use App\Models\Floor;

class AssetDetails extends Component
{
    public $asset;
    public $floors;
    public $allAssets;
    public $compartments = [];
    public $capacitySetting;

    public function mount($asset)
    {
        // Load the asset with relationships
        if (is_numeric($asset)) {
            $this->asset = Asset::with(['floor', 'devices.sensors'])->findOrFail($asset);
        } elseif ($asset instanceof Asset) {
            $this->asset = $asset->load(['floor', 'devices.sensors']);
        } else {
            throw new \Exception('Invalid asset provided');
        }

        // Load capacity settings
        $this->capacitySetting = CapacitySetting::first();
        $this->allAssets = Asset::all();
        $this->floors = Floor::orderBy('floor_name')->get();

        // Prepare SVG data
        $this->prepareCompartments();
    }

    protected function prepareCompartments()
    {
        $devices = $this->asset->devices ?? collect();

        // Skip if no devices
        if ($devices->isEmpty()) return;

        // Linear interpolation helper
        $lerp = fn($a, $b, $t) => $a + ($b - $a) * $t;

        // Bin corners
        $topLeft     = [50, 30];
        $topRight    = [305, 20];
        $bottomLeft  = [70, 210];
        $bottomRight = [300, 210];

        $n = $devices->count();
        $this->compartments = [];

        foreach ($devices as $i => $device) {
            $sensor = $device->sensors->sortByDesc('time')->first();
            $capacity = $sensor?->capacity ?? 0;

            // Helper for color
            $color = $this->capacityColor($capacity);

            $t0 = $i / $n;
            $t1 = ($i + 1) / $n;

            $topCompLeft  = [$lerp($topLeft[0], $topRight[0], $t0), $lerp($topLeft[1], $topRight[1], $t0)];
            $topCompRight = [$lerp($topLeft[0], $topRight[0], $t1), $lerp($topLeft[1], $topRight[1], $t1)];
            $bottomCompLeft  = [$lerp($bottomLeft[0], $bottomRight[0], $t0), $lerp($bottomLeft[1], $bottomRight[1], $t0)];
            $bottomCompRight = [$lerp($bottomLeft[0], $bottomRight[0], $t1), $lerp($bottomLeft[1], $bottomRight[1], $t1)];

            $fillRatio = min(100, max(0, $capacity)) / 100;

            $fillTopLeft  = [$lerp($bottomCompLeft[0], $topCompLeft[0], $fillRatio), $lerp($bottomCompLeft[1], $topCompLeft[1], $fillRatio)];
            $fillTopRight = [$lerp($bottomCompRight[0], $topCompRight[0], $fillRatio), $lerp($bottomCompRight[1], $topCompRight[1], $fillRatio)];

            $labelX = ($topCompLeft[0] + $topCompRight[0] + $bottomCompRight[0] + $bottomCompLeft[0]) / 4;
            $labelY = ($topCompLeft[1] + $topCompRight[1] + $bottomCompRight[1] + $bottomCompLeft[1]) / 4;

            $deviceNameY = $topCompLeft[1] - 6;
            $capacityTextY = $labelY + 8;

            $this->compartments[] = [
                'outline'     => [$topCompLeft, $topCompRight, $bottomCompRight, $bottomCompLeft],
                'fill'        => [$fillTopLeft, $fillTopRight, $bottomCompRight, $bottomCompLeft],
                'color'       => $color,
                'label'       => $device->device_name,
                'capacity'    => $capacity,
                'battery'     => $sensor?->battery,
                'network'     => $sensor?->network,
                'lastUpdated' => $sensor?->time,
                'labelPos'    => [$labelX, $labelY],
                'deviceNameY' => $deviceNameY,
                'capacityTextY' => $capacityTextY,
            ];
        }
    }

    protected function capacityColor($capacity)
    {
        if ($capacity <= $this->capacitySetting->empty_to) return '#1b4f1f';
        if ($capacity <= $this->capacitySetting->half_to) return '#f2c224';
        return '#e74c3c';
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
        return view('livewire.asset-details', [
            'compartments' => $this->compartments,
            'assets' => $this->allAssets,
        ]);
    }
}