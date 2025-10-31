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
        // Ensure assets on shortcode use
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_form();
        return Donate_Form_Render::render();
    }
}
