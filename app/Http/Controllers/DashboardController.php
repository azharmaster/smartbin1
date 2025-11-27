<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Floor;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{ 
    public function index()
    {
        $devices = Device::with('latestSensor', 'asset.floor')->get(); // eager-load asset and floor

        // total devices
        $totalDevices = $devices->count();

        // full: capacity > 85
        $fullDevicesCollection = $devices->filter(function($d) {
            return $d->latestSensor && is_numeric($d->latestSensor->capacity) && $d->latestSensor->capacity > 85;
        });

        $fullDevices = $fullDevicesCollection->count();

        // half-full: capacity > 40 and <= 85
        $halfDevices = $devices->filter(function($d) {
            return $d->latestSensor && is_numeric($d->latestSensor->capacity)
                && $d->latestSensor->capacity > 40 && $d->latestSensor->capacity <= 85;
        })->count();

        // empty: capacity <= 40
        $emptyDevices = $devices->filter(function($d) {
            return $d->latestSensor && is_numeric($d->latestSensor->capacity) && $d->latestSensor->capacity <= 40;
        })->count();

        // undetected: no latest sensor OR network indicates 0/unavailable
        $undetectedDevices = $devices->filter(function($d) {
            if (!$d->latestSensor) return true;
            $network = $d->latestSensor->network;
            return is_null($network) || $network === '' || (string)$network === '0' || strtolower((string)$network) === 'unavailable';
        })->count();

        // **Fetch all floors for the map display**
        $floors = Floor::all();

        // Fetch To Do items for logged-in user, only pending ones
    $todos = Todo::where('userID', Auth::id())
                 ->where('status', 'pending')   // only show incomplete
                 ->orderBy('id', 'desc')
                 ->get();

        return view('dashboard.index', compact(
            'totalDevices',
            'fullDevices',
            'fullDevicesCollection',
            'halfDevices',
            'emptyDevices',
            'undetectedDevices',
            'todos',
            'floors' // <- added this
        ));
    }
}
