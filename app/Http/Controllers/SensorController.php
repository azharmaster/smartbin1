<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\Device;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function index()
    {
        $sensors = Sensor::with('device.asset')->get(); // load device and its bin (asset)
        return view('sensors.index', compact('sensors'));
    }

    public function create()
    {
        $devices = Device::all();
        return view('sensors.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'battery' => 'nullable|numeric',
            'capacity' => 'required|numeric', // capacity is required for alert check
            'time' => 'required|date',
            'network' => 'nullable|string|max:50',
        ]);

        // Save the sensor reading
        $sensor = Sensor::create($request->only('device_id', 'battery', 'capacity', 'time', 'network'));

        $device = $sensor->device;
        $asset = $device->asset;

        // Only send alert if bin is not already full
        $latestSensors = $asset->devices()->with('latestSensor')->get()->pluck('latestSensor');

        // Check if any sensor in this bin already triggered full
        $alreadyFull = $latestSensors->filter(function ($s) {
            return $s && $s->capacity >= 85;
        });

        // Send notification only if this is the first full sensor in the bin
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
            'device_id' => 'required|exists:devices,id',
            'battery' => 'nullable|numeric',
            'capacity' => 'required|numeric', // capacity is required for alert check
            'time' => 'required|date',
            'network' => 'nullable|string|max:50',
        ]);

        $sensor->update($request->only('device_id', 'battery', 'capacity', 'time', 'network'));

        $device = $sensor->device;
        $asset = $device->asset;

        // Only send alert if bin is not already full
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
        $token = "PDVc#7eH-4YXkXcR5Yvn"; // Fonnte API token

        $supervisors = User::where('role', 4)
                           ->whereNotNull('phone')
                           ->pluck('phone');

        $message = "⚠️ *BIN FULL ALERT!* ⚠️\n\n";
        $message .= "Bin: {$asset->asset_name}\n";
        $message .= "Device: {$sensor->device->device_name}\n";
        $message .= "Current Capacity: {$sensor->capacity}%\n";
        $message .= "Time: {$sensor->time}\n\n";
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
