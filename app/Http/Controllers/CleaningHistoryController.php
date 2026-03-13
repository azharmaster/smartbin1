<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Asset;

class CleaningHistoryController extends Controller
{
    private function _getCleaningLogs(Carbon $startDate, Carbon $endDate)
    {
        $assets = Asset::with([
            'capacitySetting',
            'devices'
        ])->get();

        $sensorData = DB::table('sensors')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get()
            ->groupBy('device_id');

        $logs = [];

        foreach ($assets as $asset) {
            $setting = $asset->capacitySetting;
            if (!$setting) continue;

            foreach ($asset->devices as $device) {
                $deviceSensors = $sensorData->get($device->id_device, collect());

                $prevCapacity = null;

                foreach ($deviceSensors as $sensor) {
                    if (!is_numeric($sensor->capacity)) continue;

                    $capacity = (float) $sensor->capacity;
                    $time = Carbon::parse($sensor->created_at);

                    if ($prevCapacity === null) {
                        $prevCapacity = $capacity;
                        continue;
                    }

                    // CLEANED EVENT: FULL/HALF → EMPTY (including negative capacity)
                    if (
                        $prevCapacity > $setting->empty_to &&
                        ($capacity <= $setting->empty_to || $capacity < 0)
                    ) {
                        $logs[] = (object) [
                            'asset_name'  => $asset->asset_name,
                            'device_name' => $device->device_name ?? $device->id_device,
                            'cleaned_at'  => $time,
                        ];
                    }

                    $prevCapacity = $capacity;
                }
            }
        }

        // Consolidate logs with same asset_name within 10 minutes
        $logs = collect($logs)->sortBy('cleaned_at');
        
        $consolidated = [];
        $lastLogTimeByAsset = [];
        
        foreach ($logs as $log) {
            $key = $log->asset_name;
            
            if (!isset($lastLogTimeByAsset[$key])) {
                $consolidated[] = $log;
                $lastLogTimeByAsset[$key] = $log->cleaned_at;
            } else {
                $timeDiff = $lastLogTimeByAsset[$key]->diffInMinutes($log->cleaned_at);
                
                if ($timeDiff > 10) {
                    $consolidated[] = $log;
                    $lastLogTimeByAsset[$key] = $log->cleaned_at;
                }
            }
        }

        return collect($consolidated)->sortByDesc('cleaned_at');
    }

    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->endOfDay();

        $cleaningLogs = $this->_getCleaningLogs($startDate, $endDate);

        // Paginate the results (15 per page)
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $paginatedLogs = $cleaningLogs->forPage($currentPage, $perPage);
        
        // Create manual paginator
        $cleaningLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            $cleaningLogs->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Group by date for display
        $groupedLogs = $cleaningLogs->groupBy(function($log) {
            return $log->cleaned_at->format('Y-m-d');
        });

        return view('cleaning-history.index', compact(
            'dateFrom',
            'dateTo',
            'cleaningLogs',
            'groupedLogs'
        ));
    }

    public function exportCsv(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->endOfDay();

        $cleaningLogs = $this->_getCleaningLogs($startDate, $endDate);

        // CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cleaning_history_' . $dateFrom . '_to_' . $dateTo . '.csv"',
        ];

        // Create CSV callback
        $callback = function() use ($cleaningLogs) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['Date', 'Time', 'Asset Name', 'Status']);
            
            // Data rows
            foreach ($cleaningLogs as $log) {
                fputcsv($file, [
                    $log->cleaned_at->format('Y-m-d'),
                    $log->cleaned_at->format('H:i:s'),
                    $log->asset_name,
                    'Cleaned'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
