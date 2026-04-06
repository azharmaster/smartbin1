<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\CapacitySetting;
use App\Models\Floor;
use App\Models\Device;
use App\Models\Sensor;
use App\Services\CollectionTripService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDetails extends Component
{
    private const COLLECTION_START_HOUR = 7;
    private const COLLECTION_END_HOUR = 19;

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
    protected CollectionTripService $collectionTripService;

    protected $listeners = ['refreshData' => 'refreshData'];

    public function boot(CollectionTripService $collectionTripService)
    {
        $this->collectionTripService = $collectionTripService;
    }

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
        $chartStart = $selectedDate->copy()->timezone(config('app.timezone'))->startOfDay();
        $chartEnd = $selectedDate->copy()->timezone(config('app.timezone'))->endOfDay();
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

            $withinCollectionWindow = $this->isWithinCollectionWindow($readingTime);

            // Clear Bin detection
            if (!$binCleared) {
                if ($previousCap !== null && $previousCap > 10 && $this->isCollectionCapacity($currentCap)) {
                    $binCleared = true;
                    $triggeredDeviceId = $deviceId;
                    if ($isSelectedDate && $withinCollectionWindow) {
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
            $daySensors = collect();
            $bridgeReading = null;

            foreach ($sensors as $sensor) {
                $t = Carbon::parse($sensor->created_at)->timezone(config('app.timezone'));

                if ($t->lessThanOrEqualTo($chartStart)) {
                    $bridgeReading = $sensor;
                }

                if ($t->betweenIncluded($chartStart, $chartEnd)) {
                    $daySensors->push([
                        'record' => $sensor,
                        'time' => $t,
                    ]);
                }
            }

            if ($bridgeReading) {
                $data[] = [
                    'x'         => $chartStart->format('Y-m-d\TH:i:s'),
                    'y'         => round((float) $bridgeReading->capacity, 1),
                    'timestamp' => $chartStart->format('H:i:s') . ' (day start)',
                ];
            } elseif ($prevReading) {
                $data[] = [
                    'x'         => $chartStart->format('Y-m-d\TH:i:s'),
                    'y'         => round((float) $prevReading->capacity, 1),
                    'timestamp' => $chartStart->format('H:i:s') . ' (prev day)',
                ];
            }

            foreach ($daySensors as $sensorData) {
                $sensor = $sensorData['record'];
                $t = $sensorData['time'];
                $data[] = [
                    'x'         => $t->format('Y-m-d\TH:i:s'),
                    'y'         => round((float) $sensor->capacity, 1),
                    'timestamp' => $t->format('H:i:s'),
                ];
            }

            // Calculate end time — round up last data point to nearest 30 min
            $lastTime = $chartEnd->copy();
            if ($daySensors->isNotEmpty()) {
                $lastSensor = $daySensors->last()['time']->copy();
                $minutes = $lastSensor->minute;
                $roundedMinutes = $minutes === 0 ? 0 : ($minutes <= 30 ? 30 : 60);
                if ($roundedMinutes === 60) {
                    $lastTime = $lastSensor->addHour()->setMinute(0)->setSecond(0);
                } else {
                    $lastTime = $lastSensor->setMinute($roundedMinutes)->setSecond(0);
                }
                if ($lastTime->greaterThan($chartEnd)) {
                    $lastTime = $chartEnd->copy();
                }
            }

            $this->weeklySensorDatasets[] = [
                'label'    => $device->device_name,
                'data'     => $data,
                'end_time' => $lastTime ? $lastTime->format('Y-m-d\TH:i:s') : null,
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
        $this->clearBinHistory = $this->collectionTripService
            ->getTripsForAsset($this->asset)
            ->map(fn ($trip) => [
                'datetime' => $trip['datetime_formatted'],
                'date' => $trip['emptied_at']->format('d/m/Y'),
                'time' => $trip['emptied_at']->format('h:i A'),
                'compartment' => $trip['device_name'],
                'ago' => $trip['diff_for_humans'],
            ])
            ->values()
            ->all();
    }

    private function isWithinCollectionWindow(Carbon $timestamp): bool
    {
        $start = $timestamp->copy()->timezone(config('app.timezone'))
            ->setTime(self::COLLECTION_START_HOUR, 0, 0);
        $end = $timestamp->copy()->timezone(config('app.timezone'))
            ->setTime(self::COLLECTION_END_HOUR, 0, 0);

        return $timestamp->copy()->timezone(config('app.timezone'))->betweenIncluded($start, $end);
    }

    private function collectionWindowStart(Carbon $date): Carbon
    {
        return $date->copy()
            ->timezone(config('app.timezone'))
            ->setTime(self::COLLECTION_START_HOUR, 0, 0);
    }

    private function collectionWindowEnd(Carbon $date): Carbon
    {
        return $date->copy()
            ->timezone(config('app.timezone'))
            ->setTime(self::COLLECTION_END_HOUR, 0, 0);
    }

    private function isCollectionCapacity(float $capacity): bool
    {
        return $capacity <= 0.0 || abs($capacity) < 0.00001;
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
            if ($isClear && $this->isWithinCollectionWindow(Carbon::parse($sensor->created_at)->timezone(config('app.timezone')))) {
                $lastClear = $sensor->created_at;
            }
        }

        return [
            'last_full'  => $lastFull,
            'last_clear' => $lastClear,
        ];
    }
}
