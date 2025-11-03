<?php

namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Button_Module extends Abstract_WBCD_Divi_Module
{
    public $slug       = 'wbcd_divi_donate_button';
    public $vb_support = 'partial';
    public $name       = '';

    function init()
    {
        $this->name = esc_html__('Beacon Donation Button', 'wp-beacon-crm-donate');
    }

    public function get_fields()
    {
        return [
            'form_name' => $this->get_form_selection_field(__('Choose which donation form to link to', 'wp-beacon-crm-donate')),
            'amount' => [
                'label' => __('Amount', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '',
                'description' => __('Pre-set donation amount (leave empty for user choice)', 'wp-beacon-crm-donate'),
            ],
            'frequency' => [
                'label' => __('Frequency', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $this->get_frequency_options(),
                'default' => '',
                'description' => __('Pre-set donation frequency', 'wp-beacon-crm-donate'),
            ],
            'currency' => [
                'label' => __('Currency', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $this->get_currency_options(),
                'default' => '',
                'description' => __('Pre-set currency. Make sure the selected currency is available in the chosen form.', 'wp-beacon-crm-donate'),
            ],
            'text' => [
                'label' => __('Button Text', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => __('Donate', 'wp-beacon-crm-donate'),
            ],
            'size' => [
                'label' => __('Button Size', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $this->get_button_size_options(),
                'default' => 'md',
            ],
            'color' => [
                'label' => __('Button Color', 'wp-beacon-crm-donate'),
                'type' => 'color-alpha',
                'default' => '',
                'description' => __('Leave empty for default color', 'wp-beacon-crm-donate'),
            ],
            'custom_params' => $this->get_custom_params_field(),
        ];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        $form_name = $this->get_form_name_from_props();
        $custom_params = $this->parse_custom_params_from_props();

        $render_args = [
            'color' => isset($this->props['color']) ? $this->props['color'] : '',
            'text' => isset($this->props['text']) ? $this->props['text'] : __('Donate', 'wp-beacon-crm-donate'),
            'size' => isset($this->props['size']) ? $this->props['size'] : 'md',
            'amount' => isset($this->props['amount']) ? $this->props['amount'] : '',
            'frequency' => isset($this->props['frequency']) ? $this->props['frequency'] : '',
            'currency' => isset($this->props['currency']) ? $this->props['currency'] : '',
            'customParams' => $custom_params,
        ];

        $this->enqueue_base_assets();
        return \WBCD\Render\Donate_Button_Render::render($form_name, $render_args);
    }
}
