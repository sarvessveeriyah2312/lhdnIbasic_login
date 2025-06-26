<?php

require_once 'TokenManager.php';


$config = json_decode(file_get_contents('config.json'), true);
$loginUrl = $config['login_url'] ?? null;
$clientId = $config['client_id'] ?? null;
$clientSecret = $config['client_secret'] ?? null;

if (!$loginUrl || !$clientId || !$clientSecret) {
    exit("Missing config values. Please check config.json\n");
}


$payload = http_build_query([
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'grant_type' => 'client_credentials',
    'scope' => 'InvoicingAPI' 
]);


$headers = [
    'Content-Type: application/x-www-form-urlencoded'
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Optional: Disable SSL verification (Not recommended for production)
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    exit(" cURL error: $error\n");
}

curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);

    if (!isset($data['access_token']) || !isset($data['expires_in'])) {
        exit(" Invalid token response structure:\n$response\n");
    }


    $data['expires_at'] = time() + $data['expires_in'];

    $tokenManager = new TokenManager();
    $tokenManager->saveToken($data);

    echo "Login successful. Access token saved to token_store.json\n";
} else {
    echo "Login failed. HTTP Code: $httpCode\n";
    echo "Response:\n$response\n";
}
