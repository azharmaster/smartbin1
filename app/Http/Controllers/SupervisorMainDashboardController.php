<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Asset;
use App\Models\Device;

class SupervisorMainDashboardController extends Controller
{
    public function index()
    {
        // Load devices with their latest sensor & asset-floor relationship
        $devices = Device::with('latestSensor', 'asset.floor')->get();

        // Total devices
        $totalDevices = $devices->count();

        // FULL > 85%
        $fullDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 85;
        });
        $fullDevices = $fullDevicesCollection->count();

        // HALF 40–85%
        $halfDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 40 &&
                   $d->latestSensor->capacity <= 85;
        });
        $halfDevices = $halfDevicesCollection->count();

        // EMPTY <= 40%
        $emptyDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity <= 40;
        });
        $emptyDevices = $emptyDevicesCollection->count();

        // UNDETECTED (no sensor or capacity null)
        $undetectedDevicesCollection = $devices->filter(function ($d) {
            return !$d->latestSensor || !is_numeric($d->latestSensor->capacity);
        });
        $undetectedDevices = $undetectedDevicesCollection->count();

        // Get all floors
        $floors = Floor::all();

        // Get assets that have coordinates (for map markers)
        $assetsWithCoords = Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();

        return view('adminmaindashboard', compact(
            'devices',  
            'floors',
            'assetsWithCoords',
            'totalDevices',
            'fullDevices',
            'fullDevicesCollection',
            'halfDevices',
            'halfDevicesCollection',
            'emptyDevices',
            'emptyDevicesCollection',
            'undetectedDevices'
        ));
    }

    // ✅ NEW METHOD (ADDED ONLY – NOTHING ELSE TOUCHED)
    public function binPopup($id)
    {
        $asset = Asset::with(['floor'])->findOrFail($id);

        $devices = Device::with(['latestSensor', 'asset.floor'])
            ->where('asset_id', $id)
            ->get();

        return view(
            'admin.dashboardpopupview.dashboard_bin_modal',
            compact('asset', 'devices')
        );
    }
}