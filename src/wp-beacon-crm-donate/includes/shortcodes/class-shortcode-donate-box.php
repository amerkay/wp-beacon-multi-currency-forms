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
            'presets_single' => '10,20,30',
            'presets_monthly' => '5,10,15',
            'presets_annual' => '50,100,200',
        ], $atts, 'beaconcrm_donate_box');

        $form_name = $atts['form'];

        // Parse custom params from URL-encoded format
        $custom_params = [];
        if (!empty($atts['params'])) {
            // Parse URL-encoded format like "key1=value1&key2=value2"
            parse_str($atts['params'], $custom_params);
        }

        // Parse allowed frequencies
        $allowed_frequencies = array_map('trim', explode(',', $atts['frequencies']));
        $allowed_frequencies = array_filter($allowed_frequencies, function ($f) {
            return in_array($f, ['single', 'monthly', 'annual']);
        });
        if (empty($allowed_frequencies)) {
            $allowed_frequencies = ['single', 'monthly', 'annual'];
        }

        // Parse default presets
        $default_presets = [];
        foreach (['single', 'monthly', 'annual'] as $freq) {
            $preset_key = 'presets_' . $freq;
            if (!empty($atts[$preset_key])) {
                $amounts = array_map('trim', explode(',', $atts[$preset_key]));
                $amounts = array_map('floatval', $amounts);
                $amounts = array_filter($amounts, function ($n) {
                    return $n > 0;
                });
                $default_presets[$freq] = array_values($amounts);
            }
        }
        // Set defaults if not specified
        if (empty($default_presets['single'])) $default_presets['single'] = [10, 20, 30];
        if (empty($default_presets['monthly'])) $default_presets['monthly'] = [5, 10, 15];
        if (empty($default_presets['annual'])) $default_presets['annual'] = [50, 100, 200];

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
