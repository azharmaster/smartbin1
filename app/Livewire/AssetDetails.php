<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\CapacitySetting;
use App\Models\Floor;
use App\Models\Device;

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
        $this->compartments = [];
        
        $devices = $this->asset->devices ?? collect();
        if ($devices->isEmpty()) return;

        $lerp = fn($a, $b, $t) => $a + ($b - $a) * $t;

        // ✅ SQUARE BIN (fits inside 320×240 viewBox)
        $binSize = 210;   // ← wider & taller
        $binX = 55;       // recenter horizontally
        $binY = 15; 

        $topLeft     = [$binX, $binY];
        $topRight    = [$binX + $binSize, $binY];
        $bottomLeft  = [$binX, $binY + $binSize];
        $bottomRight = [$binX + $binSize, $binY + $binSize];

        $n = $devices->count();
        $this->compartments = [];

        foreach ($devices as $i => $device) {
            $sensor = $device->sensors->sortByDesc('time')->first();
            $capacity = $sensor?->capacity ?? 0;
            $color = $this->capacityColor($capacity);

            // Equal compartments (same logic as before, now square & parallel)
            $t0 = $i / $n;
            $t1 = ($i + 1) / $n;

            $topCompLeft     = [$lerp($topLeft[0], $topRight[0], $t0), $topLeft[1]];
            $topCompRight    = [$lerp($topLeft[0], $topRight[0], $t1), $topRight[1]];
            $bottomCompLeft  = [$lerp($bottomLeft[0], $bottomRight[0], $t0), $bottomLeft[1]];
            $bottomCompRight = [$lerp($bottomLeft[0], $bottomRight[0], $t1), $bottomRight[1]];

            // 🔒 SAME dynamic fill logic as before
            $fillRatio = min(100, max(0, $capacity)) / 100;

            $fillTopLeft  = [
                $lerp($bottomCompLeft[0], $topCompLeft[0], $fillRatio),
                $lerp($bottomCompLeft[1], $topCompLeft[1], $fillRatio),
            ];

            $fillTopRight = [
                $lerp($bottomCompRight[0], $topCompRight[0], $fillRatio),
                $lerp($bottomCompRight[1], $topCompRight[1], $fillRatio),
            ];

            // SAME label math (unchanged behavior)
            $labelX = ($topCompLeft[0] + $topCompRight[0] + $bottomCompRight[0] + $bottomCompLeft[0]) / 4;
            $labelY = ($topCompLeft[1] + $topCompRight[1] + $bottomCompRight[1] + $bottomCompLeft[1]) / 4;

            $deviceNameY = $topCompLeft[1] - 6;
            $capacityTextY = $labelY + 8;

            $this->compartments[] = [
                'outline' => [$topCompLeft, $topCompRight, $bottomCompRight, $bottomCompLeft],
                'fill' => [$fillTopLeft, $fillTopRight, $bottomCompRight, $bottomCompLeft],
                'color' => $color,
                'label' => $device->device_name,
                'capacity' => $capacity,
                'battery' => $sensor?->battery,
                'network' => $sensor?->network,
                'lastUpdated' => $sensor?->time,
                'labelPos' => [$labelX, $labelY],
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

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted.');
    }

    public function render()
    {
        return view('livewire.asset-details', [
            'compartments' => $this->compartments,
            'assets' => $this->allAssets,
        ]);
    }
}