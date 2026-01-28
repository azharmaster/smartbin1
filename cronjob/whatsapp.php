<?php


function sendWhatsApp($phone, $message)
{
    $token = 'PDVc#7eH-4YXkXcR5Yvn'; // 🔐 your Fonnte token

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => [
            'target'      => $phone,
            'message'     => $message,
            'countryCode' => '60',
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: $token",
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        file_put_contents(
            __DIR__ . '/cron.log',
            date('Y-m-d H:i') . " WhatsApp ERROR: " . curl_error($curl) . "\n",
            FILE_APPEND
        );
        curl_close($curl);
        return false;
    }

    curl_close($curl);
    return true;
}