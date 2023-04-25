<?php
/**
 * Plugin Name: Twitch Login Subscriber
 * Description: Log in to WordPress using Twitch and display content based on the user's subscription status.
 * Version: 0.1 Alpha
 * Author: Luke Kirkwood
 * Author URI: https://striding.co
 */

require_once plugin_dir_path(__FILE__) . 'twitch-api-helper.php';
require_once plugin_dir_path(__FILE__) . 'twitch-settings.php';

function twitch_login_template_redirect() {
    global $wp_query;

    if (isset($wp_query->query_vars['twitch-login'])) {
        $options = get_option('twitch_options');
        $client_id = $options['twitch_client_id'];
        $client_secret = $options['twitch_client_secret'];
        $redirect_uri = $options['twitch_redirect_uri'];
        $streamer_id = 'your_streamer_id'; // Replace this with the target streamer's Twitch user ID

        if (isset($_GET['code']) && $_GET['code']) {
            $code = sanitize_text_field($_GET['code']);
            $access_token_data = twitch_get_access_token($code, $client_id, $client_secret, $redirect_uri);
            $access_token = $access_token_data['access_token'];
            $user_info = twitch_get_user_info($access_token, $client_id);

            $subscription_data = twitch_check_subscription($access_token, $user_info['id'], $streamer_id, $client_id);

            if ($subscription_data['is_subscribed']) {
                // Create a new WordPress user if the Twitch user doesn't exist in your WordPress site,
                // or update the existing user's subscription data
                // Log the user in

                update_user_meta($user_id, 'twitch_is_subscribed', true);
            } else {
                update_user_meta($user_id, 'twitch_is_subscribed', false);
            }
        }
    }
}
add_action('template_redirect', 'twitch_login_template_redirect');

function twitch_subscriber_content_shortcode($atts, $content = null) {
    $streamer_username = 'your_streamer_username'; // Replace this with the streamer's Twitch username

    if (!is_user_logged_in()) {
        return ''; // Return nothing if the user is not logged in
    }

    $user_id = get_current_user_id();
    $is_subscribed = get_user_meta($user_id, 'twitch_is_subscribed', true);

    if (!$is_subscribed) {
        // Return a message and a button linking to the streamer's profile if the user is not subscribed
        return '<p>This content is for subscribers only. To access this content, please subscribe to <a href="https://www.twitch.tv/' . $streamer_username . '" target="_blank">' . $streamer_username . '</a> on Twitch.</p>';
    }

    return do_shortcode($content);
}
add_shortcode('twitch_subscriber_content', 'twitch_subscriber_content_shortcode');


function twitch_add_page_template($templates) {
    $templates['twitch-login-page.php'] = 'Twitch Login Page';
    return $templates;
}
add_filter('theme_page_templates', 'twitch_add_page_template');

function twitch_load_page_template($template) {
    global $post;
    $template_name = get_post_meta($post->ID, '_wp_page_template', true);
    if ($template_name == 'twitch-login-page.php') {
        $template_path = plugin_dir_path(__FILE__) . $template_name;
        if (file_exists($template_path)) {
            return $template_path;
        }
    }
    return $template;
}
add_filter('template_include', 'twitch_load_page_template');

function twitch_create_login_page() {
    $page_title = 'Twitch Login';
    $page_slug = 'twitch-login';

    // Check if the page already exists
    $existing_page = get_page_by_path($page_slug, OBJECT, 'page');
    if (!$existing_page) {
        // Create a new page with the specified title and slug
        $page_id = wp_insert_post([
            'post_title' => $page_title,
            'post_name' => $page_slug,
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);

        // Set the page template to twitch-login-page.php
        update_post_meta($page_id, '_wp_page_template', 'twitch-login-page.php');
    }
}

register_activation_hook(__FILE__, 'twitch_create_login_page');
