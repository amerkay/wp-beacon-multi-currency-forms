<?php

namespace WBCD\Integrations\Elementor;

if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Box_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wbcd_donate_box';
    }
    public function get_title()
    {
        return __('Beacon Donation Box', 'wp-beacon-crm-donate');
    }
    public function get_icon()
    {
        return 'eicon-button';
    }
    public function get_categories()
    {
        return ['general'];
    }
    protected function render()
    {
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta();
        echo \WBCD\Render\Donate_CTA_Render::render(); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
