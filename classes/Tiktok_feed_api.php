<?php

class Tiktok_feed_api {

public static function get_tiktok_athorization_code(){
        // This function should return the TikTok authorization code URL
        // Replace with actual logic to generate or retrieve the authorization code
        $client_key = 'sbawnc11g9f8qzrl7q';
        $redirect_uri = site_url('/tiktok-callback/');
        $state = wp_create_nonce('tiktok_oauth_state');
        
        return 'https://www.tiktok.com/v2/auth/authorize/?' .http_build_query([
            'client_key' => $client_key,
            'response_type' => 'code',
            'scope' => 'user.info.basic,video.list,user.info.profile',
            'redirect_uri' => $redirect_uri,
            'state' => $state,// 
        ]);
    }
public static function get_access_token($code) {
    $client_key = 'sbawnc11g9f8qzrl7q';
    $client_secret = 'HXhPve0gmb0Iz0McsUsCN92ODYGjskEI';
    $redirect_uri = site_url('/tiktok-callback/');

    // Exchange code for access token
    $response = wp_remote_post('https://open.tiktokapis.com/v2/oauth/token/', [
        'body' => [
            'client_key' => $client_key,
            'client_secret' => $client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri
        ]
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('tiktok_api_error', __('Failed to retrieve access token.', 'tiktok-feed'));
    }

    $body = wp_remote_retrieve_body($response);
    error_log('TikTok Token Response: ' . $body);

    $data = json_decode($body, true);

    if (empty($data['access_token'])) {
        return new WP_Error('tiktok_api_error', __('Error retrieving access token: ' . json_encode($data), 'tiktok-feed'));
    }

    // Save access token and open_id
    $access_token = sanitize_text_field($data['access_token']);
    $open_id = sanitize_text_field($data['open_id']);
    update_option('tiktok_access_token', $access_token);
    update_option('tiktok_open_id', $open_id);

    // Fetch user info
    $user_info_response = wp_remote_get('https://open.tiktokapis.com/v2/user/info/?fields=display_name,avatar_url', [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ]
    ]);

    if (!is_wp_error($user_info_response)) {
        $user_info_data = json_decode(wp_remote_retrieve_body($user_info_response), true);
        error_log('TikTok User Info: ' . print_r($user_info_data, true));

        if (!empty($user_info_data['data']['user'])) {
            update_option('tiktok_user_avatar', sanitize_text_field($user_info_data['data']['user']['avatar_url']));
            update_option('tiktok_user_name', sanitize_text_field($user_info_data['data']['user']['display_name']));
        }
    }

    return $access_token;
    }
public static function render_video() {
        $token = get_option('tiktok_access_token');
         if (empty($token)) {
        return '<p>Please connect your TikTok account first.</p>';
     }

        $open_id = get_option('tiktok_open_id'); // Make sure this is set after authentication

        $response = wp_remote_post('https://open.tiktokapis.com/v2/video/list/?fields=cover_image_url,id,title,embed_link', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json'
        ],
        ]);

         if (is_wp_error($response)) {
        return '<p>Error fetching TikTok feed.</p>';
        }

        $videos = json_decode(wp_remote_retrieve_body($response), true);
        $output = '<div class="tiktok-feed">';

        if (!empty($videos['data']['videos'])) {
        foreach ($videos['data']['videos'] as $video) {
            $embed_link = !empty($video['embed_link']) ? esc_url($video['embed_link']) : '';
            $cover = !empty($video['cover_image_url']) ? esc_url($video['cover_image_url']) : '';
            $title = !empty($video['title']) ? esc_html($video['title']) : '';

            if ($embed_link) {
                $output .= '<div class="tiktok-video" style="margin-bottom:20px;">';
                $output .= '<iframe src="' . $embed_link . '" width="325" height="580" frameborder="0" allowfullscreen></iframe>';
                if ($title) {
                    $output .= '<p>' . $title . '</p>';
                }
                $output .= '</div>';
            } elseif ($cover) {
                // Fallback if embed link is missing
                $output .= '<img src="' . $cover . '" alt="' . $title . '" width="325">';
            }
        }
             } else {
         echo var_dump(print_r($response));
        $output .= '<p>No videos found.</p>';
             }

         $output .= '</div>';
         return $output;
}
}

?>