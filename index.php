<?php
// 1. YOUR CREDENTIALS
$client_id     = 'client_id';
$client_secret = 'client_secret';

// 2. DATA (Cleaned up)
$fname = "Bearfox";
$lname = "Test";
$customer_name = trim($fname . " " . $lname); // Ensure no leading/trailing spaces

// 3. GET TOKEN (Simplified for test)
$auth_payload = json_encode([
    'grant_type'    => 'client_credentials',
    'client_id'     => $client_id,
    'client_secret' => $client_secret
]);

$ch = curl_init('https://api.servicefusion.com/oauth/access_token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $auth_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$token_res = json_decode(curl_exec($ch));
$token = $token_res->access_token;

if (!$token) { die("Auth failed."); }

// 4. THE PAYLOAD (Double-check every key)
$payload = [
    "customer_name" => $customer_name,
    "is_vip"        => false,
    "contacts" => [[
        "fname" => $fname,
        "lname" => $lname,
        "is_primary" => true,
        "phones" => [[
            "phone" => "9155181798",
            "type"  => "Mobile"
        ]],
        "emails" => [[
            "email" => "test@bearfoxdev.com",
            "class" => "Business" // Must be 'Business', 'Personal', or 'Other'
        ]]
    ]]
];

$json_payload = json_encode($payload);

// 5. THE REQUEST
$ch = curl_init('https://api.servicefusion.com/v1/customers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload); // Send the encoded string
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_payload) // Added this to help the server
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Status: $http_code \n";
echo "Response: $response \n";
curl_close($ch);