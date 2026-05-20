<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CollectionTripService
{
    private const COLLECTION_START_HOUR = 7;
    private const COLLECTION_END_HOUR = 22;
    private const CLEAR_HOLD_MINUTES = 60;

    public function getTrips(?string $dateFrom = null, ?string $dateTo = null, ?int $assetId = null): Collection
    {
        $rangeStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $rangeEnd = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        $assets = Asset::with([
            'floor',
            'capacitySetting',
            'devices.sensors' => fn ($q) => $q->orderBy('created_at', 'asc'),
        ])
            ->when($assetId, fn ($q) => $q->where('id', $assetId))
            ->where('is_active', 1)
            ->get();

        return $this->extractTrips($assets, $rangeStart, $rangeEnd);
    }

    public function getTripsForAsset(Asset $asset, ?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        if (!$asset->relationLoaded('devices')) {
            $asset->load([
                'floor',
                'capacitySetting',
                'devices.sensors' => fn ($q) => $q->orderBy('created_at', 'asc'),
            ]);
        }

        $rangeStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $rangeEnd = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        return $this->extractTrips(collect([$asset]), $rangeStart, $rangeEnd);
    }

    private function extractTrips(Collection $assets, ?Carbon $rangeStart, ?Carbon $rangeEnd): Collection
    {
        $collectionTrips = collect();

        foreach ($assets as $asset) {
            $capacitySetting = $asset->capacitySetting;
            $emptyTo = (float) ($capacitySetting?->empty_to ?? 0);
            $allReadings = collect();

            foreach ($asset->devices as $device) {
                foreach ($device->sensors as $sensor) {
                    if (!is_numeric($sensor->capacity)) {
                        continue;
                    }

                    $allReadings->push([
                        'asset_id' => $asset->id,
                        'asset_name' => $asset->asset_name,
                        'floor_name' => $asset->floor->floor_name ?? 'N/A',
                        'device_id' => $device->id,
                        'device_name' => $device->device_name ?? 'N/A',
                        'capacity' => (float) $sensor->capacity,
                        'created_at' => Carbon::parse($sensor->created_at)->timezone(config('app.timezone')),
                    ]);
                }
            }

            $allReadings = $allReadings->sortBy('created_at')->values();

            $previousCapacities = [];
            $lastClearedAt = null;
            $currentDay = null;

            foreach ($allReadings as $reading) {
                $deviceId = $reading['device_id'];
                $currentCap = $reading['capacity'];
                $previousCap = $previousCapacities[$deviceId] ?? null;
                $readingTime = $reading['created_at'];
                $readingDay = $readingTime->format('Y-m-d');

                if ($currentDay !== null && $currentDay !== $readingDay) {
                    $previousCapacities = [];
                    $lastClearedAt = null;
                }
                $currentDay = $readingDay;

                if (
                    $previousCap !== null &&
                    $previousCap > $emptyTo &&
                    $this->isCollectionCapacity($currentCap, $emptyTo) &&
                    !$this->isClearHoldActive($lastClearedAt, $readingTime)
                ) {
                    $lastClearedAt = $readingTime;

                    if (
                        $this->isWithinCollectionWindow($readingTime) &&
                        ($rangeStart === null || $readingTime->greaterThanOrEqualTo($rangeStart)) &&
                        ($rangeEnd === null || $readingTime->lessThanOrEqualTo($rangeEnd))
                    ) {
                        $collectionTrips->push([
                            'asset_id' => $reading['asset_id'],
                            'asset_name' => $reading['asset_name'],
                            'floor_name' => $reading['floor_name'],
                            'device_name' => $reading['device_name'],
                            'emptied_at' => $readingTime,
                            'emptied_date' => $readingTime->format('Y-m-d'),
                            'emptied_time' => $readingTime->format('H:i'),
                            'datetime_formatted' => $readingTime->format('d/m/Y h:i A'),
                            'diff_for_humans' => $readingTime->diffForHumans(),
                        ]);
                    }
                }

                $previousCapacities[$deviceId] = $currentCap;
            }
        }

        return $collectionTrips->sortByDesc('emptied_at')->values();
    }

    private function isWithinCollectionWindow(Carbon $timestamp): bool
    {
        $minutes = ($timestamp->hour * 60) + $timestamp->minute;
        $startMinutes = self::COLLECTION_START_HOUR * 60;
        $endMinutes = self::COLLECTION_END_HOUR * 60;

        return $minutes >= $startMinutes && $minutes <= $endMinutes;
    }

    private function isCollectionCapacity(float $capacity, float $emptyTo): bool
    {
        return $capacity <= $emptyTo;
    }

    private function isClearHoldActive(?Carbon $lastClearedAt, Carbon $readingTime): bool
    {
        return $lastClearedAt !== null &&
            $lastClearedAt->copy()->addMinutes(self::CLEAR_HOLD_MINUTES)->greaterThan($readingTime);
    }
}
