<?php

namespace WBCD\Shortcodes;

use WBCD\Render\Donate_Form_Render;

if (! defined('ABSPATH')) exit;

class Shortcode_Donate_Form
{
    public static function register()
    {
        add_shortcode('beaconcrm_donate_form', [__CLASS__, 'handle']);
    }

    public static function handle($atts = [])
    {
        $atts = shortcode_atts([
            'form' => '', // Form name
            'params' => '', // URL-encoded string of custom params
            'default_frequency' => '', // Default frequency: single, monthly, annual
            'default_amount' => '', // Default amount
        ], $atts, 'beaconcrm_donate_form');

        $form_name = $atts['form'];

        // Parse custom params using utility
        $custom_params = \WBCD\Utils\Params_Parser::from_url_encoded($atts['params']);

        $render_args = [
            'customParams' => $custom_params,
            'defaultFrequency' => $atts['default_frequency'],
            'defaultAmount' => $atts['default_amount']
        ];

        // Ensure assets on shortcode use
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_form($form_name);

        return Donate_Form_Render::render($form_name, $render_args);
    }
}
