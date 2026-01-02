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

        // Save notification to database
        WhatsAppNotification::create($request->only(
            'title', 'message', 'is_active', 'start_time', 'end_time'
        ));

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification created.');
    }

    /**
     * Show the form for editing a notification.
     */
    public function edit(WhatsAppNotification $whatsapp)
    {
        return view('whatsapp.edit', compact('whatsapp'));
    }

    /**
     * Update the specified notification in storage.
     */
    public function update(Request $request, WhatsAppNotification $whatsapp)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'is_active' => 'nullable|boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ]);

        // Update the notification record
        $whatsapp->update($request->only(
            'title', 'message', 'is_active', 'start_time', 'end_time'
        ));

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
     * Manually send a WhatsApp notification to all supervisors (role = 4).
     */
    public function sendNow(WhatsAppNotification $notification)
    {
        $this->sendWhatsApp($notification);

        return redirect()->route('whatsapp.index')
                         ->with('success', 'Notification sent successfully.');
    }

    /**
     * Protected helper to send WhatsApp messages using Fonnte API.
     *
     * @param WhatsAppNotification $notification
     */
    protected function sendWhatsApp(WhatsAppNotification $notification)
    {
        $token = "PDVc#7eH-4YXkXcR5Yvn";

        // Get all supervisors who have a phone number
        $supervisors = User::where('role', 4)
                           ->whereNotNull('phone')
                           ->pluck('phone');

        // Loop through each supervisor and send message
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

            // Log result for debugging
            if (curl_errno($curl)) {
                logger("Failed to send WhatsApp to {$phone}: " . curl_error($curl));
            } else {
                logger("WhatsApp sent to {$phone}: {$response}");
            }

            curl_close($curl);
        }

        // Update last_sent_at timestamp for record
        $notification->update(['last_sent_at' => now()]);
    }
}
