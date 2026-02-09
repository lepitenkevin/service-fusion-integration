<?php 
add_action('wpcf7_mail_sent', 'bearfox_direct_service_fusion_sync');

function bearfox_direct_service_fusion_sync($contact_form) {
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) return;
    $data = $submission->get_posted_data();

    // 1. AUTHENTICATION - Get a fresh token automatically
    $auth_response = wp_remote_post('https://api.servicefusion.com/oauth/access_token', [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode([
            'grant_type'    => 'client_credentials',
            'client_id'     => 'client_id',
            'client_secret' => 'client_secret'
        ])
    ]);

    if (is_wp_error($auth_response)) return;
    $auth_data = json_decode(wp_remote_retrieve_body($auth_response));
    $access_token = $auth_data->access_token ?? '';

    if ($access_token) {
        // 2. DATA MAPPING - Building the nested structure you need
        $payload = [
            "customer_name" => ($data['text-939'] ?? '') . " " . ($data['text-631'] ?? ''),
            "public_notes"  => $data['textarea-849'] ?? '',
            "contacts" => [[
                "fname" => $data['text-939'] ?? '',
                "lname" => $data['text-631'] ?? '',
                "is_primary" => true,
                "phones" => [[
                    "phone" => $data['tel-503'] ?? '',
                    "type"  => "Mobile"
                ]],
                "emails" => [[
                    "email" => $data['email-331'] ?? '',
                    "class" => "Business"
                ]]
            ]],
            "locations" => [[
                "street_1"    => $data['text-564'] ?? '',
                "city"        => $data['text-524'] ?? '',
                "postal_code" => $data['text-719'] ?? '',
                "is_primary"  => true
            ]]
        ];

        // 3. SEND TO SERVICE FUSION
        wp_remote_post('https://api.servicefusion.com/v1/customers', [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json'
            ],
            'body' => json_encode($payload)
        ]);
    }
}
?>