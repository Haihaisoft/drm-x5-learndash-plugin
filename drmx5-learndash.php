<?php
/**
 * Plugin Name: DRM-X 5.0 Integration for LearnDash
 * Plugin URI: https://www.drm-x.com/
 * Description: Integrates DRM-X 5.0 protected content and the ZJGet player with WordPress and LearnDash.
 * Version: 1.0.0
 * Author: Haihaisoft
 * Author URI: https://www.haihaisoft.com/
 * Text Domain: drmx5-learndash
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Requires Plugins: sfwd-lms
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('DRMX5_LD_VERSION', '1.0.0');
define('DRMX5_LD_FILE', __FILE__);
define('DRMX5_LD_DIR', plugin_dir_path(__FILE__));
define('DRMX5_LD_URL', plugin_dir_url(__FILE__));

require_once DRMX5_LD_DIR . 'includes/class-drmx5-ld-request.php';
require_once DRMX5_LD_DIR . 'includes/class-drmx5-ld-course-authorizer.php';
require_once DRMX5_LD_DIR . 'includes/class-drmx5-ld-client.php';
require_once DRMX5_LD_DIR . 'includes/class-drmx5-ld-license-controller.php';
require_once DRMX5_LD_DIR . 'includes/class-drmx5-ld-settings.php';

/** Load translations after WordPress has selected the current locale. */
function drmx5_ld_load_textdomain() {
    load_plugin_textdomain('drmx5-learndash', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'drmx5_ld_load_textdomain', 1);

/** Register the ZJGet player shortcode. */
function drmx5_ld_register_shortcode() {
    add_shortcode('zjget-player', 'drmx5_ld_player_shortcode');
}
add_action('init', 'drmx5_ld_register_shortcode');

/**
 * Render [zjget-player]https://example.com/video.mp4[/zjget-player].
 *
 * @param array|string $atts Shortcode attributes.
 * @param string|null  $content Shortcode content.
 * @return string
 */
function drmx5_ld_player_shortcode($atts = array(), $content = null) {
    unset($atts);
    $content = html_entity_decode((string) $content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (!preg_match('~https?://[^\s<>"\']+~iu', $content, $matches)) {
        return '<span class="drmx5-ld-error">' .
            esc_html__('The ZJGet player URL must start with http:// or https://.', 'drmx5-learndash') .
            '</span>';
    }

    $url = esc_url_raw($matches[0], array('http', 'https'));
    if (!$url) {
        return '<span class="drmx5-ld-error">' .
            esc_html__('The ZJGet player URL must start with http:// or https://.', 'drmx5-learndash') .
            '</span>';
    }

    $editing = is_admin() || is_customize_preview() ||
        (defined('REST_REQUEST') && REST_REQUEST) ||
        isset($_GET['elementor-preview']) || isset($_GET['fl_builder']);
    $editing = (bool) apply_filters('drmx5_ld_disable_player', $editing);

    if ($editing) {
        return '<div class="drmx5-ld-editing-placeholder" style="padding:12px;border:1px solid #ccd0d4;background:#f6f7f7">' .
            '<strong>' . esc_html__('ZJGet protected video (player disabled while editing)', 'drmx5-learndash') . '</strong>' .
            '<br><small>' . esc_html($url) . '</small></div>';
    }

    static $rendered = false;
    if ($rendered) {
        return '<span class="drmx5-ld-error">' .
            esc_html__('Only one ZJGet player can be displayed on the same page.', 'drmx5-learndash') .
            '</span>';
    }
    $rendered = true;

    wp_enqueue_script(
        'drmx5-ld-embed-zjget',
        'https://www.zjget.com/assets/embed_js/embed_zjget.js',
        array(),
        null,
        true
    );
    wp_enqueue_script(
        'drmx5-ld-videojs',
        'https://www.zjget.com/assets/videojs-8.23.3/video.min.js',
        array('drmx5-ld-embed-zjget'),
        null,
        true
    );
    wp_enqueue_script(
        'drmx5-ld-zjget',
        'https://www.zjget.com/assets/embed_js/zjget.js',
        array('drmx5-ld-videojs'),
        null,
        true
    );

    return '<div id="ZJGet_Video_URL" style="display:none">' . esc_html($url) . '</div>';
}

/** Show a dependency notice without breaking the WordPress admin area. */
function drmx5_ld_dependency_notice() {
    if (!current_user_can('activate_plugins') || function_exists('sfwd_lms_has_access')) {
        return;
    }
    echo '<div class="notice notice-error"><p>' .
        esc_html__('DRM-X 5.0 Integration requires LearnDash to be installed and activated.', 'drmx5-learndash') .
        '</p></div>';
}
add_action('admin_notices', 'drmx5_ld_dependency_notice');

DRMX5_LD_Settings::init();



