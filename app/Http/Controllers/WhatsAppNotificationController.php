<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotification;
use App\Models\User;
use Illuminate\Http\Request;

class WhatsAppNotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = WhatsAppNotification::all();
        return view('whatsapp.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        return view('whatsapp.create');
    }

    /**
     * Store a newly created notification in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'is_active' => 'nullable|boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ]);

        WhatsAppNotification::create($request->only('title', 'message', 'is_active', 'start_time', 'end_time'));

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification created.');
    }

    /**
     * Show the form for editing a notification.
     */
    public function edit(WhatsAppNotification $notification)
    {
        return view('whatsapp.edit', compact('notification'));
    }

    /**
     * Update the specified notification in storage.
     */
    public function update(Request $request, WhatsAppNotification $notification)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'is_active' => 'nullable|boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ]);

        $notification->update($request->only('title', 'message', 'is_active', 'start_time', 'end_time'));

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification updated.');
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(WhatsAppNotification $notification)
    {
        $notification->delete();

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification deleted.');
    }

    /**
     * Manually send a WhatsApp notification to all supervisors.
     */
    public function sendNow(WhatsAppNotification $notification)
    {
        $this->sendWhatsApp($notification);

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification sent successfully.');
    }

    /**
     * Send WhatsApp to all supervisors (role = 4) using Fonnte API.
     */
    protected function sendWhatsApp(WhatsAppNotification $notification)
    {
        $token = "PDVc#7eH-4YXkXcR5Yvn";

        $supervisors = User::where('role', 4)
                           ->whereNotNull('phone') // ensure phone exists
                           ->pluck('phone');

        foreach ($supervisors as $phone) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $phone,
                    'message' => $notification->message,
                    'countryCode' => '60',
                ],
                CURLOPT_HTTPHEADER => [
                    "Authorization: $token"
                ],
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                logger("Failed to send WhatsApp to {$phone}: " . curl_error($curl));
            } else {
                logger("WhatsApp sent to {$phone}: {$response}");
            }

            curl_close($curl);
        }

        // Update last_sent_at timestamp
        $notification->update(['last_sent_at' => now()]);
    }
}
