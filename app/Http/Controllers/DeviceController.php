<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public $allAssets;

    public function index()
    {
        $devices = Device::with(['asset', 'latestSensor'])->get();
        $assets = Asset::all(); // fetch all assets for dropdown

        return view('devices.index', compact('devices', 'assets'));
    }

    public function create()
    {
        $assets = Asset::all();

        return view('devices.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id'    => 'required|exists:assets,id',
            'device_name' => 'nullable|string|max:255',
            'id_device'   => 'required|string|max:32|unique:devices,id_device',
            'serialno'    => 'nullable|string|max:255',
            'simcard'     => 'nullable|string|max:255',
        ]);

        Device::create($request->only(
            'asset_id',
            'device_name',
            'id_device',
            'serialno',
            'simcard'
        ));

        return redirect()->back()->with('success', 'Device created.');
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
            'asset_id'    => 'required|exists:assets,id',
            'device_name' => 'nullable|string|max:255',
            'id_device'   => [
                'required',
                'string',
                'max:32',
                Rule::unique('devices', 'id_device')->ignore($device->id),
            ],
            'serialno'    => 'nullable|string|max:255',
            'simcard'     => 'nullable|string|max:255',
        ]);

        $device->update($request->only(
            'asset_id',
            'device_name',
            'id_device',
            'serialno',
            'simcard'
        ));

        return redirect()->back()->with('success', 'Device updated.');
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->back()->with('success', 'Device deleted.');
    }
}
