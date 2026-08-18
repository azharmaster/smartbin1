<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Floor;
use App\Models\Asset;
use Illuminate\Support\Collection;
use App\Models\CapacitySetting;
use App\Models\Sensor;
use Carbon\Carbon;


class AdminMainDashboardController extends Controller
{
    private const DASHBOARD_HISTORY_DAYS = 14;

    /**
     * Display Admin Main Dashboard
     */
    public function index()
    {
        /** @var Collection<int, Device> $devices */
        $devices = $this->loadDevicesWithLatestSensor();
        $devices->each(fn ($device) => $device->setRelation('latestSensor', $device->sensors->last()));

        $totalDevices = $devices->count();

        $totalDevices = $devices->count();
        $fullDevicesCollection = $this->countFullDevices($devices);
        $fullDevices = $fullDevicesCollection->count();
        $halfDevicesCollection = $this->countHalfDevices($devices);
        $halfDevices = $halfDevicesCollection->count();
        $emptyDevicesCollection = $this->countEmptyDevicesCollection($devices);
        $emptyDevices = $emptyDevicesCollection->count();
        $undetectedDevices = $this->countUndetectedDevices($devices);

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

        $lastEmptiedTimes = collect();
        
        // Get last emptied times for each bin (asset)
        $lastEmptiedTimesByBin = $this->getLastEmptiedTimesByBin($devices);

        $lastUpdated = Sensor::max('created_at');

        $floors = collect();
        $assetsWithCoords = collect();

        return view('adminmaindashboard', compact(
            'devices',
            'groupedDevices',
            'lastEmptiedTimes',
            'lastEmptiedTimesByBin',
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

    private function countEmptyDevicesCollection($devices)
{
    return $devices->filter(fn($d) =>
        $d->latestSensor &&
        $d->asset?->capacitySetting &&
        is_numeric($d->latestSensor->capacity) &&
        $d->latestSensor->capacity <= $d->asset->capacitySetting->empty_to
    );
}

    private function loadDevicesWithLatestSensor(): Collection
    {
        $historyStart = Carbon::now()->subDays(self::DASHBOARD_HISTORY_DAYS);

        return Device::with([
            'asset.floor',
            'asset.capacitySetting',
            'sensors' => fn ($q) => $q->where('created_at', '>=', $historyStart)
                ->orderBy('created_at', 'asc'),
        ])->get();
    }

    /** Capacity > 85% */
    private function countFullDevices($devices)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            $d->asset?->capacitySetting &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > $d->asset->capacitySetting->half_to
        );
    }

    private function countHalfDevices($devices)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            $d->asset?->capacitySetting &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity > $d->asset->capacitySetting->empty_to &&
            $d->latestSensor->capacity <= $d->asset->capacitySetting->half_to
        );
    }

    private function countEmptyDevices($devices)
    {
        return $devices->filter(fn($d) =>
            $d->latestSensor &&
            $d->asset?->capacitySetting &&
            is_numeric($d->latestSensor->capacity) &&
            $d->latestSensor->capacity <= $d->asset->capacitySetting->empty_to
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

    /**
     * Get the last emptied time for each bin (asset).
     * Tracks when any compartment in the bin went from full/half to empty/negative.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getLastEmptiedTimesByBin(Collection $devices)
    {
        $result = [];

        foreach ($devices as $device) {
            if (!$device->asset || !$device->asset->capacitySetting) continue;

            $assetId = $device->asset->id;
            $capacity = $device->asset->capacitySetting;
            $sensors = $device->sensors->sortByDesc('created_at')->values();

            // Initialize if not set
            if (!isset($result[$assetId])) {
                $result[$assetId] = null;
            }

            $wasFullOrHalf = false;
            $previousCapacity = null;

            foreach ($sensors as $sensor) {
                if (!is_numeric($sensor->capacity)) continue;

                $currentCapacity = $sensor->capacity;

                // Check if compartment was full or half (capacity > empty_to)
                if (!$wasFullOrHalf && $previousCapacity !== null && $previousCapacity > $capacity->empty_to) {
                    $wasFullOrHalf = true;
                }

                // Check if compartment was emptied (capacity goes negative or <= empty_to after being full/half)
                if ($wasFullOrHalf && ($currentCapacity < 0 || $currentCapacity <= $capacity->empty_to)) {
                    $emptiedTime = \Carbon\Carbon::parse($sensor->created_at);

                    // Keep the most recent emptied time for this bin
                    if (!$result[$assetId] || $emptiedTime > $result[$assetId]) {
                        $result[$assetId] = $emptiedTime;
                    }

                    $wasFullOrHalf = false; // reset for next cycle
                }

                $previousCapacity = $currentCapacity;
            }
        }

        return collect($result);
    }

    /**
     * Get the last emptied time for each device (compartment).
     * Tracks when each compartment went from full/half to empty/negative.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getLastEmptiedTimesByDevice()
    {
        $result = [];

        $devices = Device::with([
            'asset.capacitySetting',
            'sensors' => fn ($q) => $q->orderBy('created_at', 'desc')
        ])->get();

        foreach ($devices as $device) {
            if (!$device->asset || !$device->asset->capacitySetting) continue;

            $deviceId = $device->id_device;
            $capacity = $device->asset->capacitySetting;
            $sensors = $device->sensors;

            // Initialize if not set
            if (!isset($result[$deviceId])) {
                $result[$deviceId] = null;
            }

            $wasFullOrHalf = false;
            $previousCapacity = null;

            foreach ($sensors as $sensor) {
                if (!is_numeric($sensor->capacity)) continue;

                $currentCapacity = $sensor->capacity;

                // Check if compartment was full or half (capacity > empty_to)
                if (!$wasFullOrHalf && $previousCapacity !== null && $previousCapacity > $capacity->empty_to) {
                    $wasFullOrHalf = true;
                }

                // Check if compartment was emptied (capacity goes negative or <= empty_to after being full/half)
                if ($wasFullOrHalf && ($currentCapacity < 0 || $currentCapacity <= $capacity->empty_to)) {
                    $emptiedTime = \Carbon\Carbon::parse($sensor->created_at);

                    // Keep the most recent emptied time
                    if (!$result[$deviceId] || $emptiedTime > $result[$deviceId]) {
                        $result[$deviceId] = $emptiedTime;
                    }

                    $wasFullOrHalf = false; // reset for next cycle
                }

                $previousCapacity = $currentCapacity;
            }
        }

        return collect($result);
    }
}
