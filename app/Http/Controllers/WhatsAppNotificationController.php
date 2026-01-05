<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotification;
use Illuminate\Http\Request;

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
                'title' => 'Full Bin Alert',
                'message' => '⚠️ Bin Full Alert! ⚠️', // fixed message
                'is_active' => true,
                'start_time' => now(),
                'end_time' => now()->addYear(),
            ]);
        }

        return view('whatsapp.index', compact('notification'));
    }

    /**
     * Update the notification ON/OFF and start/end dates
     */
    public function update(Request $request, WhatsAppNotification $notification)
    {
        $request->validate([
            'is_active' => 'nullable|boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        $notification->update([
            'is_active' => $request->has('is_active') ? true : false,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification settings updated.');
    }
}
