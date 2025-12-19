<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device; // adjust if needed
use App\Models\Asset;
use App\Models\Task;   // <-- added for task charts
use Carbon\Carbon;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function dashboard()
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
        $halfDevices = $devices->filter(function ($d) {
            return $d->latestSensor &&
                   is_numeric($d->latestSensor->capacity) &&
                   $d->latestSensor->capacity > 40 &&
                   $d->latestSensor->capacity <= 85;
        })->count();

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

        $todos = Todo::where('userID', Auth::id())
                     ->where('status', 'pending')
                     ->orderBy('id', 'desc')
                     ->get();

        /* ---------------------------------------------------
         * 🆕 STAFF CALENDAR DATA (ADDED ONLY)
         * --------------------------------------------------- */
        $assignedTasks = Task::where('user_id', Auth::id())
                             ->orderBy('created_at', 'asc')
                             ->get();

        // Build calendar events
    $calendarEvents = collect();

    foreach ($assignedTasks as $task) {
        $calendarEvents->push([
            'id'    => $task->id,
            'title' => 'Task: ' . ($task->asset->asset_name ?? 'Asset'),
            'start' => $task->created_at->toDateString(),

            'color' => match ($task->status) {
                'completed'   => '#28a745',
                'in_progress' => '#17a2b8',
                'pending'     => '#ffc107',
                'reject'      => '#dc3545',
                default       => '#6c757d',
            },

            'extendedProps' => [
                'user'   => $task->user->name ?? 'N/A',
                'asset'  => $task->asset->asset_name ?? 'N/A',
                'floor'  => $task->floor->floor_name ?? 'N/A',
                'status' => $task->status,
                'notes'  => $task->notes ?? '-',
            ],
        ]);
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

            // ---- added calendar variable ----
            'assignedTasks' => $assignedTasks,
            'calendarEvents' => $calendarEvents,

            'todos' => $todos,
        ]);
    }
}
