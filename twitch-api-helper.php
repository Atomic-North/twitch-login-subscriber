   <?php

   function twitch_get_access_token($code, $client_id, $client_secret, $redirect_uri) {
    $url = 'https://id.twitch.tv/oauth2/token';
    $response = wp_remote_post($url, [
        'body' => [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri,
        ],
    ]);



    if (is_wp_error($response)) {
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data;
}

function twitch_get_user_info($access_token, $client_id) {
    $url = 'https://api.twitch.tv/helix/users';
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Client-ID' => $client_id,
        ],
    ]);

    if (is_wp_error($response)) {
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['data'][0]) ? $data['data'][0] : null;
}

function twitch_check_subscription($access_token, $user_id, $streamer_id, $client_id) {
    $url = 'https://api.twitch.tv/helix/subscriptions/user?' . http_build_query([
        'user_id' => $user_id,
        'broadcaster_id' => $streamer_id,
    ]);

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Client-ID' => $client_id,
        ],
    ]);

    if (is_wp_error($response)) {
        return [
            'is_subscribed' => false,
        ];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return [
        'is_subscribed' => isset($data['data'][0]),
    ];
}
