<?php

defined('ABSPATH') || exit;

/** WordPress settings page for the DRM-X 5.0 LearnDash integration. */
class DRMX5_LD_Settings {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_page'));
        add_action('admin_init', array(__CLASS__, 'register'));
        add_filter('plugin_action_links_' . plugin_basename(DRMX5_LD_FILE), array(__CLASS__, 'action_links'));
    }

    public static function defaults() {
        return array(
            'admin_email' => '',
            'webservice_auth' => '',
            'group_id' => '',
            'service_area' => 'international',
            'rights_mode' => 'post',
            'fixed_rights_id' => '',
        );
    }

    public static function add_page() {
        add_options_page(
            __('DRM-X 5.0 Integration', 'drmx5-learndash'),
            __('DRM-X 5.0', 'drmx5-learndash'),
            'manage_options',
            'drmx5-learndash',
            array(__CLASS__, 'render')
        );
    }

    public static function register() {
        register_setting('drmx5_ld_group', 'drmx5_ld_settings', array(
            'type' => 'array',
            'sanitize_callback' => array(__CLASS__, 'sanitize'),
            'default' => self::defaults(),
        ));
    }

    public static function sanitize($input) {
        $input = is_array($input) ? $input : array();
        return array(
            'admin_email' => sanitize_email($input['admin_email'] ?? ''),
            'webservice_auth' => sanitize_text_field($input['webservice_auth'] ?? ''),
            'group_id' => preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($input['group_id'] ?? '')),
            'service_area' => ($input['service_area'] ?? '') === 'china' ? 'china' : 'international',
            'rights_mode' => ($input['rights_mode'] ?? '') === 'fixed' ? 'fixed' : 'post',
            'fixed_rights_id' => preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($input['fixed_rights_id'] ?? '')),
        );
    }

    public static function action_links($links) {
        array_unshift($links, '<a href="' . esc_url(admin_url('options-general.php?page=drmx5-learndash')) . '">' .
            esc_html__('Settings', 'drmx5-learndash') . '</a>');
        return $links;
    }

    public static function render() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $options = wp_parse_args(get_option('drmx5_ld_settings', array()), self::defaults());
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('DRM-X 5.0 Integration for LearnDash', 'drmx5-learndash'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('drmx5_ld_group'); ?>
                <h2><?php esc_html_e('DRM-X 5.0 account', 'drmx5-learndash'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php self::text_row('admin_email', 'AdminEmail', __('The administrator email address of the DRM-X 5.0 account.', 'drmx5-learndash'), $options, 'email'); ?>
                    <?php self::text_row('webservice_auth', 'WebServiceAuthStr', __('The Web Service Authorization String configured in DRM-X 5.0.', 'drmx5-learndash'), $options, 'password'); ?>
                    <?php self::text_row('group_id', 'GroupID', __('The DRM-X 5.0 user group ID used when creating users and issuing licenses.', 'drmx5-learndash'), $options); ?>
                    <tr>
                        <th scope="row"><label for="drmx5-service-area"><?php esc_html_e('DRM-X service area', 'drmx5-learndash'); ?></label></th>
                        <td><select id="drmx5-service-area" name="drmx5_ld_settings[service_area]">
                            <option value="international" <?php selected($options['service_area'], 'international'); ?>><?php esc_html_e('International (5.drm-x.com)', 'drmx5-learndash'); ?></option>
                            <option value="china" <?php selected($options['service_area'], 'china'); ?>><?php esc_html_e('China (5.drm-x.cn)', 'drmx5-learndash'); ?></option>
                        </select><p class="description"><?php esc_html_e('Select the service domain associated with your DRM-X account.', 'drmx5-learndash'); ?></p></td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Permission source', 'drmx5-learndash'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="drmx5-rights-mode"><?php esc_html_e('RightsID mode', 'drmx5-learndash'); ?></label></th>
                        <td><select id="drmx5-rights-mode" name="drmx5_ld_settings[rights_mode]">
                            <option value="fixed" <?php selected($options['rights_mode'], 'fixed'); ?>><?php esc_html_e('Fixed RightsID and automatically update permission', 'drmx5-learndash'); ?></option>
                            <option value="post" <?php selected($options['rights_mode'], 'post'); ?>><?php esc_html_e('Read the License Profile RightsID from ZJGet POST data', 'drmx5-learndash'); ?></option>
                        </select><p class="description"><?php esc_html_e('Fixed mode updates the configured permission. POST mode uses the RightsID supplied from the DRM-X License Profile through ZJGet and does not update it.', 'drmx5-learndash'); ?></p></td>
                    </tr>
                    <?php self::text_row('fixed_rights_id', __('Fixed RightsID', 'drmx5-learndash'), __('Required only in fixed mode. The plugin updates this permission before obtaining the license.', 'drmx5-learndash'), $options); ?>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>
            <h2><?php esc_html_e('Integration address', 'drmx5-learndash'); ?></h2>
            <p><?php esc_html_e('Set the DRM-X custom login/integration URL to:', 'drmx5-learndash'); ?></p>
            <code><?php echo esc_html(DRMX5_LD_License_Controller::endpoint_url()); ?></code>

            <h2><?php esc_html_e('LearnDash course IDs', 'drmx5-learndash'); ?></h2>
            <p><?php esc_html_e('Enter the WordPress post ID of the LearnDash course in the DRM-X License Profile YourProductID field. Separate multiple course IDs with a hyphen, for example: 123-124.', 'drmx5-learndash'); ?></p>

            <h2><?php esc_html_e('Embed a protected video', 'drmx5-learndash'); ?></h2>
            <code>[zjget-player]https://example.com/protected-video.mp4[/zjget-player]</code>
        </div>
        <?php
    }

    private static function text_row($key, $label, $description, array $options, $type = 'text') {
        ?>
        <tr>
            <th scope="row"><label for="drmx5-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
            <td><input class="regular-text" id="drmx5-<?php echo esc_attr($key); ?>" type="<?php echo esc_attr($type); ?>"
                name="drmx5_ld_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($options[$key]); ?>" autocomplete="off">
                <p class="description"><?php echo esc_html($description); ?></p></td>
        </tr>
        <?php
    }
}


