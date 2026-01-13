<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotification;
use App\Models\Asset;   // ✅ Added to fetch bins/assets
use App\Models\Device;  // ✅ Added to toggle device notifications
use App\Models\NotificationOff; // ✅ Added for notification off schedules
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
        $bins = Asset::with('devices')->get();

        // ✅ Fetch all devices separately for the "optional" select list
        $devices = Device::all();

        // ✅ Fetch all currently active notification off schedules
        $notificationOffs = NotificationOff::with(['asset', 'device'])
                                ->where('active', true)
                                ->get();

        // Pass all variables to the view
        return view('whatsapp.index', compact('notification', 'bins', 'devices', 'notificationOffs'));
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
     * Create a notification off schedule for bins/devices
     */
    public function notificationOff(Request $request)
    {
        $request->validate([
            'start_at'  => 'required|date',
            'end_at'    => 'required|date|after:start_at',
            'asset_ids' => 'required|array',
            'device_ids'=> 'nullable|array',
        ]);

        // Save for bins
        foreach ($request->asset_ids as $assetId) {
            NotificationOff::create([
                'asset_id'  => $assetId,
                'device_id' => null,
                'start_at'  => $request->start_at,
                'end_at'    => $request->end_at,
            ]);
        }

        // Save for devices (if any)
        if ($request->device_ids) {
            foreach ($request->device_ids as $deviceId) {
                NotificationOff::create([
                    'asset_id'  => null,
                    'device_id' => $deviceId,
                    'start_at'  => $request->start_at,
                    'end_at'    => $request->end_at,
                ]);
            }
        }

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification off schedule saved!');
    }

    /**
     * Optional: Deactivate expired notification off schedules
     * Can be called via cron or included in sending logic
     */
    public function deactivateExpiredSchedules()
    {
        NotificationOff::where('end_at', '<', Carbon::now())
                       ->update(['active' => false]);
    }
}
