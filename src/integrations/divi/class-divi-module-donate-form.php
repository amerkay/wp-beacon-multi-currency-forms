<?php

namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Form_Module extends Abstract_WBCD_Divi_Module
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
        return [
            'form_name' => $this->get_form_selection_field(__('Choose which donation form to display', 'wp-beacon-crm-donate')),
            'custom_params' => [
                'label' => __('Required URL Parameters', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '',
                'description' => __('Enter required parameters in URL format: bcn_c_adopted_animal=12345&bcn_custom=abc. If missing, users will be redirected to include them.', 'wp-beacon-crm-donate'),
            ],
        ];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        $form_name = $this->get_form_name_from_props();
        $custom_params = $this->parse_custom_params_from_props();

        $render_args = [
            'customParams' => $custom_params
        ];

        $this->enqueue_base_assets();
        \WBCD\Assets::enqueue_donation_form($form_name);
        return \WBCD\Render\Donate_Form_Render::render($form_name, $render_args);
    }
}
