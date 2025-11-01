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
        ], $atts, 'beaconcrm_donate_box');

        $form_name = $atts['form'];

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta($form_name);

        return Donate_CTA_Render::render($form_name);
    }
}
