<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Sensor;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpiBinController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '7'); // Default 7 days
        $startDate = Carbon::now()->subDays($period);

        // Get all assets with their KPI data
        $bins = Asset::with(['devices.sensors' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->get()
        ->map(function ($asset) {
            $sensors = $asset->devices->flatMap->sensors->sortBy('created_at');

            if ($sensors->isEmpty()) {
                return null;
            }

            // Count how many times bin reached full (>=85%)
            $fullCount = $sensors->filter(function ($s) {
                return $s->capacity >= 85;
            })->count();

            // Calculate average emptying duration (time from full to empty)
            $emptyingDurations = [];
            $fullTime = null;

            foreach ($sensors as $sensor) {
                if ($sensor->capacity >= 85 && $fullTime === null) {
                    $fullTime = $sensor->created_at;
                } elseif ($sensor->capacity < 20 && $fullTime !== null) {
                    $duration = $sensor->created_at->diffInHours($fullTime);
                    $emptyingDurations[] = $duration;
                    $fullTime = null;
                }
            }

            $avgEmptyingDuration = !empty($emptyingDurations)
                ? round(array_sum($emptyingDurations) / count($emptyingDurations), 2)
                : null;

            // Get latest capacity
            $latestSensor = $sensors->last();
            $currentCapacity = $latestSensor ? $latestSensor->capacity : 0;

            // Calculate average capacity
            $avgCapacity = $sensors->avg('capacity') ?? 0;

            return [
                'asset' => $asset,
                'full_count' => $fullCount,
                'avg_emptying_duration' => $avgEmptyingDuration,
                'current_capacity' => $currentCapacity,
                'avg_capacity' => round($avgCapacity, 2),
                'total_readings' => $sensors->count(),
            ];
        })
        ->filter() // Remove nulls
        ->sortByDesc('full_count'); // Sort by most frequent full bins

        // Get summary statistics
        $firstBin = $bins->first();
        $summary = [
            'total_bins' => $bins->count(),
            'bins_full_avg' => round($bins->avg('full_count') ?? 0, 2),
            'most_problematic_bin' => $firstBin ? $firstBin['asset']->asset_name : 'N/A',
            'avg_emptying_time' => round($bins->filter(fn($b) => $b['avg_emptying_duration'])->avg('avg_emptying_duration') ?? 0, 2),
        ];

        return view('kpi.bin.index', compact('bins', 'summary', 'period'));
    }

    public function export(Request $request)
    {
        $period = $request->input('period', '7');
        $startDate = Carbon::now()->subDays($period);

        $bins = Asset::with(['devices.sensors' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->get()
        ->map(function ($asset) {
            $sensors = $asset->devices->flatMap->sensors->sortBy('created_at');

            if ($sensors->isEmpty()) {
                return null;
            }

            $fullCount = $sensors->filter(function ($s) {
                return $s->capacity >= 85;
            })->count();

            $emptyingDurations = [];
            $fullTime = null;

            foreach ($sensors as $sensor) {
                if ($sensor->capacity >= 85 && $fullTime === null) {
                    $fullTime = $sensor->created_at;
                } elseif ($sensor->capacity < 20 && $fullTime !== null) {
                    $duration = $sensor->created_at->diffInHours($fullTime);
                    $emptyingDurations[] = $duration;
                    $fullTime = null;
                }
            }

            $avgEmptyingDuration = !empty($emptyingDurations)
                ? round(array_sum($emptyingDurations) / count($emptyingDurations), 2)
                : null;

            $latestSensor = $sensors->last();
            $currentCapacity = $latestSensor ? $latestSensor->capacity : 0;
            $avgCapacity = $sensors->avg('capacity') ?? 0;

            return [
                'asset_name' => $asset->asset_name,
                'location' => $asset->location,
                'full_count' => $fullCount,
                'avg_emptying_duration_hrs' => $avgEmptyingDuration ?? 'N/A',
                'current_capacity' => $currentCapacity,
                'avg_capacity' => round($avgCapacity, 2),
            ];
        })
        ->filter();

        // Generate CSV
        $csv = "Asset Name,Location,Full Count,Avg Emptying Duration (hrs),Current Capacity,Avg Capacity\n";
        foreach ($bins as $bin) {
            $csv .= sprintf(
                '"%s","%s",%s,%s,%s,%s' . "\n",
                $bin['asset_name'],
                $bin['location'],
                $bin['full_count'],
                $bin['avg_emptying_duration_hrs'],
                $bin['current_capacity'],
                $bin['avg_capacity']
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kpi_bin_export_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
