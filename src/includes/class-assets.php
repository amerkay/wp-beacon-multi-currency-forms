<?php

namespace WBCD;

if (! defined('ABSPATH')) exit;

class Assets
{

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
