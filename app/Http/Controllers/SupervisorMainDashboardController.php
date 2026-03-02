<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Asset;
use App\Models\Device;
use App\Models\Sensor;
use Carbon\Carbon;

class SupervisorMainDashboardController extends Controller
{
    public function index()
    {
        // Load devices with their latest sensor & asset-floor relationship
        $devices = Device::with('latestSensor', 'asset.floor', 'asset.capacitySetting')->get();

        // Total devices
        $totalDevices = $devices->count();

        // Get capacity settings helper
        $getCapacityStatus = function($device) {
            if (!$device->latestSensor || !is_numeric($device->latestSensor->capacity) || !$device->asset->capacitySetting) {
                return 'undetected';
            }
            $capacity = $device->latestSensor->capacity;
            $setting = $device->asset->capacitySetting;
            if ($capacity > $setting->half_to) return 'full';
            if ($capacity > $setting->empty_to) return 'half';
            return 'empty';
        };

        // FULL > half_to threshold
        $fullDevicesCollection = $devices->filter(fn($d) => $getCapacityStatus($d) === 'full');
        $fullDevices = $fullDevicesCollection->count();

        // HALF empty_to to half_to
        $halfDevicesCollection = $devices->filter(fn($d) => $getCapacityStatus($d) === 'half');
        $halfDevices = $halfDevicesCollection->count();

        // EMPTY <= empty_to threshold
        $emptyDevicesCollection = $devices->filter(fn($d) => $getCapacityStatus($d) === 'empty');
        $emptyDevices = $emptyDevicesCollection->count();

        // UNDETECTED (no sensor or capacity null)
        $undetectedDevicesCollection = $devices->filter(fn($d) => $getCapacityStatus($d) === 'undetected');
        $undetectedDevices = $undetectedDevicesCollection->count();

        // Group devices by asset (bin name) and then by compartment type
        $groupedDevices = $devices->filter(fn($d) => $d->asset && $d->asset->is_active)
            ->groupBy(function($device) {
                // Group by asset name first
                return $device->asset->asset_name ?? 'Unknown Bin';
            })
            ->map(function($assetGroup) {
                // Within each asset, group by device/compartment type
                return $assetGroup->groupBy(function($device) {
                    // Extract compartment type from device_name
                    // e.g., "TRX Bin 01 - General" -> "General"
                    $parts = explode('-', $device->device_name ?? '');
                    $compartment = trim(end($parts));
                    return $compartment ?: 'Unknown';
                });
            });

        // Get all floors
        $floors = Floor::all();

        // Get assets that have coordinates (for map markers)
        $assetsWithCoords = Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();

        // Get last updated time
        $lastUpdated = Sensor::max('created_at');

        return view('adminmaindashboard', compact(
            'devices',
            'groupedDevices',
            'floors',
            'assetsWithCoords',
            'totalDevices',
            'fullDevices',
            'fullDevicesCollection',
            'halfDevices',
            'halfDevicesCollection',
            'emptyDevices',
            'emptyDevicesCollection',
            'undetectedDevices',
            'lastUpdated'
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