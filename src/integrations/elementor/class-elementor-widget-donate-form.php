<?php

namespace WBCD\Integrations\Elementor;

// If Elementor isn't loaded, bail before defining the class.
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

if (!defined('ABSPATH'))
    exit;

class Donate_Form_Widget extends Abstract_WBCD_Elementor_Widget
{
    public function get_name()
    {
        return 'wbcd_donate_form';
    }

    public function get_title()
    {
        return __('Beacon Donation Form', 'beacon-multi-currency-forms');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    protected function register_controls()
    {
        // Form Settings Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Form Settings', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_form_selection_control(__('Choose which donation form to display', 'beacon-multi-currency-forms'));

        $this->add_control(
            'default_frequency',
            [
                'label' => __('Default Frequency', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_frequency_options(),
                'description' => __('Set a default donation frequency', 'beacon-multi-currency-forms'),
            ]
        );

        $this->add_control(
            'default_amount',
            [
                'label' => __('Default Amount', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('e.g., 50', 'beacon-multi-currency-forms'),
                'description' => __('Set a default donation amount', 'beacon-multi-currency-forms'),
            ]
        );

        $this->end_controls_section();

        // Custom Parameters Section - with custom notice for required params
        $this->add_custom_params_section(__('Add required parameters that must be in the URL. If missing, users will be redirected to include them.', 'beacon-multi-currency-forms'));
    }

    protected function render()
    {
        // Show editor placeholder
        if (
            $this->render_editor_placeholder(
                __('Beacon Donation Form', 'beacon-multi-currency-forms'),
                '📋',
                [
                    __('Form', 'beacon-multi-currency-forms') => $this->get_settings_for_display()['form_name'] ?: __('Default', 'beacon-multi-currency-forms')
                ]
            )
        ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';
        $custom_params = $this->parse_custom_params_from_settings($settings);

        $render_args = [
            'customParams' => $custom_params,
            'defaultFrequency' => isset($settings['default_frequency']) ? $settings['default_frequency'] : '',
            'defaultAmount' => isset($settings['default_amount']) ? $settings['default_amount'] : ''
        ];

        $this->enqueue_base_assets();
        \WBCD\Assets::enqueue_donation_form($form_name);
        echo \WBCD\Render\Donate_Form_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
