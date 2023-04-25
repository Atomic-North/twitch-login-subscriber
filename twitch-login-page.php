<?php
/**
 * Template Name: Twitch Login Page
 */
get_header();
?>

<div class="twitch-login-container">
    <?php
    if (is_user_logged_in()) {
        echo do_shortcode('[twitch_subscriber_content]');
    } else {
        $options = get_option('twitch_options');
        $client_id = $options['twitch_client_id'];
        $redirect_uri = $options['twitch_redirect_uri'];
        $auth_url = 'https://id.twitch.tv/oauth2/authorize?' . http_build_query([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'user:read:subscriptions',
        ]);
        echo '<p>Please log in using your Twitch account:</p>';
        echo '<a href="' . esc_url($auth_url) . '"><img src="' . plugins_url('assets/twitch-login-button.png', __FILE__) . '" alt="Twitch Login Button"></a>';
    }
    ?>
</div>

<?php
get_footer();
