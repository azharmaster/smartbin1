<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Floor;
use App\Models\Asset;
use App\Models\Todo;
use App\Models\User; // <-- Add this
use App\Models\Task; // <-- Added for assigned tasks
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Load devices with their latest sensor & asset-floor relationship
        $devices = Device::with('latestSensor', 'asset.floor')->get();

        // Total devices
        $totalDevices = $devices->count();

        // FULL > 85%
        $fullDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 85;
        });
        $fullDevices = $fullDevicesCollection->count();

        // HALF 40–85%
        $halfDevicesCollection = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 40 &&
                   $d->latestSensor->capacity <= 85;
        });
        $halfDevices = $halfDevicesCollection->count();

        // EMPTY <= 40%
        $emptyDevices = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity <= 40;
        })->count();

        // Undetected: no sensor or bad network
        $undetectedDevices = $devices->filter(function ($d) {
            if (!$d->latestSensor) return true;
            $network = $d->latestSensor->network;
            return is_null($network)
                || $network === ''
                || (string)$network === '0'
                || strtolower((string)$network) === 'unavailable';
        })->count();

        // Load floors for the map dropdown
        $floors = Floor::all();

        $assetsWithCoords = Asset::whereNotNull('x')
            ->whereNotNull('y')
            ->get();

        // Load To-Do items for the current user
        $todos = Todo::where('userID', Auth::id())
                     ->where('status', 'pending')
                     ->orderBy('id', 'desc')
                     ->get();

        // Load all users for the simple user list
        $users = User::all(); // <-- Added this line

        // Load assigned tasks (optional: latest 10 or all)
        $assignedTasks = Task::with('user', 'asset', 'floor')
                             ->orderBy('id', 'desc')
                             ->get(); // <-- Added this

        // Pass all data to the dashboard view
        return view('dashboard.index', compact(
            'totalDevices',
            'fullDevices',
            'fullDevicesCollection',
            'halfDevices',
            'halfDevicesCollection',
            'emptyDevices',
            'undetectedDevices',
            'todos',
            'floors',
            'assetsWithCoords',
            'devices',
            'users',          // <-- Added this here
            'assignedTasks'   // <-- Added this here
        ));
    }
}
