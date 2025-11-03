<?php

namespace WBCD\Shortcodes;

use WBCD\Render\Donate_Box_Render;

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
            'target_page_id' => 0, // Target page ID
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

        // Get target page URL
        $target_page_url = '';
        $target_page_id = absint($atts['target_page_id']);
        if ($target_page_id > 0) {
            $permalink = get_permalink($target_page_id);
            if ($permalink) {
                $target_page_url = $permalink;
            }
        }

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
            'defaultPresets' => $default_presets,
            'targetPageUrl' => $target_page_url
        ];

        \WBCD\Assets::enqueue_front_base();

        return Donate_Box_Render::render($form_name, $render_args);
    }
}
