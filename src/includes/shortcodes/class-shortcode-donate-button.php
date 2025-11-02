<?php

namespace WBCD\Shortcodes;

use WBCD\Render\Donate_Button_Render;

if (! defined('ABSPATH')) exit;

class Shortcode_Donate_Button
{
    public static function register()
    {
        add_shortcode('beaconcrm_donate_button', [__CLASS__, 'handle']);
    }

    public static function handle($atts = [])
    {
        $atts = shortcode_atts([
            'form' => '', // Form name
            'color' => '',
            'text' => 'Donate',
            'size' => 'md',
            'amount' => '',
            'frequency' => '',
            'currency' => '',
            'params' => '', // JSON string or URL-encoded string of custom params
        ], $atts, 'beaconcrm_donate_button');

        $form_name = $atts['form'];

        // Parse custom params from URL-encoded format
        $custom_params = [];
        if (!empty($atts['params'])) {
            // Parse URL-encoded format like "key1=value1&key2=value2"
            parse_str($atts['params'], $custom_params);
        }

        $render_args = [
            'color' => $atts['color'],
            'text' => $atts['text'],
            'size' => $atts['size'],
            'amount' => $atts['amount'],
            'frequency' => $atts['frequency'],
            'currency' => $atts['currency'],
            'customParams' => $custom_params,
        ];

        \WBCD\Assets::enqueue_front_base();

        return Donate_Button_Render::render($form_name, $render_args);
    }
}
