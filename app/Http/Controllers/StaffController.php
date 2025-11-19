<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device; // adjust if needed
use App\Models\Asset;

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

        return view('dashboard.staffindex', [
            'totalDevices' => $totalDevices,
            'fullDevices' => $fullDevices,
            'halfDevices' => $halfDevices,
            'emptyDevices' => $emptyDevices,
            'undetectedDevices' => $undetectedDevices,
            'fullDevicesCollection' => $fullDevicesCollection,
        ]);
    }
}
