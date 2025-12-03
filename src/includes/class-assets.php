<?php

namespace BMCF;

if (!defined('ABSPATH'))
    exit;

class Assets
{
    /**
     * Track if constants have been localized to prevent duplicate output.
     */
    private static $constants_localized = false;

    /**
     * Track if Beacon SDK has been enqueued to prevent duplicate calls.
     */
    private static $sdk_enqueued = false;

    /**
     * Enqueue Beacon SDK loader script.
     * Call this to ensure the SDK is loaded (respects single-load pattern).
     */
    public static function enqueue_beacon_sdk()
    {
        if (self::$sdk_enqueued) {
            return;
        }

        wp_enqueue_script(
            'bmcf-beacon-sdk',
            BMCF_URL . 'public/js/beacon-sdk-loader.js',
            [],
            BMCF_VERSION,
            false // Load in head for early initialization
        );

        self::$sdk_enqueued = true;
    }

    public static function enqueue_front_base()
    {
        // Shared front styles
        wp_register_style(
            'bmcf-front',
            BMCF_URL . 'public/css/donate.css',
            [],
            BMCF_VERSION
        );
        wp_enqueue_style('bmcf-front');

        // Inject CSS variables from constants (AFTER enqueue)
        self::inject_css_variables();

        // Localize constants for JavaScript (output in wp_head) - only once
        if (!self::$constants_localized) {
            add_action('wp_head', [__CLASS__, 'localize_constants'], 1);
            self::$constants_localized = true;
        }
    }

    /**
     * Localize constants for use in JavaScript.
     * Makes default values available to all frontend JS scripts.
     */
    public static function localize_constants()
    {
        $constants_data = [
            'colors' => Constants::get_all_colors(),
            'presets' => Constants::get_all_presets(),
            'frequencies' => Constants::get_default_frequencies(),
            'buttonSizes' => Constants::get_valid_button_sizes(),
            'version' => Constants::get_version(),
        ];

        // Register a dummy script to attach inline script to
        wp_register_script('bmcf-constants', '', [], BMCF_VERSION, false);
        wp_enqueue_script('bmcf-constants');
        
        // Add inline script with constants data
        $inline_script = 'window.BMCF_CONSTANTS = ' . wp_json_encode($constants_data) . ';';
        wp_add_inline_script('bmcf-constants', $inline_script);
    }

    /**
     * Inject CSS variables from constants into document head.
     * Sets default values for CSS custom properties at :root level so all components can access them.
     */
    public static function inject_css_variables()
    {
        $colors = Constants::get_all_colors();
        // Inject into :root so all BMCF components (buttons, boxes, forms) can access the variables
        $inline_css = ":root{--bmcf-brand:{$colors['brand']};--bmcf-primary:{$colors['primary']};--bmcf-text:{$colors['text']};--bmcf-border:{$colors['border']};}";
        wp_add_inline_style('bmcf-front', $inline_css);
    }

    public static function enqueue_donation_form($form_name = '')
    {
        // Enqueue Beacon SDK if not already loaded globally
        if (!Settings::get_load_beacon_globally()) {
            self::enqueue_beacon_sdk();
        }

        $beaconAccountName = Settings::get_beacon_account();
        $forms = Settings::get_forms_by_currency($form_name);

        // Get default currency for this specific form
        $default_currency = Settings::get_default_currency($form_name);

        // Get UTM parameter mappings and field names
        $utm_params = Settings::get_utm_params();
        $utm_field_names = Settings::get_utm_field_names();

        wp_register_script(
            'bmcf-donate-form',
            BMCF_URL . 'public/js/donate-form.js',
            [],
            BMCF_VERSION,
            true
        );

        wp_localize_script('bmcf-donate-form', 'BMCF_FORM_DATA', [
            'beaconAccountName' => $beaconAccountName,
            'formsByCurrency' => $forms,
            'allowedCurrencies' => array_keys($forms),
            'defaultCurrency' => $default_currency,
            'utmParams' => $utm_params,
            'utmFieldNames' => $utm_field_names,
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);

        wp_enqueue_script('bmcf-donate-form');
    }

    public static function enqueue_donation_box($form_name = '', $target_page_url = '')
    {
        // Enqueue Beacon SDK if not already loaded globally
        if (!Settings::get_load_beacon_globally()) {
            self::enqueue_beacon_sdk();
        }

        $beaconAccountName = Settings::get_beacon_account();
        $forms = Settings::get_forms_by_currency($form_name);
        $symbols = Settings::get_currency_symbols();

        // Get default currency for this specific form
        $default_currency = Settings::get_default_currency($form_name);

        wp_register_script(
            'bmcf-donate-box',
            BMCF_URL . 'public/js/donate-box.js',
            [],
            BMCF_VERSION,
            true
        );

        // Build id+symbol structure expected by the donate box script
        $byCur = [];
        foreach ($forms as $code => $id) {
            $symbol = isset($symbols[$code]) ? $symbols[$code] : $code;
            $byCur[$code] = ['id' => $id, 'symbol' => $symbol];
        }

        wp_localize_script('bmcf-donate-box', 'BMCF_BOX_DATA', [
            'beaconAccountName' => $beaconAccountName,
            'formsByCurrency' => $byCur,
            'baseURL' => $target_page_url,
            'defaultCurrency' => $default_currency,
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);

        wp_enqueue_script('bmcf-donate-box');
    }
}
