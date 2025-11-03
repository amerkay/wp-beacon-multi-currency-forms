<?php

namespace WBCD\Shortcodes;

use WBCD\Render\Donate_CTA_Render;

if (! defined('ABSPATH')) exit;

class Shortcode_Donate_Box
{
    public static function register()
    {
        add_shortcode('beaconcrm_donate_box', [__CLASS__, 'handle']);
    }

    public static function handle($atts = [])
    {
        $default_presets = \WBCD\Constants::get_all_presets();

        $atts = shortcode_atts([
            'form' => '', // Form name
            'primary_color' => '',
            'brand_color' => '',
            'title' => 'Make a donation',
            'subtitle' => 'Pick your currency, frequency, and amount',
            'notice' => "You'll be taken to our secure donation form to complete your gift.",
            'button_text' => 'Donate now →',
            'params' => '', // JSON string or serialized array of custom params
            'frequencies' => 'single,monthly,annual', // Comma-separated list
            'presets_single' => implode(',', $default_presets['single']),
            'presets_monthly' => implode(',', $default_presets['monthly']),
            'presets_annual' => implode(',', $default_presets['annual']),
        ], $atts, 'beaconcrm_donate_box');

        $form_name = $atts['form'];

        // Parse custom params using utility
        $custom_params = \WBCD\Utils\Params_Parser::from_url_encoded($atts['params']);

        // Parse allowed frequencies using utility
        $allowed_frequencies = \WBCD\Utils\Frequency_Parser::from_csv($atts['frequencies']);

        // Parse default presets using utility
        $default_presets = \WBCD\Utils\Preset_Parser::parse_all_presets($atts);

        $render_args = [
            'primaryColor' => $atts['primary_color'],
            'brandColor' => $atts['brand_color'],
            'title' => $atts['title'],
            'subtitle' => $atts['subtitle'],
            'noticeText' => $atts['notice'],
            'buttonText' => $atts['button_text'],
            'customParams' => $custom_params,
            'allowedFrequencies' => $allowed_frequencies,
            'defaultPresets' => $default_presets
        ];

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta($form_name);

        return Donate_CTA_Render::render($form_name, $render_args);
    }
}
