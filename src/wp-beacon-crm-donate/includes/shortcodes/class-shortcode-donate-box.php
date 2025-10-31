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
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta();
        return Donate_CTA_Render::render();
    }
}
