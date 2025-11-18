<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Asset;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('asset')->get();
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        $assets = Asset::all();
        return view('devices.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'device_name' => 'nullable|string|max:255',
        ]);

        Device::create($request->only('asset_id', 'device_name'));

        return redirect()->route('devices.index')->with('success', 'Device created.');
    }

    public function show(Device $device)
    {
        return view('devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        $assets = Asset::all();
        return view('devices.edit', compact('device', 'assets'));
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'device_name' => 'nullable|string|max:255',
        ]);

        $device->update($request->only('asset_id', 'device_name'));

        return redirect()->route('devices.index')->with('success', 'Device updated.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted.');
    }
}
