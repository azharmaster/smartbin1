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
        // Load all sensors with their device and the bin (asset) they belong to
        $sensors = Sensor::with('device.asset')->get();
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
            'capacity' => 'required|numeric', // required for alert check
            'time' => 'required|date',
            'network' => 'nullable|string|max:50',
        ]);

        // Save the sensor reading
        $sensor = Sensor::create($request->only('device_id', 'battery', 'capacity', 'time', 'network'));

        // Get device and the bin (asset) it belongs to
        $device = $sensor->device;
        $asset = $device->asset;

        // Fetch latest sensor readings of all devices in this bin
        $latestSensors = $asset->devices()->with('latestSensor')->get()->pluck('latestSensor');

        // Check if any sensor in this bin is already >= 85%
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
            'capacity' => 'required|numeric', // required for alert check
            'time' => 'required|date',
            'network' => 'nullable|string|max:50',
        ]);

        // Update the sensor reading
        $sensor->update($request->only('device_id', 'battery', 'capacity', 'time', 'network'));

        $device = $sensor->device;
        $asset = $device->asset;

        // Fetch latest sensor readings of all devices in this bin
        $latestSensors = $asset->devices()->with('latestSensor')->get()->pluck('latestSensor');

        // Check if any sensor in this bin is already >= 85%
        $alreadyFull = $latestSensors->filter(function ($s) {
            return $s && $s->capacity >= 85;
        });

        // Send notification only if this is the first full sensor in the bin
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

    /**
     * Send WhatsApp notification to all supervisors when a bin is full
     *
     * @param Asset $asset The bin that is full
     * @param Sensor $sensor The sensor that triggered the alert
     */
    protected function sendBinFullNotification(Asset $asset, Sensor $sensor)
    {
        $token = "PDVc#7eH-4YXkXcR5Yvn"; // Fonnte API token

        // Get all supervisors (role = 4) who have a phone number
        $supervisors = User::where('role', 4)
                           ->whereNotNull('phone')
                           ->pluck('phone');

        // Build WhatsApp message dynamically
        $message = "⚠️ *BIN FULL ALERT!* ⚠️\n\n";
        $message .= "Bin: {$asset->asset_name}\n";
        $message .= "Device: {$sensor->device->device_name}\n";
        $message .= "Current Capacity: {$sensor->capacity}%\n";
        $message .= "Time: {$sensor->time}\n\n";
        $message .= "Please take immediate action.";

        // Send message to each supervisor
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
