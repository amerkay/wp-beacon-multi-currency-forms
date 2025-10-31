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
            WBCD_URL . 'public/css/donate.css',
            [],
            WBCD_VERSION
        );
        wp_enqueue_style('wbcd-front');
    }

    public static function enqueue_donation_form()
    {
        $forms = Settings::get_forms_by_currency();

        wp_register_script(
            'wbcd-donate-form',
            WBCD_URL . 'public/js/donate-form.js',
            [],
            WBCD_VERSION,
            true
        );

        wp_localize_script('wbcd-donate-form', 'WBCD_FORM_DATA', [
            'account'          => WBCD_BEACON_ACCOUNT,
            'formsByCurrency'  => $forms,
            'allowedCurrencies' => array_keys($forms),
            // We don’t enqueue Beacon SDK globally; it’s injected once by the page script.
        ]);

        wp_enqueue_script('wbcd-donate-form');
    }

    public static function enqueue_donation_cta()
    {
        $forms = Settings::get_forms_by_currency();
        $url   = Settings::get_target_page_url();

        wp_register_script(
            'wbcd-donate-cta',
            WBCD_URL . 'public/js/donate-cta.js',
            [],
            WBCD_VERSION,
            true
        );

        // Build id+symbol structure expected by the CTA script
        $symbols = ['GBP' => '£', 'EUR' => '€', 'USD' => '$'];
        $byCur   = [];
        foreach ($forms as $code => $id) {
            if (! isset($symbols[$code])) continue;
            $byCur[$code] = ['id' => $id, 'symbol' => $symbols[$code]];
        }

        wp_localize_script('wbcd-donate-cta', 'WBCD_CTA_DATA', [
            'account'         => WBCD_BEACON_ACCOUNT,
            'formsByCurrency' => $byCur,
            'baseURL'         => $url,
        ]);

        wp_enqueue_script('wbcd-donate-cta');
    }
}
