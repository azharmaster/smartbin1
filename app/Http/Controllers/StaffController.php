<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device; // adjust if needed
use App\Models\Asset;
use App\Models\Task;   // <-- added for task charts
use Carbon\Carbon;

class StaffController extends Controller
{
    public function dashboard()
    {
        // Replace these with your actual queries
        $totalDevices = Device::count();
        $fullDevices = Device::whereHas('latestSensor', fn($q) => $q->where('capacity', 100))->count();
        $halfDevices = Device::whereHas('latestSensor', fn($q) => $q->whereBetween('capacity', [50, 99]))->count();
        $emptyDevices = Device::whereHas('latestSensor', fn($q) => $q->where('capacity', 0))->count();
        $undetectedDevices = Device::whereDoesntHave('latestSensor')->count();
        $fullDevicesCollection = Device::whereHas('latestSensor', fn($q) => $q->where('capacity', 100))->get();

        /* ---------------------------------------------------
         * BAR CHART DATA (added here, nothing modified)
         * --------------------------------------------------- */

        $months = [];
        $pendingPerMonth = [];
        $completedPerMonth = [];
        $rejectedPerMonth = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()->month($i)->format('F');
            $months[] = $monthName;

            $pendingPerMonth[] = Task::whereMonth('created_at', $i)
                ->where('status', 'pending')
                ->count();

            $completedPerMonth[] = Task::whereMonth('created_at', $i)
                ->where('status', 'completed')
                ->count();

            $rejectedPerMonth[] = Task::whereMonth('created_at', $i)
                ->where('status', 'rejected')
                ->count();
        }

        return view('dashboard.staffindex', [
            'totalDevices' => $totalDevices,
            'fullDevices' => $fullDevices,
            'halfDevices' => $halfDevices,
            'emptyDevices' => $emptyDevices,
            'undetectedDevices' => $undetectedDevices,
            'fullDevicesCollection' => $fullDevicesCollection,

            // ---- added chart variables ----
            'months' => $months,
            'pendingPerMonth' => $pendingPerMonth,
            'completedPerMonth' => $completedPerMonth,
            'rejectedPerMonth' => $rejectedPerMonth,
        ]);
    }
}
