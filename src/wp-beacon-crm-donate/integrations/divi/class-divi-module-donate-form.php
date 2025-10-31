<?php


namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Form_Module extends \ET_Builder_Module
{
    public $slug       = 'wbcd_divi_donate_form';
    public $vb_support = 'off';
    public $name       = '';

    function init()
    {
        $this->name = esc_html__('Beacon Donation Form', 'wp-beacon-crm-donate');
    }

    public function get_fields()
    {
        return [];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_form();
        return \WBCD\Render\Donate_Form_Render::render();
    }
}
