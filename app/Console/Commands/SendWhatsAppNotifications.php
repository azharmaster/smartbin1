<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppNotification;
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
            $this->sendWhatsApp($notif);
        }

        $this->info('WhatsApp notifications processed.');
    }

    protected function sendWhatsApp(WhatsAppNotification $notif)
    {
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
            // Update last_sent_at
            $notif->update(['last_sent_at' => now()]);
        }

        curl_close($curl);
    }
}
