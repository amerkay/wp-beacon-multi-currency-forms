<?php


namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Box_Module extends \ET_Builder_Module
{
    public $slug       = 'wbcd_divi_donate_box';
    public $vb_support = 'off';
    public $name       = '';

    function init()
    {
        $this->name = esc_html__('Beacon Donation Box', 'wp-beacon-crm-donate');
    }

    public function get_fields()
    {
        return [];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta();
        return \WBCD\Render\Donate_CTA_Render::render();
    }
}
