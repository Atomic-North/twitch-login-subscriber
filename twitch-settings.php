<?php // Register the settings page
function twitch_settings_page() {
    add_options_page('Twitch Settings', 'Twitch Settings', 'manage_options', 'twitch-settings', 'twitch_settings_page_content');
}
add_action('admin_menu', 'twitch_settings_page');

// Render the settings page content
function twitch_settings_page_content() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('twitch_messages', 'twitch_message', __('Settings saved', 'twitch'), 'updated');
    }
    settings_errors('twitch_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('twitch_settings');
            do_settings_sections('twitch_settings');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Register the settings
function twitch_settings_init() {
    register_setting('twitch_settings', 'twitch_options');

    add_settings_section('twitch_settings_section', __('Twitch Settings', 'twitch'), 'twitch_settings_section_callback', 'twitch_settings');

    add_settings_field('twitch_client_id', __('Twitch Client ID', 'twitch'), 'twitch_client_id_field_callback', 'twitch_settings', 'twitch_settings_section');
    add_settings_field('twitch_client_secret', __('Twitch Client Secret', 'twitch'), 'twitch_client_secret_field_callback', 'twitch_settings', 'twitch_settings_section');
    add_settings_field('twitch_redirect_uri', __('Twitch Redirect URI', 'twitch'), 'twitch_redirect_uri_field_callback', 'twitch_settings_section');

}
add_action('admin_init', 'twitch_settings_init');

// Render the settings section
function twitch_settings_section_callback() {
    echo '<p>' . __('Enter your Twitch application details:', 'twitch') . '</p>';
}

// Render the Client ID field
function twitch_client_id_field_callback() {
    $options = get_option('twitch_options');
    echo '<input type="text" id="twitch_client_id" name="twitch_options[twitch_client_id]" value="' . esc_attr($options['twitch_client_id']) . '">';
}

// Render the Client Secret field
function twitch_client_secret_field_callback() {
    $options = get_option('twitch_options');
    echo '<input type="text" id="twitch_client_secret" name="twitch_options[twitch_client_secret]" value="' . esc_attr($options['twitch_client_secret']) . '">';
}

// Render the Redirect URI field
function twitch_redirect_uri_field_callback() {
    $options = get_option('twitch_options');
    echo '<input type="text" id="twitch_redirect_uri" name="twitch_options[twitch_redirect_uri]" value="' . esc_attr($options['twitch_redirect_uri']) . '">';
}

function twitch_enqueue_admin_styles() {
    wp_enqueue_style('twitch-admin', plugin_dir_url(__FILE__) . 'assets/twitch-admin.css');
}
add_action('admin_enqueue_scripts', 'twitch_enqueue_admin_styles');
