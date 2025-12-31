<?php
use Illuminate\Support\Facades\DB;

// Make sure you include the Laravel bootstrap file if using this standalone
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap Laravel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = "PDVc#7eH-4YXkXcR5Yvn";

$message = "🚀 *SPECIAL OFFER!* 🚀\n\n" .
           "Get 50% OFF on all products this weekend!\n\n" .
           "✨ Features:\n" .
           "• Premium Quality\n" .
           "• Fast Delivery\n" .
           "• 24/7 Support\n\n" .
           "🛒 Shop now: https://example.com\n" .
           "📞 Contact: +1234567890\n\n" .
           "#SpecialOffer #Discount #Sale";

// Fetch all supervisors (role = 4) with a phone number
$supervisors = DB::table('users')->where('role', 4)->whereNotNull('phone')->pluck('phone');

foreach ($supervisors as $target) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $target,
            'message' => $message,
            'countryCode' => '60',
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: $token"
        ),
    ));

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo "Error sending to $target: " . curl_error($curl) . "\n";
    } else {
        echo "Message sent successfully to $target\n";
        echo "Response: " . $response . "\n";
    }

    curl_close($curl);
}
