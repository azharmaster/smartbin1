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
    public $clearBinHistory = [];
    public $chartClearEvents = [];
    public $chartFullEvents = [];

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
        $this->prepareClearBinHistory();
    }

    /**
     * Show all sensor data points ordered by created_at
     * Also detects Clear Bin & Full events for chart markers
     * Prepends last reading from previous day to connect line from 00:00
     */
    protected function prepareDailyChart()
    {
        $devices = $this->asset->devices;
        $selectedDate = Carbon::parse($this->selectedDate);
        $prevDate = $selectedDate->copy()->subDay();
        $capacitySetting = $this->asset->capacitySetting;

        $this->weeklyChartLabels = [];
        $this->weeklySensorDatasets = [];
        $this->chartClearEvents = [];
        $this->chartFullEvents = [];

        // --- Collect ALL sensor readings across all devices for clear/full detection ---
        $allReadings = collect();
        foreach ($devices as $device) {
            $allSensors = DB::table('sensors')
                ->select('capacity', 'created_at')
                ->where('device_id', $device->id_device)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($allSensors as $s) {
                $allReadings->push([
                    'device_id'   => $device->id,
                    'device_name' => $device->device_name,
                    'capacity'    => (float) $s->capacity,
                    'created_at'  => Carbon::parse($s->created_at)->timezone(config('app.timezone')),
                ]);
            }
        }
        $allReadings = $allReadings->sortBy('created_at')->values();

        // --- Detect Clear Bin & Full events ---
        $previousCapacities = [];
        $binCleared         = false;
        $triggeredDeviceId  = null;
        $clearEventCount    = 0;

        $currentDay = null;

        foreach ($allReadings as $reading) {
            $deviceId    = $reading['device_id'];
            $currentCap  = $reading['capacity'];
            $previousCap = $previousCapacities[$deviceId] ?? null;
            $readingTime = $reading['created_at'];
            $readingDay  = $readingTime->format('Y-m-d');
            $isSelectedDate = $readingDay === $this->selectedDate;

            // Reset state when day changes
            if ($currentDay !== null && $currentDay !== $readingDay) {
                $binCleared        = false;
                $triggeredDeviceId = null;
                $previousCapacities = [];
                if ($readingDay === $this->selectedDate) {
                    $clearEventCount = 0;
                }
            }
            $currentDay = $readingDay;

            // Clear Bin detection
            if (!$binCleared) {
                if ($previousCap !== null && $previousCap > 10 && $currentCap <= 0) {
                    $binCleared = true;
                    $triggeredDeviceId = $deviceId;
                    if ($isSelectedDate) {
                        $clearEventCount++;
                        $this->chartClearEvents[] = [
                            'time'  => $readingTime->format('H:i'),
                            'count' => $clearEventCount,
                            'datetime' => $this->selectedDate . 'T' . $readingTime->format('H:i:s'),
                        ];
                    }
                }
            } else {
                if ($deviceId === $triggeredDeviceId && $currentCap > 10) {
                    $binCleared = false;
                    $triggeredDeviceId = null;
                }
            }

            // Full Bin detection
            if ($capacitySetting && $isSelectedDate) {
                if ($previousCap !== null && $previousCap <= $capacitySetting->half_to && $currentCap > $capacitySetting->half_to) {
                    $this->chartFullEvents[] = $readingTime->format('H:i');
                }
            }

            $previousCapacities[$deviceId] = $currentCap;
        }

        // --- Build per-device datasets for selected date ---
        foreach ($devices as $device) {
            // Get last reading from previous day to connect line from 00:00
            $prevReading = DB::table('sensors')
                ->select('capacity', 'created_at')
                ->where('device_id', $device->id_device)
                ->whereDate('created_at', $prevDate)
                ->orderBy('created_at', 'desc')
                ->first();

            // Get all readings for selected date
            $sensors = DB::table('sensors')
                ->select('capacity', 'created_at')
                ->where('device_id', $device->id_device)
                ->whereDate('created_at', $selectedDate)
                ->orderBy('created_at', 'asc')
                ->get();

            $data = [];

            // Prepend previous day last reading at 00:00:00 of selected date
            if ($prevReading) {
                $data[] = [
                    'x'         => $this->selectedDate . 'T00:00:00',
                    'y'         => round((float) $prevReading->capacity, 1),
                    'timestamp' => '00:00:00 (prev day)',
                ];
            }

            foreach ($sensors as $sensor) {
                $t = Carbon::parse($sensor->created_at);
                $data[] = [
                    'x'         => $this->selectedDate . 'T' . $t->format('H:i:s'),
                    'y'         => round((float) $sensor->capacity, 1),
                    'timestamp' => $t->format('H:i:s'),
                ];
            }

            // Calculate end time — round up last data point to nearest 30 min
            $lastTime = null;
            if ($sensors->isNotEmpty()) {
                $lastSensor = Carbon::parse($sensors->last()->created_at);
                $minutes = $lastSensor->minute;
                $roundedMinutes = $minutes < 30 ? 30 : 60;
                $lastTime = $lastSensor->copy()->setMinute($roundedMinutes)->setSecond(0);
                if ($roundedMinutes === 60) {
                    $lastTime->addHour()->setMinute(0);
                }
            }

            $this->weeklySensorDatasets[] = [
                'label'    => $device->device_name,
                'data'     => $data,
                'end_time' => $lastTime ? $this->selectedDate . 'T' . $lastTime->format('H:i:s') : null,
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

    /**
     * Prepare cleared bin history for this asset (all time)
     */
    protected function prepareClearBinHistory()
    {
        $this->clearBinHistory = [];
        $devices = $this->asset->devices;

        $allReadings = collect();
        foreach ($devices as $device) {
            $sensors = DB::table('sensors')
                ->select('capacity', 'created_at')
                ->where('device_id', $device->id_device)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($sensors as $s) {
                $allReadings->push([
                    'device_id'   => $device->id,
                    'device_name' => $device->device_name ?? 'N/A',
                    'capacity'    => (float) $s->capacity,
                    'created_at'  => Carbon::parse($s->created_at)->timezone(config('app.timezone')),
                ]);
            }
        }
        $allReadings = $allReadings->sortBy('created_at')->values();

        $previousCapacities = [];
        $binCleared         = false;
        $triggeredDeviceId  = null;

        foreach ($allReadings as $reading) {
            $deviceId    = $reading['device_id'];
            $currentCap  = $reading['capacity'];
            $previousCap = $previousCapacities[$deviceId] ?? null;
            $readingTime = $reading['created_at'];

            if (!$binCleared) {
                if ($previousCap !== null && $previousCap > 10 && $currentCap <= 0) {
                    $binCleared        = true;
                    $triggeredDeviceId = $deviceId;

                    $this->clearBinHistory[] = [
                        'datetime'     => $readingTime->format('d/m/Y h:i A'),
                        'date'         => $readingTime->format('d/m/Y'),
                        'time'         => $readingTime->format('h:i A'),
                        'compartment'  => $reading['device_name'],
                        'ago'          => $readingTime->diffForHumans(),
                    ];
                }
            } else {
                if ($deviceId === $triggeredDeviceId && $currentCap > 10) {
                    $binCleared        = false;
                    $triggeredDeviceId = null;
                }
            }

            $previousCapacities[$deviceId] = $currentCap;
        }

        // Most recent first
        $this->clearBinHistory = array_reverse($this->clearBinHistory);
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
