<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\CollectionTripService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CollectionTripController extends Controller
{
    public function __construct(private CollectionTripService $collectionTripService)
    {
    }

    /**
     * Display collection trips with date and asset filters.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $assetId = $request->integer('asset_id') ?: null;

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $collectionTrips = $this->collectionTripService->getTrips($dateFrom, $dateTo, $assetId);
        $totalTrips = $collectionTrips->count();
        $uniqueBins = $collectionTrips->unique('asset_id')->count();
        $assets = Asset::where('is_active', 1)->orderBy('asset_name')->get(['id', 'asset_name']);

        return view('collection-trips.index', compact(
            'collectionTrips',
            'dateFrom',
            'dateTo',
            'totalTrips',
            'uniqueBins',
            'assets',
            'assetId'
        ));
    }

    /**
     * Export collection trips to CSV.
     */
    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $assetId = $request->integer('asset_id') ?: null;

        $collectionTrips = $this->collectionTripService->getTrips($dateFrom, $dateTo, $assetId);

        $filename = "collection_trips_{$dateFrom}_to_{$dateTo}.csv";
        $filepath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($filepath, 'w');

        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, [
            'Asset Name',
            'Date Time',
        ]);

        foreach ($collectionTrips as $trip) {
            fputcsv($file, [
                $trip['asset_name'],
                $trip['datetime_formatted'],
            ]);
        }

        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}
