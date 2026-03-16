<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\Device;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    public function index(Request $request)
    {
        // Get all sensors with relationships for client-side filtering
        $allSensors = Sensor::with('device.asset')->orderBy('created_at', 'desc')->get()
            ->map(function ($sensor) {
                // Calculate network_strength based on RSRP
                $rsrp = $sensor->rsrp;
                if ($rsrp !== null) {
                    $rsrpValue = floatval($rsrp);
                    if ($rsrpValue > -80) {
                        $sensor->network_strength = 'Strong';
                    } elseif ($rsrpValue > -100) {
                        $sensor->network_strength = 'Normal';
                    } elseif ($rsrpValue > -110) {
                        $sensor->network_strength = 'Week';
                    } else {
                        $sensor->network_strength = 'Very Week';
                    }
                }
                
                // Calculate battery percentage
                $voltage = $sensor->battery;
                if ($voltage !== null) {
                    $voltage = floatval($voltage);
                    if ($voltage >= 3.7) {
                        $sensor->battery_percentage = 100;
                    } elseif ($voltage >= 3.6) {
                        $sensor->battery_percentage = 98;
                    } elseif ($voltage >= 3.5) {
                        $sensor->battery_percentage = 95;
                    } elseif ($voltage >= 3.4) {
                        $sensor->battery_percentage = 80;
                    } elseif ($voltage >= 3.3) {
                        $sensor->battery_percentage = 20;
                    } elseif ($voltage >= 3.2) {
                        $sensor->battery_percentage = 10;
                    } elseif ($voltage >= 3.1) {
                        $sensor->battery_percentage = 8;
                    } elseif ($voltage >= 3.0) {
                        $sensor->battery_percentage = 5;
                    } elseif ($voltage >= 2.9) {
                        $sensor->battery_percentage = 3;
                    } elseif ($voltage >= 2.8) {
                        $sensor->battery_percentage = 1;
                    } else {
                        $sensor->battery_percentage = 0;
                    }
                } else {
                    $sensor->battery_percentage = 0;
                }
                
                return $sensor;
            });

        // Get all assets for dropdown
        $assets = Asset::orderBy('asset_name')->get();

        // ✅ UPDATED PART FOR CHART (JOIN DEVICE NAME + ASSET NAME)
        $latestPerDevice = Sensor::join('devices', 'sensors.device_id', '=', 'devices.id_device')
            ->join('assets', 'devices.asset_id', '=', 'assets.id')
            ->select(
                'sensors.device_id',
                'devices.device_name',
                'assets.asset_name',
                'sensors.capacity',
                'sensors.created_at',
                'sensors.rsrp',
                'sensors.nsr'
            )
            ->whereIn('sensors.id', function ($q) {
                $q->select(DB::raw('MAX(id)'))
                  ->from('sensors')
                  ->groupBy('device_id');
            })
            ->orderBy('sensors.device_id')
            ->get()
            ->map(function ($item) {
                $sensor = new Sensor([
                    'rsrp' => $item->rsrp,
                    'nsr' => $item->nsr
                ]);
                $item->network_strength = $sensor->network_strength;

                // Add device_name and asset_name for chart labels
                $item->device_name = $item->device_name ?? 'Unknown Device';
                $item->asset_name = $item->asset_name ?? 'Unknown Bin';

                // Extract last 4 digits of device_id for chart label
                $deviceId = (string)$item->device_id;
                $item->device_id_short = strlen($deviceId) >= 4 ? substr($deviceId, -4) : $deviceId;

                return $item;
            });

        return view('sensors.index', compact('allSensors', 'latestPerDevice', 'assets'));
    }

    public function create()
    {
        $devices = Device::all();
        return view('sensors.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id_device',
            'battery' => 'nullable|numeric',
            'capacity' => 'required|numeric',
            'time' => 'required|date',
            'rsrp' => 'nullable|string|max:50',
            'nsr' => 'nullable|string|max:50',
        ]);

        $sensor = Sensor::create($request->only('device_id', 'battery', 'capacity', 'created_at', 'rsrp', 'nsr'));

        $device = $sensor->device;
        $asset = $device->asset;

        $latestSensors = $asset->devices()->with('latestSensor')->get()->pluck('latestSensor');

        $alreadyFull = $latestSensors->filter(function ($s) {
            return $s && $s->capacity >= 85;
        });

        if ($sensor->capacity >= 85 && $alreadyFull->count() === 1) {
            $this->sendBinFullNotification($asset, $sensor);
        }

        return redirect()->route('sensors.index')->with('success', 'Sensor logged.');
    }

    public function show(Sensor $sensor)
    {
        return view('sensors.show', compact('sensor'));
    }

    public function edit(Sensor $sensor)
    {
        $devices = Device::all();
        return view('sensors.edit', compact('sensor', 'devices'));
    }

    public function update(Request $request, Sensor $sensor)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id_device',
            'battery' => 'nullable|numeric',
            'capacity' => 'required|numeric',
            'time' => 'required|date',
            'rsrp' => 'nullable|string|max:50',
            'nsr' => 'nullable|string|max:50',
        ]);

        $sensor->update($request->only('device_id', 'battery', 'capacity', 'created_at', 'rsrp', 'nsr'));

        $device = $sensor->device;
        $asset = $device->asset;

        $latestSensors = $asset->devices()->with('latestSensor')->get()->pluck('latestSensor');

        $alreadyFull = $latestSensors->filter(function ($s) {
            return $s && $s->capacity >= 85;
        });

        if ($sensor->capacity >= 85 && $alreadyFull->count() === 1) {
            $this->sendBinFullNotification($asset, $sensor);
        }

        return redirect()->route('sensors.index')->with('success', 'Sensor updated.');
    }

    public function destroy(Sensor $sensor)
    {
        $sensor->delete();
        return redirect()->route('sensors.index')->with('success', 'Sensor deleted.');
    }

    protected function sendBinFullNotification(Asset $asset, Sensor $sensor)
    {
        $token = "PDVc#7eH-4YXkXcR5Yvn";

        $supervisors = User::where('role', 4)
                           ->whereNotNull('phone')
                           ->pluck('phone');

        $message = "⚠️ *BIN FULL ALERT!* ⚠️\n\n";
        $message .= "Bin: {$asset->asset_name}\n";
        $message .= "Device: {$sensor->device->device_name}\n";
        $message .= "Current Capacity: {$sensor->capacity}%\n";
        $message .= "Time: {$sensor->created_at}\n\n";
        $message .= "Please take immediate action.";

        foreach ($supervisors as $phone) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '60',
                ],
                CURLOPT_HTTPHEADER => [
                    "Authorization: $token"
                ],
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                logger("Failed to send WhatsApp to {$phone}: " . curl_error($curl));
            } else {
                logger("WhatsApp sent to {$phone}: {$response}");
            }

            curl_close($curl);
        }
    }
}
