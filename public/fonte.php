<?php

use App\Models\WhatsAppNotification;
use App\Models\User;

// Include Laravel bootstrap if running standalone
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set your WhatsApp token
$token = "PDVc#7eH-4YXkXcR5Yvn";

// Get the notification ID (e.g., from query string or manually set)
$notificationId = $argv[1] ?? null;

if (!$notificationId) {
    die("Please provide a notification ID as argument.\n");
}

// Fetch the notification
$notification = WhatsAppNotification::find($notificationId);

if (!$notification) {
    die("Notification ID {$notificationId} not found.\n");
}

// Fetch all supervisors (role = 4)
$supervisors = User::where('role', 4)
                   ->whereNotNull('mobile')
                   ->pluck('mobile');

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
        echo "Failed to send WhatsApp to {$phone}: " . curl_error($curl) . "\n";
    } else {
        echo "WhatsApp sent to {$phone}: {$response}\n";
    }

    curl_close($curl);
}

// Update last_sent_at
$notification->update(['last_sent_at' => now()]);

echo "Notification ID {$notificationId} sent to all supervisors.\n";