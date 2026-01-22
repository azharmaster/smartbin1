<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotification;
use App\Models\Asset;   // ✅ Added to fetch bins/assets
use App\Models\Device;  // ✅ Added to toggle device notifications
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WhatsAppNotificationController extends Controller
{
    /**
     * Show the single Full Bin Notification setting
     */
    public function index()
    {
        // Get the first (and only) Full Bin Notification
        $notification = WhatsAppNotification::first();

        // If none exists yet, create a default
        if (!$notification) {
            $notification = WhatsAppNotification::create([
                'title'      => 'Full Bin Alert',
                'message'    => '⚠️ Bin Full Alert! ⚠️', // fixed message
                'is_active'  => true,
                'start_date' => now()->toDateString(),
                'end_date'   => now()->addYear()->toDateString(),
                'start_time' => now()->format('H:i'),
                'end_time'   => now()->addHour()->format('H:i'),
            ]);
        }

        // Cast start_date and end_date to Carbon for Blade formatting
        if ($notification->start_date) {
            $notification->start_date = Carbon::parse($notification->start_date);
        }
        if ($notification->end_date) {
            $notification->end_date = Carbon::parse($notification->end_date);
        }

        // ✅ Fetch all bins (assets) and eager load devices
        $bins = Asset::withCount([
            'devices as off_devices_count' => function ($q) {
                $q->where('is_active', false);
            }
        ])->with('devices')->get();

        // ✅ Fetch supervisors for WhatsApp notification toggle
        $supervisors = User::where('role', 'supervisor')->get();

        return view('whatsapp.index', compact('notification', 'bins', 'supervisors'));
    }

    /**
     * Update the notification ON/OFF and start/end dates & time
     */
    public function update(Request $request, WhatsAppNotification $notification)
    {
        $request->validate([
            'is_active'  => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);

        $notification->update([
            'is_active'  => $request->has('is_active') ? true : false,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification settings updated.');
    }

    /**
     * Toggle the ON/OFF status of a Bin (Asset)
     */
    public function toggleBin(Asset $bin)
    {
        $bin->is_active = !$bin->is_active;
        $bin->save();

        return redirect()->route('whatsapp.index')
                         ->with('success', "Bin '{$bin->asset_name}' notification toggled.");
    }

    /**
     * Toggle the ON/OFF status of a Device
     */
    public function toggleDevice(Device $device)
    {
        $device->is_active = !$device->is_active;
        $device->save();

        return redirect()->route('whatsapp.index')
                         ->with('success', "Device '{$device->device_name}' notification toggled.");
    }

    /**
     * Toggle the ON/OFF status of a Supervisor (WhatsApp notification)
     */
    public function toggleSupervisor(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->route('whatsapp.index')
                         ->with('success', "Supervisor '{$user->name}' notification updated.");
    }
}
