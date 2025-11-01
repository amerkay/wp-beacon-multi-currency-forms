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
        $forms = \WBCD\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'wp-beacon-crm-donate')];

        foreach ($forms as $name => $label) {
            $options[$name] = $label;
        }

        return [
            'form_name' => [
                'label' => __('Select Form', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $options,
                'default' => '',
                'description' => __('Choose which donation form to use', 'wp-beacon-crm-donate'),
            ],
        ];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        $form_name = isset($this->props['form_name']) ? $this->props['form_name'] : '';

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta($form_name);
        return \WBCD\Render\Donate_CTA_Render::render($form_name);
    }
}
