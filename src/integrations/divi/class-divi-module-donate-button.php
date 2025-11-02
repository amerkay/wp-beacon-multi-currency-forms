<?php

namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Button_Module extends \ET_Builder_Module
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
        $forms = \WBCD\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'wp-beacon-crm-donate')];

        foreach ($forms as $name => $label) {
            $options[$name] = $label;
        }

        // Get all available currencies across all forms
        $all_currencies = \WBCD\Settings::get_forms_by_currency();
        $currency_options = ['' => __('None (use default)', 'wp-beacon-crm-donate')];
        foreach (array_keys($all_currencies) as $code) {
            $currency_options[$code] = $code;
        }

        return [
            'form_name' => [
                'label' => __('Select Form', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $options,
                'default' => '',
                'description' => __('Choose which donation form to link to', 'wp-beacon-crm-donate'),
            ],
            'amount' => [
                'label' => __('Amount', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '',
                'description' => __('Pre-set donation amount (leave empty for user choice)', 'wp-beacon-crm-donate'),
            ],
            'frequency' => [
                'label' => __('Frequency', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => [
                    '' => __('None (use default)', 'wp-beacon-crm-donate'),
                    'single' => __('Single', 'wp-beacon-crm-donate'),
                    'monthly' => __('Monthly', 'wp-beacon-crm-donate'),
                    'annual' => __('Annual', 'wp-beacon-crm-donate'),
                ],
                'default' => '',
                'description' => __('Pre-set donation frequency', 'wp-beacon-crm-donate'),
            ],
            'currency' => [
                'label' => __('Currency', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $currency_options,
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
                'options' => [
                    'md' => __('Medium', 'wp-beacon-crm-donate'),
                    'lg' => __('Large', 'wp-beacon-crm-donate'),
                    'xl' => __('Extra Large', 'wp-beacon-crm-donate'),
                ],
                'default' => 'md',
            ],
            'color' => [
                'label' => __('Button Color', 'wp-beacon-crm-donate'),
                'type' => 'color-alpha',
                'default' => '',
                'description' => __('Leave empty for default color', 'wp-beacon-crm-donate'),
            ],
            'custom_params' => [
                'label' => __('Custom URL Parameters', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '',
                'description' => __('Enter custom parameters in URL format: bcn_c_adopted_animal=12345&key2=value2. This will be added to the URL of the donation form.', 'wp-beacon-crm-donate'),
            ],
        ];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        $form_name = isset($this->props['form_name']) ? $this->props['form_name'] : '';

        // Parse custom params from URL-encoded format
        $custom_params = [];
        if (!empty($this->props['custom_params'])) {
            // Parse URL-encoded format
            parse_str($this->props['custom_params'], $custom_params);
        }

        $render_args = [
            'color' => isset($this->props['color']) ? $this->props['color'] : '',
            'text' => isset($this->props['text']) ? $this->props['text'] : __('Donate', 'wp-beacon-crm-donate'),
            'size' => isset($this->props['size']) ? $this->props['size'] : 'md',
            'amount' => isset($this->props['amount']) ? $this->props['amount'] : '',
            'frequency' => isset($this->props['frequency']) ? $this->props['frequency'] : '',
            'currency' => isset($this->props['currency']) ? $this->props['currency'] : '',
            'customParams' => $custom_params,
        ];

        \WBCD\Assets::enqueue_front_base();
        return \WBCD\Render\Donate_Button_Render::render($form_name, $render_args);
    }
}
