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
     * Clear Bin Logic (per asset/bin):
     * 1. Mana-mana compartment hit 0% dari sebelumnya >10% → 1 Collection Trip, binCleared = true
     * 2. Semua compartment lain dalam bin yang sama — skip
     * 3. Reset (binCleared = false) hanya bila compartment yang triggered naik balik >10%
     * 4. Barulah boleh detect collection trip baru
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
            'devices.sensors' => fn($q) => $q->orderBy('created_at', 'asc'),
        ])->where('is_active', 1)->get();

        $collectionTrips = collect();

        $rangeStart = Carbon::parse($dateFrom)->startOfDay();
        $rangeEnd   = Carbon::parse($dateTo)->endOfDay();

        foreach ($assets as $asset) {
            if (!$asset->capacitySetting) {
                continue;
            }

            // Gabungkan semua sensor readings dari semua compartments, sort by time ASC
            $allSensorReadings = collect();
            foreach ($asset->devices as $device) {
                foreach ($device->sensors as $sensor) {
                    if (!is_numeric($sensor->capacity)) continue;
                    $allSensorReadings->push([
                        'device_id'   => $device->id,
                        'device_name' => $device->device_name ?? 'N/A',
                        'capacity'    => (float) $sensor->capacity,
                        'created_at'  => Carbon::parse($sensor->created_at),
                    ]);
                }
            }

            // Sort semua readings ikut masa ASC
            $allSensorReadings = $allSensorReadings->sortBy('created_at')->values();

            // Track previous capacity per device
            $previousCapacities = []; // [device_id => capacity]

            // Track bin state
            $binCleared        = false;
            $triggeredDeviceId = null; // compartment yang triggered clear event

            foreach ($allSensorReadings as $reading) {
                $deviceId    = $reading['device_id'];
                $currentCap  = $reading['capacity'];
                $previousCap = $previousCapacities[$deviceId] ?? null;
                $readingTime = $reading['created_at'];

                if (!$binCleared) {
                    // Bin belum clear — check ada compartment yang hit 0% dari >10%
                    if (
                        $previousCap !== null &&
                        $previousCap > 10 &&
                        $currentCap <= 0
                    ) {
                        // Clear event detected!
                        $binCleared        = true;
                        $triggeredDeviceId = $deviceId;

                        if ($readingTime->between($rangeStart, $rangeEnd)) {
                            $collectionTrips->push([
                                'asset_id'           => $asset->id,
                                'asset_name'         => $asset->asset_name,
                                'floor_name'         => $asset->floor->floor_name ?? 'N/A',
                                'device_name'        => $reading['device_name'],
                                'emptied_at'         => $readingTime,
                                'emptied_date'       => $readingTime->format('Y-m-d'),
                                'emptied_time'       => $readingTime->format('H:i'),
                                'datetime_formatted' => $readingTime->format('d/m/Y h:i A'),
                                'diff_for_humans'    => $readingTime->diffForHumans(),
                            ]);
                        }
                    }
                } else {
                    // Bin dah clear — tunggu triggered compartment naik balik >10%
                    if ($deviceId === $triggeredDeviceId && $currentCap > 10) {
                        // Triggered compartment dah naik balik — reset!
                        $binCleared        = false;
                        $triggeredDeviceId = null;
                    }
                    // Compartment lain — skip, just update previous capacity
                }

                $previousCapacities[$deviceId] = $currentCap;
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
