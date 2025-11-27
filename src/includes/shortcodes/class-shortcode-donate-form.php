<?php

namespace BMCF\Shortcodes;

use BMCF\Render\Donate_Form_Render;

if (!defined('ABSPATH'))
    exit;

class Shortcode_Donate_Form
{
    public static function register()
    {
        add_shortcode('beacon_donate_form', [__CLASS__, 'handle']);
    }

    public static function handle($atts = [])
    {
        $atts = shortcode_atts([
            'form' => '', // Form name
            'params' => '', // URL-encoded string of custom params
            'default_frequency' => '', // Default frequency: single, monthly, annual
            'default_amount' => '', // Default amount
        ], $atts, 'beacon_donate_form');

        $form_name = $atts['form'];

        // Parse custom params using utility
        $custom_params = \BMCF\Utils\Params_Parser::from_url_encoded($atts['params']);

        $render_args = [
            'customParams' => $custom_params,
            'defaultFrequency' => sanitize_text_field($atts['default_frequency']),
            'defaultAmount' => sanitize_text_field($atts['default_amount'])
        ];

        // Ensure assets on shortcode use
        \BMCF\Assets::enqueue_front_base();
        \BMCF\Assets::enqueue_donation_form($form_name);

        $output = Donate_Form_Render::render($form_name, $render_args);

        // Strip whitespace between tags to prevent wpautop from adding <p> tags
        // This is necessary when shortcode is used in Block Editor
        $output = preg_replace('/>\s+</', '><', $output);

        return $output;
    }
}
