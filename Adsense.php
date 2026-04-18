<?php
/**
 * Plugin Name: Simple Google AdSense inject
 * Plugin URI: no
 * Description: A simple plugin to add Google AdSense everywhere except on specific posts as configured from the WordPress admin panel.
 * Version: 1.11
 * Author: Viktor Dite + Claude
 * Author URI: https://mizine.de
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: mizine-adsense
 */

if (!defined('ABSPATH')) {
    exit;
}

function mizine_adsense_get_excluded_ids() {
    $option_value = get_option('adsense_post_ids', '');
    $ids = array_map('intval', array_map('trim', explode(',', $option_value)));
    return array_filter($ids);
}

function mizine_adsense_enqueue() {
    if (is_feed() || is_preview()) {
        return;
    }

    $adsense_client_id = get_option('adsense_client_id', '');
    if (empty($adsense_client_id)) {
        return;
    }

    if (is_singular() && in_array((int) get_the_ID(), mizine_adsense_get_excluded_ids(), true)) {
        return;
    }

    $adsense_script_url = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode($adsense_client_id);

    wp_enqueue_script('google-adsense', $adsense_script_url, array(), null, array(
        'strategy'  => 'async',
        'in_footer' => true,
    ));
    wp_script_add_data('google-adsense', 'crossorigin', 'anonymous');
}
add_action('wp_enqueue_scripts', 'mizine_adsense_enqueue');

// Fallback for WP < 6.3: inject async attribute manually.
add_filter('script_loader_tag', function($tag, $handle) {
    if ($handle !== 'google-adsense') {
        return $tag;
    }
    if (strpos($tag, ' async') === false) {
        $tag = str_replace('<script ', '<script async ', $tag);
    }
    return $tag;
}, 10, 2);

function mizine_adsense_sanitize_post_ids($value) {
    $ids = array_map('intval', array_map('trim', explode(',', (string) $value)));
    $ids = array_filter($ids, function($id) { return $id > 0; });
    return implode(',', array_unique($ids));
}

function mizine_adsense_sanitize_client_id($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    return preg_match('/^ca-pub-\d{10,20}$/', $value) ? $value : '';
}

add_action('admin_menu', function() {
    add_options_page('AdSense Excluded Posts', 'AdSense Excluded Posts', 'manage_options', 'adsense-specific-posts', 'adsense_specific_posts_options_page');
});

add_action('admin_init', function() {
    register_setting('adsense-specific-posts-options', 'adsense_post_ids', array(
        'type'              => 'string',
        'sanitize_callback' => 'mizine_adsense_sanitize_post_ids',
        'default'           => '',
    ));
    register_setting('adsense-specific-posts-options', 'adsense_client_id', array(
        'type'              => 'string',
        'sanitize_callback' => 'mizine_adsense_sanitize_client_id',
        'default'           => '',
    ));
});

function adsense_specific_posts_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Google AdSense — Excluded Posts</h1>
        <form method="post" action="options.php">
            <?php settings_fields('adsense-specific-posts-options'); ?>
            <?php do_settings_sections('adsense-specific-posts-options'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="adsense_post_ids">Excluded Post IDs</label></th>
                    <td><input type="text" id="adsense_post_ids" name="adsense_post_ids" class="regular-text" value="<?php echo esc_attr(get_option('adsense_post_ids')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="adsense_client_id">AdSense Client ID</label></th>
                    <td><input type="text" id="adsense_client_id" name="adsense_client_id" class="regular-text" value="<?php echo esc_attr(get_option('adsense_client_id')); ?>" placeholder="ca-pub-xxxxxxxxxxxxxxxx" /></td>
                </tr>
            </table>
            <p><i>Enter the post IDs to EXCLUDE from AdSense, separated by commas, e.g., 7658,971590. AdSense will be loaded on all other pages. Client ID must match the format <code>ca-pub-xxxxxxxxxxxxxxxx</code>.</i></p>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
