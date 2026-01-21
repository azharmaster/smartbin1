<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Floor;
use App\Models\Asset;
use Illuminate\Support\Collection;
use App\Models\CapacitySetting;
use App\Models\Sensor;


class AdminMainDashboardController extends Controller
{
    /**
     * Display Admin Main Dashboard
     */
    public function index()
    {
        /** @var Collection<int, Device> $devices */
        $devices = $this->loadDevicesWithLatestSensor();

        // ✅ Load capacity settings from DB
        $capacity = CapacitySetting::first();
        $emptyMax = $capacity->empty_to;
        $halfMax  = $capacity->half_to;

        $totalDevices = $devices->count();

        $totalDevices = $devices->count();
        $fullDevicesCollection = $this->countFullDevices($devices, $halfMax);
        $fullDevices = $fullDevicesCollection->count();
        $halfDevicesCollection = $this->countHalfDevices($devices, $emptyMax, $halfMax);
        $halfDevices = $halfDevicesCollection->count();
        $emptyDevicesCollection = $this->countEmptyDevicesCollection($devices, $emptyMax);
        $emptyDevices = $emptyDevicesCollection->count();
        $undetectedDevices = $this->countUndetectedDevices($devices);

        $lastUpdated = Sensor::latest('created_at')->value('created_at');

        $floors = Floor::all();
        $assetsWithCoords = $this->loadAssetsWithCoordinates();

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
            'undetectedDevices',
            'lastUpdated',
        ));
    }

    /**
     * Popup modal for bin details
     */
    public function binPopup($id)
    {
        $asset = Asset::with('floor')->findOrFail($id);

        $devices = Device::with(['latestSensor', 'asset.floor'])
            ->where('asset_id', $id)
            ->get();

        return view(
            'admin.dashboardpopupview.dashboard_bin_modal',
            compact('asset', 'devices')
        );
    }

    private function countEmptyDevicesCollection($devices, $emptyMax)
{
    return $devices->filter(fn($d) =>
        $d->latestSensor &&
        is_numeric($d->latestSensor->capacity) &&
        $d->latestSensor->capacity <= $emptyMax
    );
}

    private function loadDevicesWithLatestSensor(): Collection
    {
        return Device::with([
            'latestSensor',
            'asset.floor'
        ])->get();
    }

    /** Capacity > 85% */
    private function countFullDevices($devices, $halfMax)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > $halfMax
        );
    }

    private function countHalfDevices($devices, $emptyMax, $halfMax)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > $emptyMax &&
            $d->latestSensor->capacity <= $halfMax
        );
    }

    private function countEmptyDevices($devices, $emptyMax)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity <= $emptyMax
        )->count();
    }

    /** No sensor or invalid capacity */
    private function countUndetectedDevices(Collection $devices): int
    {
        return $devices->filter(fn ($d) =>
            !$d->latestSensor ||
            !is_numeric($d->latestSensor->capacity)
        )->count();
    }

    /** Assets for map markers */
    private function loadAssetsWithCoordinates()
    {
        return Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();
    }
}
