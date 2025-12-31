<?php
$token = "PDVc#7eH-4YXkXcR5Yvn";
$target = "601136820907";

$message = "🚀 *SPECIAL OFFER!* 🚀\n\n" .
           "Get 50% OFF on all products this weekend!\n\n" .
           "✨ Features:\n" .
           "• Premium Quality\n" .
           "• Fast Delivery\n" .
           "• 24/7 Support\n\n" .
           "🛒 Shop now: https://example.com\n" .
           "📞 Contact: +1234567890\n\n" .
           "#SpecialOffer #Discount #Sale";

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
    echo "Error: " . curl_error($curl);
} else {
    echo "Promotion message sent successfully to $target\n";
    echo "Response: " . $response;
}

curl_close($curl);
?>