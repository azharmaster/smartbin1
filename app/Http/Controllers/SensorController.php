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
        $query = Sensor::with('device.asset')->orderBy('created_at', 'desc');

        // Apply search if there is a query
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                  ->orWhereHas('device', function ($deviceQuery) use ($search) {
                      $deviceQuery->where('device_name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('perPage', 10);
        $sensors = $query->paginate($perPage)->withQueryString();

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

        return view('sensors.index', compact('sensors', 'latestPerDevice'));
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

        $sensor = Sensor::create($request->only('device_id', 'battery', 'capacity', 'time', 'rsrp', 'nsr'));

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

        $sensor->update($request->only('device_id', 'battery', 'capacity', 'time', 'rsrp', 'nsr'));

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
