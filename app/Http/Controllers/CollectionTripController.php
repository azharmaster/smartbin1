<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Asset;
use App\Models\CapacitySetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionTripController extends Controller
{
    /**
     * Display collection trips with date filter.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get date filters
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        // Validate dates
        if ($dateFrom > $dateTo) {
            $temp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $temp;
        }

        // Get collection trips
        $collectionTrips = $this->getCollectionTrips($dateFrom, $dateTo);

        // Get summary statistics
        $totalTrips = $collectionTrips->count();
        $uniqueBins = $collectionTrips->unique('asset_id')->count();

        return view('collection-trips.index', compact(
            'collectionTrips',
            'dateFrom',
            'dateTo',
            'totalTrips',
            'uniqueBins'
        ));
    }

    /**
     * Get collection trips between dates (same logic as Last Emptied), grouped by asset.
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @return \Illuminate\Support\Collection
     */
    private function getCollectionTrips($dateFrom, $dateTo)
    {
        $devices = Device::with([
            'asset',
            'asset.capacitySetting',
            'asset.floor',
            'sensors' => fn($q) => $q->orderBy('created_at', 'desc')
        ])->whereHas('asset', fn($q) => $q->where('is_active', 1))
          ->where('is_active', 1)
          ->get();

        // Group by asset
        $assetsTrips = [];

        foreach ($devices as $device) {
            if (!$device->asset || !$device->asset->capacitySetting) {
                continue;
            }

            $assetId = $device->asset->id;
            $capacity = $device->asset->capacitySetting;
            $sensors = $device->sensors;

            if (!isset($assetsTrips[$assetId])) {
                $assetsTrips[$assetId] = [
                    'asset_id' => $assetId,
                    'asset_name' => $device->asset->asset_name,
                    'floor_name' => $device->asset->floor->floor_name ?? 'N/A',
                    'emptied_times' => [],
                ];
            }

            $wasFullOrHalf = false;
            $previousCapacity = null;
            $lastEmptiedTime = null;

            foreach ($sensors as $sensor) {
                if (!is_numeric($sensor->capacity)) {
                    continue;
                }

                $currentCapacity = $sensor->capacity;
                $sensorTime = Carbon::parse($sensor->created_at);

                // Check if bin was full or half (capacity > empty_to)
                if (!$wasFullOrHalf && $previousCapacity !== null && $previousCapacity > $capacity->empty_to) {
                    $wasFullOrHalf = true;
                }

                // Check if bin was emptied (capacity goes negative or <= empty_to after being full/half)
                if ($wasFullOrHalf && ($currentCapacity < 0 || $currentCapacity <= $capacity->empty_to)) {
                    $emptiedTime = Carbon::parse($sensor->created_at);

                    // Check if within date range
                    if ($emptiedTime->between(Carbon::parse($dateFrom)->startOfDay(), Carbon::parse($dateTo)->endOfDay())) {
                        // Only add if this is a new emptying event (not duplicate)
                        if (!$lastEmptiedTime || $emptiedTime < $lastEmptiedTime) {
                            $assetsTrips[$assetId]['emptied_times'][] = [
                                'emptied_at' => $emptiedTime,
                                'emptied_date' => $emptiedTime->format('Y-m-d'),
                                'emptied_time' => $emptiedTime->format('H:i'),
                                'datetime_formatted' => $emptiedTime->format('d/m/Y H:i'),
                                'diff_for_humans' => $emptiedTime->diffForHumans(),
                            ];
                            $lastEmptiedTime = $emptiedTime;
                        }
                    }

                    $wasFullOrHalf = false; // reset for next cycle
                }

                $previousCapacity = $currentCapacity;
            }
        }

        // Convert to collection and flatten for display
        $collectionTrips = collect();

        foreach ($assetsTrips as $assetId => $assetData) {
            if (empty($assetData['emptied_times'])) {
                continue;
            }

            // Sort emptied times descending
            $emptiedTimes = collect($assetData['emptied_times'])->sortByDesc('emptied_at');

            // Get the most recent emptied time for sorting
            $mostRecentEmpty = $emptiedTimes->first()['emptied_at'];

            $collectionTrips->push([
                'asset_id' => $assetId,
                'asset_name' => $assetData['asset_name'],
                'floor_name' => $assetData['floor_name'],
                'total_trips' => $emptiedTimes->count(),
                'last_emptied_at' => $mostRecentEmpty,
                'last_emptied_formatted' => $emptiedTimes->first()['datetime_formatted'],
                'last_emptied_diff' => $emptiedTimes->first()['diff_for_humans'],
                'emptied_times' => $emptiedTimes->values()->all(),
            ]);
        }

        // Sort by last emptied time descending (most recent first)
        return $collectionTrips->sortByDesc('last_emptied_at')->values();
    }

    /**
     * Export collection trips to CSV.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $collectionTrips = $this->getCollectionTrips($dateFrom, $dateTo);

        // Create CSV
        $filename = "collection_trips_{$dateFrom}_to_{$dateTo}.csv";
        $filepath = storage_path('app/temp/' . $filename);

        // Ensure directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($file, [
            'No.',
            'Asset Name',
            'Floor',
            'Device/Compartment',
            'Date Emptied',
            'Time Emptied',
            'Date Time'
        ]);

        // Data
        $no = 1;
        foreach ($collectionTrips as $trip) {
            fputcsv($file, [
                $no++,
                $trip['asset_name'],
                $trip['floor_name'],
                $trip['device_name'],
                $trip['emptied_date'],
                $trip['emptied_time'],
                $trip['datetime_formatted']
            ]);
        }

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}
