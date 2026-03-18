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
     * Get collection trips between dates.
     *
     * Clear Bin Logic:
     * - A compartment (device) is considered cleared when its capacity reads 0%
     *   AND the previous reading was > 10%.
     * - Each qualifying event is recorded as one collection trip entry.
     *
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @return \Illuminate\Support\Collection
     */
    private function getCollectionTrips($dateFrom, $dateTo)
    {
        $assets = Asset::with([
            'floor',
            'capacitySetting',
            'devices' => fn($q) => $q->where('is_active', 1),
            'devices.sensors' => fn($q) => $q->orderBy('created_at', 'desc'),
        ])->where('is_active', 1)->get();

        $collectionTrips = collect();

        $rangeStart = Carbon::parse($dateFrom)->startOfDay();
        $rangeEnd   = Carbon::parse($dateTo)->endOfDay();

        foreach ($assets as $asset) {
            if (!$asset->capacitySetting) {
                continue;
            }

            // Process each compartment (device) independently
            foreach ($asset->devices as $device) {
                $sensors = $device->sensors;
                $previousCapacity = null;

                foreach ($sensors as $sensor) {
                    if (!is_numeric($sensor->capacity)) {
                        continue;
                    }

                    $currentCapacity = (float) $sensor->capacity;

                    // NEW Clear Bin Logic:
                    // Compartment is cleared when current reading = 0%
                    // AND previous reading was > 10%
                    if (
                        $previousCapacity !== null &&
                        $previousCapacity > 10 &&
                        $currentCapacity <= 0
                    ) {
                        $emptiedTime = Carbon::parse($sensor->created_at);

                        // Only record if within the selected date range
                        if ($emptiedTime->between($rangeStart, $rangeEnd)) {
                            $collectionTrips->push([
                                'asset_id'           => $asset->id,
                                'asset_name'         => $asset->asset_name,
                                'floor_name'         => $asset->floor->floor_name ?? 'N/A',
                                'device_name'        => $device->device_name ?? 'N/A',
                                'emptied_at'         => $emptiedTime,
                                'emptied_date'       => $emptiedTime->format('Y-m-d'),
                                'emptied_time'       => $emptiedTime->format('H:i'),
                                'datetime_formatted' => $emptiedTime->format('d/m/Y h:i A'),
                                'diff_for_humans'    => $emptiedTime->diffForHumans(),
                            ]);
                        }
                    }

                    $previousCapacity = $currentCapacity;
                }
            }
        }

        // Sort by emptied time descending (most recent first)
        return $collectionTrips->sortByDesc('emptied_at')->values();
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

        // Headers - Asset Name and Date Time only
        fputcsv($file, [
            'Asset Name',
            'Date Time'
        ]);

        // Data
        foreach ($collectionTrips as $trip) {
            fputcsv($file, [
                $trip['asset_name'],
                $trip['datetime_formatted']
            ]);
        }

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}
