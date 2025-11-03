<?php

namespace WBCD;

if (! defined('ABSPATH')) exit;

class Assets
{
    /**
     * Track if constants have been localized to prevent duplicate output.
     */
    private static $constants_localized = false;

    public static function enqueue_front_base()
    {
        // Shared front styles
        wp_register_style(
            'wbcd-front',
            WPBCD_URL . 'public/css/donate.css',
            [],
            WPBCD_VERSION
        );
        wp_enqueue_style('wbcd-front');

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

        // Output as inline script in head
        echo '<script type="text/javascript">';
        echo 'window.WBCD_CONSTANTS = ' . wp_json_encode($constants_data) . ';';
        echo '</script>';
    }

    /**
     * Inject CSS variables from constants into document head.
     * Sets default values for CSS custom properties at :root level so all components can access them.
     */
    public static function inject_css_variables()
    {
        $colors = Constants::get_all_colors();
        // Inject into :root so all WBCD components (buttons, boxes, forms) can access the variables
        $inline_css = ":root{--wpbcd-brand:{$colors['brand']};--wpbcd-primary:{$colors['primary']};--wpbcd-text:{$colors['text']};--wpbcd-border:{$colors['border']};}";
        wp_add_inline_style('wbcd-front', $inline_css);
    }

    public static function enqueue_donation_form($form_name = '')
    {
        $beaconAccountName = Settings::get_beacon_account();
        $forms = Settings::get_forms_by_currency($form_name);

        // Get default currency for this specific form
        $default_currency = Settings::get_default_currency($form_name);

        wp_register_script(
            'wbcd-donate-form',
            WPBCD_URL . 'public/js/donate-form.js',
            [],
            WPBCD_VERSION,
            true
        );

        wp_localize_script('wbcd-donate-form', 'WPBCD_FORM_DATA', [
            'beaconAccountName' => $beaconAccountName,
            'formsByCurrency'  => $forms,
            'allowedCurrencies' => array_keys($forms),
            'defaultCurrency' => $default_currency,
        ]);

        wp_enqueue_script('wbcd-donate-form');
    }

    public static function enqueue_donation_cta($form_name = '')
    {
        $beaconAccountName = Settings::get_beacon_account();
        $forms = Settings::get_forms_by_currency($form_name);
        $symbols = Settings::get_currency_symbols();
        $url   = Settings::get_target_page_url($form_name);

        // Get default currency for this specific form
        $default_currency = Settings::get_default_currency($form_name);

        wp_register_script(
            'wbcd-donate-cta',
            WPBCD_URL . 'public/js/donate-cta.js',
            [],
            WPBCD_VERSION,
            true
        );

        // Build id+symbol structure expected by the CTA script
        $byCur = [];
        foreach ($forms as $code => $id) {
            $symbol = isset($symbols[$code]) ? $symbols[$code] : $code;
            $byCur[$code] = ['id' => $id, 'symbol' => $symbol];
        }

        wp_localize_script('wbcd-donate-cta', 'WPBCD_CTA_DATA', [
            'beaconAccountName' => $beaconAccountName,
            'formsByCurrency' => $byCur,
            'baseURL'         => $url,
            'defaultCurrency' => $default_currency,
        ]);

        wp_enqueue_script('wbcd-donate-cta');
    }
}
