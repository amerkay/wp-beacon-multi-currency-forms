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
        ], $atts, 'beaconcrm_donate_form');

        $form_name = $atts['form'];

        // Parse custom params from URL-encoded format
        $custom_params = [];
        if (!empty($atts['params'])) {
            // Parse URL-encoded format like "key1=value1&key2=value2"
            parse_str($atts['params'], $custom_params);
        }

        $render_args = [
            'customParams' => $custom_params
        ];

        // Ensure assets on shortcode use
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_form($form_name);

        return Donate_Form_Render::render($form_name, $render_args);
    }
}
