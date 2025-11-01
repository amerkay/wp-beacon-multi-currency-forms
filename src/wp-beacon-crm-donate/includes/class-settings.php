<?php

namespace WBCD;

if (! defined('ABSPATH')) exit;

class Settings
{

    public static function add_menu()
    {
        add_options_page(
            __('Beacon Donate', 'wp-beacon-crm-donate'),
            __('Beacon Donate', 'wp-beacon-crm-donate'),
            'manage_options',
            'wbcd-settings',
            [__CLASS__, 'render_page']
        );
    }

    public static function register()
    {
        // Defaults mirror the tested IDs you provided.
        $defaults = [
            'GBP' => '57085719',
            'EUR' => '694de004',
            'USD' => '17a36966',
        ];
        add_option('wbcd_beacon_account', '');
        add_option('wbcd_currencies', $defaults);
        add_option('wbcd_target_page_id', 0);

        register_setting('wbcd_group', 'wbcd_beacon_account', [
            'type'              => 'string',
            'sanitize_callback' => [__CLASS__, 'sanitize_account'],
            'default'           => '',
        ]);

        register_setting('wbcd_group', 'wbcd_currencies', [
            'type'              => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_currencies'],
            'default'           => $defaults,
        ]);

        register_setting('wbcd_group', 'wbcd_target_page_id', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ]);

        add_settings_section(
            'wbcd_section_main',
            __('Donation Settings', 'wp-beacon-crm-donate'),
            function () {
                echo '<p>' . esc_html__('Configure your Beacon account name, form IDs per currency, and select the page that hosts the full donation form.', 'wp-beacon-crm-donate') . '</p>';
            },
            'wbcd-settings'
        );

        add_settings_field(
            'wbcd_field_beacon_account',
            __('Beacon Account Name', 'wp-beacon-crm-donate'),
            [__CLASS__, 'field_beacon_account'],
            'wbcd-settings',
            'wbcd_section_main'
        );

        add_settings_field(
            'wbcd_field_currencies',
            __('Currencies → Beacon Form IDs', 'wp-beacon-crm-donate'),
            [__CLASS__, 'field_currencies'],
            'wbcd-settings',
            'wbcd_section_main'
        );

        add_settings_field(
            'wbcd_field_target_page',
            __('Donation Form Page', 'wp-beacon-crm-donate'),
            [__CLASS__, 'field_target_page'],
            'wbcd-settings',
            'wbcd_section_main'
        );
    }

    public static function sanitize_account($input)
    {
        // Account name should be alphanumeric/hyphen/underscore only
        return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($input));
    }

    public static function sanitize_currencies($input)
    {
        $out = ['GBP' => '', 'EUR' => '', 'USD' => ''];
        if (is_array($input)) {
            foreach ($out as $code => $_) {
                $raw = isset($input[$code]) ? (string) $input[$code] : '';
                $out[$code] = preg_replace('/[^a-zA-Z0-9]/', '', $raw); // alnum only
            }
        }
        return $out;
    }

    public static function field_beacon_account()
    {
        $value = get_option('wbcd_beacon_account', '');
        echo '<input type="text" id="wbcd_beacon_account" name="wbcd_beacon_account" value="' . esc_attr($value) . '" class="regular-text" required />';
        echo '<p class="description">' . esc_html__('Enter your BeaconCRM account name (e.g., "yourorg"). This is required.', 'wp-beacon-crm-donate') . '</p>';
        echo '<p class="description"><strong>' . esc_html__('How to find your account name:', 'wp-beacon-crm-donate') . '</strong></p>';
        echo '<ol class="description" style="margin-left: 1.5em;">';
        echo '<li>' . esc_html__('Navigate to any of your forms on BeaconCRM\'s interface.', 'wp-beacon-crm-donate') . '</li>';
        echo '<li>' . esc_html__('Click it, then click "Embed".', 'wp-beacon-crm-donate') . '</li>';
        echo '<li>' . wp_kses_post(__('The form code should look like <code>&lt;div class="beacon-form" data-account="yourorg" data-form="000000"&gt;&lt;/div&gt;</code>. In this example, the account name is <code>yourorg</code>.', 'wp-beacon-crm-donate')) . '</li>';
        echo '</ol>';
    }

    public static function field_currencies()
    {
        $cur = get_option('wbcd_currencies', []);
        $codes = ['GBP' => '£', 'EUR' => '€', 'USD' => '$'];
        echo '<table class="wbcd-currency-table"><tbody>';
        foreach ($codes as $code => $symbol) {
            $val = isset($cur[$code]) ? esc_attr($cur[$code]) : '';
            echo '<tr>';
            echo '<th scope="row"><label for="wbcd_' . esc_attr($code) . '">' . esc_html($code . ' ' . $symbol) . '</label></th>';
            echo '<td><input type="text" id="wbcd_' . esc_attr($code) . '" name="wbcd_currencies[' . esc_attr($code) . ']" value="' . $val . '" class="regular-text" /></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p class="description">' . esc_html__('Enter the BeaconCRM form IDs for each supported currency (GBP, EUR, USD).', 'wp-beacon-crm-donate') . '</p>';
    }

    public static function field_target_page()
    {
        $selected = (int) get_option('wbcd_target_page_id', 0);
        $args = [
            'name'              => 'wbcd_target_page_id',
            'echo'              => 1,
            'show_option_none'  => __('— Select a page —', 'wp-beacon-crm-donate'),
            'option_none_value' => '0',
            'selected'          => $selected,
        ];
        // Native page dropdown.
        wp_dropdown_pages($args); // Core helper for page selection. :contentReference[oaicite:7]{index=7}
        echo '<p class="description">' . esc_html__('This is where the CTA box will send donors (the full donation form).', 'wp-beacon-crm-donate') . '</p>';
    }

    public static function render_page()
    {
        if (! current_user_can('manage_options')) return;
?>
        <div class="wrap">
            <h1><?php esc_html_e('Beacon Donate', 'wp-beacon-crm-donate'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wbcd_group');
                do_settings_sections('wbcd-settings');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    // Helpers used by other classes
    public static function get_beacon_account()
    {
        return get_option('wbcd_beacon_account', '');
    }

    public static function get_forms_by_currency()
    {
        $map = get_option('wbcd_currencies', []);
        $defaults = ['GBP' => '57085719', 'EUR' => '694de004', 'USD' => '17a36966'];
        return wp_parse_args($map, $defaults);
    }

    public static function get_target_page_url()
    {
        $id = (int) get_option('wbcd_target_page_id', 0);
        if ($id > 0) {
            $url = get_permalink($id);
            if ($url) return $url;
        }
        // Reasonable fallback if not configured: look for /donation-form/; else home.
        $maybe = home_url('/donation-form/');
        return $maybe ?: home_url('/');
    }
}
