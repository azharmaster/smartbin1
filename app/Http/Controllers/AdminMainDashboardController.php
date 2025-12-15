<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Asset;
use App\Models\Device;

class AdminMainDashboardController extends Controller
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

        // Get all floors
        $floors = Floor::all();

        // Get assets that have coordinates (for map markers)
        $assetsWithCoords = Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();

        return view('adminmaindashboard', compact(
            'floors',
            'assetsWithCoords',
            'fullDevices',
            'fullDevicesCollection',
            'halfDevices',
            'halfDevicesCollection',
        ));
    }
}
