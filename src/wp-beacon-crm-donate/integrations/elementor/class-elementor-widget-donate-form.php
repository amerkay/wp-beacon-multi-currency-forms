<?php


namespace WBCD\Integrations\Elementor;

// If Elementor isn't loaded, bail before defining the class.
if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Form_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wbcd_donate_form';
    }
    public function get_title()
    {
        return __('Beacon Donation Form', 'wp-beacon-crm-donate');
    }
    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }
    public function get_categories()
    {
        return ['general'];
    }
    protected function render()
    {
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_form();
        echo \WBCD\Render\Donate_Form_Render::render(); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
