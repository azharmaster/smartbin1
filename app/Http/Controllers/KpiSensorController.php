<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Sensor;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpiSensorController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '7'); // Default 7 days
        $startDate = Carbon::now()->subDays($period);

        // Get all sensors with their KPI data
        $sensors = Sensor::with(['device.asset'])
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy('device_id');

        $sensorKpiData = [];

        foreach ($sensors as $deviceId => $deviceSensors) {
            $deviceSensors = $deviceSensors->sortBy('created_at');
            $firstSensor = $deviceSensors->first();

            if (!$firstSensor) {
                continue;
            }

            // Check for weak network signals
            $weakNetworkCount = $deviceSensors->filter(function ($s) {
                return in_array($s->network_strength, ['Week', 'Very Week']);
            })->count();

            // Check for abnormal data (negative values)
            $abnormalData = $deviceSensors->filter(function ($s) {
                return $s->capacity < 0 ||
                       $s->battery < 0 ||
                       ($s->rsrp !== null && (float)$s->rsrp > 0) || // RSRP should be negative
                       ($s->nsr !== null && (float)$s->nsr < 0);
            });

            // Check data frequency (expecting data every 30 minutes)
            $expectedReadings = 48; // 24 hours * 2 readings per hour
            $actualReadings = $deviceSensors->count();
            $dataFrequency = $actualReadings > 0 ? round(($actualReadings / $expectedReadings) * 100, 2) : 0;

            // Calculate average RSRP and NSR
            $avgRsrp = $deviceSensors->filter(fn($s) => $s->rsrp !== null)->avg('rsrp') ?? 0;
            $avgNsr = $deviceSensors->filter(fn($s) => $s->nsr !== null)->avg('nsr') ?? 0;

            // Get latest sensor reading
            $latestSensor = $deviceSensors->last();

            $sensorKpiData[] = [
                'device_id' => $deviceId,
                'device_name' => $firstSensor->device->device_name ?? 'Unknown',
                'asset_name' => $firstSensor->device->asset->asset_name ?? 'Unknown Bin',
                'weak_network_count' => $weakNetworkCount,
                'weak_network_percentage' => $actualReadings > 0
                    ? round(($weakNetworkCount / $actualReadings) * 100, 2)
                    : 0,
                'abnormal_data_count' => $abnormalData->count(),
                'abnormal_data_details' => $abnormalData->take(5), // Show first 5
                'expected_readings' => $expectedReadings * $period,
                'actual_readings' => $actualReadings,
                'data_frequency_percentage' => min($dataFrequency, 100),
                'avg_rsrp' => round($avgRsrp, 2),
                'avg_nsr' => round($avgNsr, 2),
                'latest_capacity' => $latestSensor ? $latestSensor->capacity : 0,
                'latest_battery' => $latestSensor ? $latestSensor->battery : 0,
                'latest_reading_time' => $latestSensor ? $latestSensor->created_at : null,
            ];
        }

        // Sort by weak network percentage (highest first)
        usort($sensorKpiData, function ($a, $b) {
            return $b['weak_network_percentage'] <=> $a['weak_network_percentage'];
        });

        // Summary statistics
        $summary = [
            'total_devices' => count($sensorKpiData),
            'devices_with_weak_network' => collect($sensorKpiData)->filter(fn($d) => $d['weak_network_count'] > 0)->count(),
            'devices_with_abnormal_data' => collect($sensorKpiData)->filter(fn($d) => $d['abnormal_data_count'] > 0)->count(),
            'avg_data_frequency' => round(collect($sensorKpiData)->avg('data_frequency_percentage') ?? 0, 2),
            'avg_rsrp' => round(collect($sensorKpiData)->avg('avg_rsrp') ?? 0, 2),
            'avg_nsr' => round(collect($sensorKpiData)->avg('avg_nsr') ?? 0, 2),
        ];

        return view('kpi.sensor.index', compact('sensorKpiData', 'summary', 'period'));
    }

    public function details(Request $request, $deviceId)
    {
        $period = $request->input('period', '7');
        $startDate = Carbon::now()->subDays($period);

        $sensors = Sensor::with(['device.asset'])
            ->where('device_id', $deviceId)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'asc')
            ->get();

        // Prepare chart data
        $chartData = $sensors->map(function($s) {
            return [
                'time' => $s->created_at->format('d M H:i'),
                'battery' => $s->battery,
                'capacity' => $s->capacity,
                'rsrp' => $s->rsrp,
                'nsr' => $s->nsr
            ];
        });

        return view('kpi.sensor.details', compact('sensors', 'deviceId', 'period', 'chartData'));
    }

    public function export(Request $request)
    {
        $period = $request->input('period', '7');
        $startDate = Carbon::now()->subDays($period);

        $sensors = Sensor::with(['device.asset'])
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy('device_id');

        $csv = "Device ID,Device Name,Asset Name,Weak Network Count,Weak Network %,Abnormal Data Count,Expected Readings,Actual Readings,Data Frequency %,Avg RSRP,Avg NSR,Latest Capacity,Latest Battery\n";

        foreach ($sensors as $deviceId => $deviceSensors) {
            $deviceSensors = $deviceSensors->sortBy('created_at');
            $firstSensor = $deviceSensors->first();

            if (!$firstSensor) {
                continue;
            }

            $weakNetworkCount = $deviceSensors->filter(function ($s) {
                return in_array($s->network_strength, ['Week', 'Very Week']);
            })->count();

            $abnormalData = $deviceSensors->filter(function ($s) {
                return $s->capacity < 0 || $s->battery < 0;
            });

            $expectedReadings = 48 * $period;
            $actualReadings = $deviceSensors->count();
            $dataFrequency = min(round(($actualReadings / $expectedReadings) * 100, 2), 100);

            $avgRsrp = round($deviceSensors->filter(fn($s) => $s->rsrp !== null)->avg('rsrp') ?? 0, 2);
            $avgNsr = round($deviceSensors->filter(fn($s) => $s->nsr !== null)->avg('nsr') ?? 0, 2);

            $latestSensor = $deviceSensors->last();

            $csv .= sprintf(
                '"%s","%s","%s",%s,%s,%s,%s,%s,%s,%s,%s,%s,%s' . "\n",
                $deviceId,
                $firstSensor->device->device_name ?? 'Unknown',
                $firstSensor->device->asset->asset_name ?? 'Unknown Bin',
                $weakNetworkCount,
                $actualReadings > 0 ? round(($weakNetworkCount / $actualReadings) * 100, 2) : 0,
                $abnormalData->count(),
                $expectedReadings,
                $actualReadings,
                $dataFrequency,
                $avgRsrp,
                $avgNsr,
                $latestSensor ? $latestSensor->capacity : 0,
                $latestSensor ? $latestSensor->battery : 0
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kpi_sensor_export_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
