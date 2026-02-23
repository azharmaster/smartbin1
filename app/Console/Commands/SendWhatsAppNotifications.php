<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppNotification;
use App\Models\NotificationLog; // <-- log model
use Carbon\Carbon;

class SendWhatsAppNotifications extends Command
{
    protected $signature = 'send:whatsapp';
    protected $description = 'Send active WhatsApp notifications automatically';

    public function handle()
    {
        $now = Carbon::now();

        // Get all active notifications within optional schedule
        $notifications = WhatsAppNotification::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', $now);
            })
            ->get();

        foreach ($notifications as $notif) {
            $this->sendWhatsApp($notif, $now);
        }

        $this->info('WhatsApp notifications processed.');
    }

    protected function sendWhatsApp(WhatsAppNotification $notif, $now)
    {
        // 1️⃣ Prevent duplicate: check if already sent in last 10 minutes (adjust as needed)
        $alreadySent = NotificationLog::where('device_id', $notif->device_id)
            ->where('channel', 'whatsapp')
            ->where('message_preview', $notif->message) // optional: match message text
            ->where('sent_at', '>=', $now->copy()->subMinutes(10))
            ->exists();

        if ($alreadySent) {
            $this->info("Skipped duplicate for {$notif->target}");
            return;
        }

        // 2️⃣ Send WhatsApp
        $token = "PDVc#7eH-4YXkXcR5Yvn"; // Your token
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $notif->target,
                'message' => $notif->message,
                'countryCode' => '60',
            ],
            CURLOPT_HTTPHEADER => [
                "Authorization: $token"
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $this->error("Failed to send to {$notif->target}: " . curl_error($curl));
        } else {
            $this->info("Sent to {$notif->target}");

            // 3️⃣ Log notification
            NotificationLog::create([
                'device_id' => $notif->device_id ?? null,
                'channel' => 'whatsapp',
                'message_preview' => $notif->message,
                'message_full' => $notif->message,
                'sent_at' => $now,
            ]);

            // Update last_sent_at
            $notif->update(['last_sent_at' => $now]);
        }

        curl_close($curl);
    }
}