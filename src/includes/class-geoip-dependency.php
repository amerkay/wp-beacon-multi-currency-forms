<?php

namespace WBCD;

if (!defined('ABSPATH'))
    exit;

class GeoIP_Dependency
{

    public static function admin_notices()
    {
        if (!current_user_can('manage_options'))
            return;

        // Check if the GeoIP setup notice has been dismissed
        if (get_option('wbcd_geoip_notice_dismissed', false)) {
            return;
        }

        // Check if Beacon account is configured
        $beaconAccountName = Settings::get_beacon_account();
        if (empty($beaconAccountName)) {
            $url = admin_url('options-general.php?page=wbcd-settings');
            echo '<div class="notice notice-error"><p>';
            echo wp_kses_post(sprintf(
                /* translators: 1: open link, 2: close link */
                __('Beacon Multi-Currency Forms requires configuration. Please %1$senter your Beacon account name%2$s in the settings.', 'beacon-multi-currency-forms'),
                '<a href="' . esc_url($url) . '">',
                '</a>'
            ));
            echo '</p></div>';
            return;
        }

        // Check if the plugin is active
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $is_active = function_exists('is_plugin_active') && is_plugin_active('geoip-detect/geoip-detect.php');

        if (!$is_active) {
            $url = admin_url('plugin-install.php?tab=plugin-information&plugin=geoip-detect&TB_iframe=true&width=772&height=785');
            echo '<div class="notice notice-error"><p>';
            echo wp_kses_post(sprintf(
                /* translators: 1: open link, 2: close link */
                __('Beacon Multi-Currency Forms requires the %1$sGeolocation IP Detection%2$s plugin. Please install & activate it.', 'beacon-multi-currency-forms'),
                '<a href="' . esc_url($url) . '" rel="noopener noreferrer">',
                '</a>'
            ));
            echo '</p></div>';
            return;
        }

        // Gentle guidance to enable AJAX/JS API + MaxMind credentials
        $settings_url = admin_url('options-general.php?page=geoip-detect%2Fgeoip-detect.php');
        $ajax_doc = 'https://github.com/yellowtree/geoip-detect/wiki/API%3A-AJAX';
        $mm_doc = 'https://www.maxmind.com/en/create-account';

        echo '<div class="notice notice-info is-dismissible" data-dismissible="wbcd-geoip-notice"><p>';
        echo wp_kses_post(sprintf(
            /* translators: 1: settings link open, 2: close, 3: docs open, 4: close, 5: MaxMind open, 6: close */
            __('For reliable currency auto-detection on cached pages, go to %1$sGeolocation IP Detection settings%2$s and enable the JS/AJAX endpoint. See the %3$sAJAX docs%4$s. Also configure automatic GeoLite2 updates with your %5$sMaxMind Account ID & License Key%6$s.', 'beacon-multi-currency-forms'),
            '<a href="' . esc_url($settings_url) . '" rel="noopener noreferrer">',
            '</a>',
            '<a href="' . esc_url($ajax_doc) . '" target="_blank" rel="noopener noreferrer">',
            '</a>',
            '<a href="' . esc_url($mm_doc) . '" target="_blank" rel="noopener noreferrer">',
            '</a>'
        ));
        echo '</p></div>';
    }

    public static function dismiss_notice()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        check_ajax_referer('wbcd_dismiss_geoip_notice', 'nonce');

        update_option('wbcd_geoip_notice_dismissed', true);
        wp_send_json_success();
    }

    public static function enqueue_dismiss_script()
    {
        if (!current_user_can('manage_options'))
            return;
        if (get_option('wbcd_geoip_notice_dismissed', false))
            return;

        ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $(document).on('click', '.notice[data-dismissible="wbcd-geoip-notice"] .notice-dismiss', function () {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'wbcd_dismiss_geoip_notice',
                                    nonce: '<?php echo esc_js(wp_create_nonce('wbcd_dismiss_geoip_notice')); ?>'
                                }
                            });
                        });
                    });
                </script>
                <?php
    }
}
