<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\CapacitySetting;
use App\Models\Floor;
use App\Models\Device;
use App\Models\Sensor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDetails extends Component
{
    public $asset;
    public $floors;
    public $allAssets;
    public $compartments = [];
    public $capacitySetting;
    public $weeklyChartLabels = [];
    public $weeklyChartValues = [];
    public $weeklyChartMin = [];
    public $weeklyChartMax = [];
    public $weeklySensorDatasets = [];
    public $deviceStatuses = [];
    public $selectedDate;

    protected $listeners = ['refreshData' => 'refreshData'];

    public function mount($asset)
    {
        if (is_numeric($asset)) {
            $this->asset = Asset::with([
                'floor',
                'capacitySetting',      // ✅ IMPORTANT
                'devices.sensors',
            ])->findOrFail($asset);
        } elseif ($asset instanceof Asset) {
            $this->asset = $asset->load(['floor', 'devices.sensors']);
        } else {
            throw new \Exception('Invalid asset provided');
        }

        $this->capacitySetting = CapacitySetting::first();
        $this->allAssets = Asset::all();
        $this->floors = Floor::orderBy('floor_name')->get();
        
        // Get date from query parameter or default to today
        $this->selectedDate = request()->query('date', date('Y-m-d'));

        $this->deviceStatuses = [];
        foreach ($this->asset->devices as $device) {
            $this->deviceStatuses[$device->id_device] = $this->getLastFullAndClear($device, $this->asset->capacitySetting->half_to, $this->asset->capacitySetting->empty_to);
        }

        $this->prepareCompartments();
        $this->prepareDailyChart();
    }

    /**
     * 24-hour interval data for selected date
     */
    protected function prepareDailyChart()
    {
        $devices = $this->asset->devices;
        $selectedDate = Carbon::parse($this->selectedDate);

        // Create 24-hour slots (hourly intervals)
        $slots = collect();
        for ($hour = 0; $hour < 24; $hour++) {
            $slots->push(sprintf('%02d:00', $hour));
        }

        $this->weeklyChartLabels = $slots->values();
        $this->weeklySensorDatasets = [];

        foreach ($devices as $device) {
            // Get raw sensor data for the selected date
            $sensors = DB::table('sensors')
                ->select('capacity', 'created_at')
                ->where('device_id', $device->id_device)
                ->whereDate('created_at', $selectedDate)
                ->orderBy('created_at', 'asc')
                ->get();

            // Group by hourly slots
            $slotData = [];
            $slotTimestamps = [];

            foreach ($sensors as $sensor) {
                $hour = Carbon::parse($sensor->created_at)->format('H:00');
                
                if (!isset($slotData[$hour])) {
                    $slotData[$hour] = [];
                    $slotTimestamps[$hour] = [];
                }
                $slotData[$hour][] = $sensor->capacity;
                $slotTimestamps[$hour][] = Carbon::parse($sensor->created_at)->format('H:i:s');
            }

            // Build values and timestamps arrays
            $values = [];
            $timestamps = [];

            foreach ($slots as $slot) {
                if (isset($slotData[$slot]) && !empty($slotData[$slot])) {
                    // Calculate average
                    $avg = array_sum($slotData[$slot]) / count($slotData[$slot]);
                    $values[] = round($avg, 1);
                    // Use the latest timestamp for each slot
                    $timestamps[] = max($slotTimestamps[$slot]);
                } else {
                    $values[] = null;
                    $timestamps[] = null;
                }
            }

            $this->weeklySensorDatasets[] = [
                'label' => $device->device_name,
                'data'  => $values,
                'timestamps' => $timestamps,
            ];
        }
    }

    /**
     * ✅ FIXED: restore capacityTextY & deviceNameY
     */
    protected function prepareCompartments()
    {
        $this->compartments = [];

        $devices = $this->asset->devices ?? collect();
        if ($devices->isEmpty()) return;

        $lerp = fn($a, $b, $t) => $a + ($b - $a) * $t;

        $binSize = 210;
        $binX = 55;
        $binY = 15;

        $topLeft     = [$binX, $binY];
        $topRight    = [$binX + $binSize, $binY];
        $bottomLeft  = [$binX, $binY + $binSize];
        $bottomRight = [$binX + $binSize, $binY + $binSize];

        $n = $devices->count();

        foreach ($devices as $i => $device) {
            $sensor = $device->sensors->sortByDesc('created_at')->first();
            $capacity = $sensor?->capacity ?? 0;
            $color = $this->capacityColor($capacity);

            $t0 = $i / $n;
            $t1 = ($i + 1) / $n;

            $topCompLeft     = [$lerp($topLeft[0], $topRight[0], $t0), $topLeft[1]];
            $topCompRight    = [$lerp($topLeft[0], $topRight[0], $t1), $topRight[1]];
            $bottomCompLeft  = [$lerp($bottomLeft[0], $bottomRight[0], $t0), $bottomLeft[1]];
            $bottomCompRight = [$lerp($bottomLeft[0], $bottomRight[0], $t1), $bottomRight[1]];

            $fillRatio = min(100, max(0, $capacity)) / 100;

            $fillTopLeft  = [
                $lerp($bottomCompLeft[0], $topCompLeft[0], $fillRatio),
                $lerp($bottomCompLeft[1], $topCompLeft[1], $fillRatio),
            ];

            $fillTopRight = [
                $lerp($bottomCompRight[0], $topCompRight[0], $fillRatio),
                $lerp($bottomCompRight[1], $topCompRight[1], $fillRatio),
            ];

            $labelX = ($topCompLeft[0] + $topCompRight[0] + $bottomCompRight[0] + $bottomCompLeft[0]) / 4;
            $labelY = ($topCompLeft[1] + $topCompRight[1] + $bottomCompRight[1] + $bottomCompLeft[1]) / 4;

            $deviceNameY = $topCompLeft[1] - 6;
            $capacityTextY = $labelY + 8;

            $batteryVoltage = $sensor?->battery ?? 0;
            $batteryPercentage = $this->voltageToPercentage($batteryVoltage);
            $batteryStatus = $this->getBatteryStatus($batteryVoltage);

            $this->compartments[] = [
                'outline' => [$topCompLeft, $topCompRight, $bottomCompRight, $bottomCompLeft],
                'fill' => [$fillTopLeft, $fillTopRight, $bottomCompRight, $bottomCompLeft],
                'color' => $color,
                'label' => $device->device_name,
                'capacity' => $capacity,
                'battery' => $batteryVoltage,
                'battery_percentage' => $batteryPercentage,
                'battery_status' => $batteryStatus,
                'rsrp' => $sensor?->rsrp,
                'lastUpdated' => $sensor?->created_at,
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

    /**
     * Convert battery voltage to percentage based on the provided mapping
     */
    protected function voltageToPercentage($voltage)
    {
        if ($voltage >= 3.7) {
            return 100;
        } elseif ($voltage >= 3.6) {
            return 98;
        } elseif ($voltage >= 3.5) {
            return 95;
        } elseif ($voltage >= 3.4) {
            return 80;
        } elseif ($voltage >= 3.3) {
            return 20;
        } elseif ($voltage >= 3.2) {
            return 10;
        } elseif ($voltage >= 3.1) {
            return 8;
        } elseif ($voltage >= 3.0) {
            return 5;
        } elseif ($voltage >= 2.9) {
            return 3;
        } else {
            return 1;
        }
    }

    /**
     * Get battery status based on voltage
     */
    protected function getBatteryStatus($voltage)
    {
        if ($voltage <= 3.2) {
            return 'recommended_replacement';
        } elseif ($voltage <= 3.1) {
            return 'required_replacement';
        } else {
            return 'normal';
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

    private function getLastFullAndClear(Device $device, float $full_threshold, float $empty_threshold): array
    {
        $sensors = $device->sensors()
            ->whereNotNull('capacity')
            ->orderBy('created_at', 'asc')
            ->get();

        $lastFull = null;
        $lastClear = null;

        foreach ($sensors as $sensor) {
            $isFull  = $sensor->capacity >= $full_threshold;
            $isClear = $sensor->capacity <= $empty_threshold;

            // Track last full independently
            if ($isFull) {
                $lastFull = $sensor->created_at;
            }

            // Track last clear independently (latest clear reading)
            if ($isClear) {
                $lastClear = $sensor->created_at;
            }
        }

        return [
            'last_full'  => $lastFull,
            'last_clear' => $lastClear,
        ];
    }
}
