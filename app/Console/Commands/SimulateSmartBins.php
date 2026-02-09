<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Device;
use App\Models\Sensor;

class SimulateSmartBins extends Command
{
    protected $signature = 'smartbin:simulate';
    protected $description = 'Simulate SmartBin sensor data gradually (dummy only, no notifications)';

    public function handle()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $now = Carbon::now();

        $this->info("🟢 Simulating gradual sensor data at {$now->format('d-m-Y H:i:s')}");

        // Fetch all active devices
        $devices = Device::where('is_active', 1)->get();

        if ($devices->isEmpty()) {
            $this->info("⚠️ No active devices found.");
            return;
        }

        foreach ($devices as $device) {
            if (!$device->asset || $device->asset->is_active != 1) {
                $this->info("⚠️ Skipping {$device->device_name} (Asset inactive)");
                continue;
            }

            // Get the last sensor reading for this device
            $lastSensor = Sensor::where('device_id', $device->id_device)
                                ->latest('time')
                                ->first();

            if ($lastSensor) {
                $lastCapacity = $lastSensor->capacity;

                // If last value >= 80, reset to small random value (0–10)
                if ($lastCapacity >= 80) {
                    $newCapacity = rand(0, 10);
                } else {
                    // Gradually increase: add 2–10% to last value
                    $increment = rand(2, 10);
                    $newCapacity = min($lastCapacity + $increment, 100);
                }
            } else {
                // If no previous data, start small (0–10%)
                $newCapacity = rand(0, 10);
            }

            // Generate random values for rsrp and nsr
            $rsrp = rand(0, 1) ? -90 : -95;             // either -90 or -95
            $nsr  = collect([0, -5, -10])->random();    // 0, -5, or -10

            // Insert dummy sensor reading
            Sensor::create([
                'device_id' => $device->id_device,
                'capacity'  => $newCapacity,
                'battery'   => 3.5,
                'rsrp'      => $rsrp,
                'nsr'       => $nsr,
                'time'      => $now,
            ]);

            $this->info("✅ {$device->device_name} → Capacity: {$newCapacity}%, Battery: 95, RSRP: {$rsrp}, NSR: {$nsr}");
        }

        $this->info("🎉 Gradual simulation completed. No notifications were sent.");
    }
}
