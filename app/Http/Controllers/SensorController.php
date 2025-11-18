<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\Device;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function index()
    {
        $sensors = Sensor::with('device')->get();
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
            'capacity' => 'nullable|numeric',
            'time' => 'required|date',
            'network' => 'nullable|string|max:50',
        ]);

        Sensor::create($request->only('device_id', 'battery', 'capacity', 'time', 'network'));

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
            'capacity' => 'nullable|numeric',
            'time' => 'required|date',
            'network' => 'nullable|string|max:50',
        ]);

        $sensor->update($request->only('device_id', 'battery', 'capacity', 'time', 'network'));

        return redirect()->route('sensors.index')->with('success', 'Sensor updated.');
    }

    public function destroy(Sensor $sensor)
    {
        $sensor->delete();
        return redirect()->route('sensors.index')->with('success', 'Sensor deleted.');
    }
}
