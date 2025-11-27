<?php

namespace BMCF\Shortcodes;

use BMCF\Render\Donate_Form_Render;

if (!defined('ABSPATH'))
    exit;

class Shortcode_Donate_Form
{
    public static function register()
    {
        add_shortcode('beacondonate_form', [__CLASS__, 'handle']);
    }

    public static function handle($atts = [])
    {
        $atts = shortcode_atts([
            'form' => '', // Form name
            'params' => '', // URL-encoded string of custom params
            'default_frequency' => '', // Default frequency: single, monthly, annual
            'default_amount' => '', // Default amount
        ], $atts, 'beacondonate_form');

        $form_name = $atts['form'];

        // Parse custom params using utility
        $custom_params = \BMCF\Utils\Params_Parser::from_url_encoded($atts['params']);

        $render_args = [
            'customParams' => $custom_params,
            'defaultFrequency' => $atts['default_frequency'],
            'defaultAmount' => $atts['default_amount']
        ];

        // Ensure assets on shortcode use
        \BMCF\Assets::enqueue_front_base();
        \BMCF\Assets::enqueue_donation_form($form_name);

        return Donate_Form_Render::render($form_name, $render_args);
    }
}
