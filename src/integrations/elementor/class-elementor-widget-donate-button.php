<?php

namespace WBCD\Integrations\Elementor;

if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Button_Widget extends Abstract_WBCD_Elementor_Widget
{
    public function get_name()
    {
        return 'wbcd_donate_button';
    }

    public function get_title()
    {
        return __('Beacon Donation Button', 'wp-beacon-crm-donate');
    }

    public function get_icon()
    {
        return 'eicon-button';
    }

    protected function register_controls()
    {
        // Form Settings Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Form Settings', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_form_selection_control(__('Choose which donation form to link to', 'wp-beacon-crm-donate'));

        $this->end_controls_section();

        // Donation Settings Section
        $this->start_controls_section(
            'donation_section',
            [
                'label' => __('Donation Settings', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'amount',
            [
                'label' => __('Amount', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Pre-set donation amount (leave empty for user choice)', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'frequency',
            [
                'label' => __('Frequency', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_frequency_options(),
                'description' => __('Pre-set donation frequency', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'currency',
            [
                'label' => __('Currency', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_currency_options(),
                'description' => __('Pre-set currency (availability depends on selected form)', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();

        // Button Settings Section
        $this->start_controls_section(
            'button_section',
            [
                'label' => __('Button Settings', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => __('Button Text', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'size',
            [
                'label' => __('Button Size', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'md',
                'options' => $this->get_button_size_options(),
            ]
        );

        $this->add_control(
            'color',
            [
                'label' => __('Button Color', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'description' => __('Leave empty for default color', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();

        // Custom Parameters Section
        $this->add_custom_params_section(__('Add custom parameters to append to the donation form URL.', 'wp-beacon-crm-donate'));
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Show editor placeholder
        if ($this->render_editor_placeholder(
            __('Beacon Donation Button', 'wp-beacon-crm-donate'),
            '🔘',
            [
                __('Text', 'wp-beacon-crm-donate') => $settings['text'] ?? __('Donate', 'wp-beacon-crm-donate'),
                __('Size', 'wp-beacon-crm-donate') => $settings['size'] ?? 'md',
                __('Form', 'wp-beacon-crm-donate') => $settings['form_name'] ?: __('Default', 'wp-beacon-crm-donate'),
            ]
        )) {
            return;
        }

        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';
        $custom_params = $this->parse_custom_params_from_settings($settings);

        $render_args = [
            'color' => isset($settings['color']) ? $settings['color'] : '',
            'text' => isset($settings['text']) ? $settings['text'] : __('Donate', 'wp-beacon-crm-donate'),
            'size' => isset($settings['size']) ? $settings['size'] : 'md',
            'amount' => isset($settings['amount']) ? $settings['amount'] : '',
            'frequency' => isset($settings['frequency']) ? $settings['frequency'] : '',
            'currency' => isset($settings['currency']) ? $settings['currency'] : '',
            'customParams' => $custom_params,
        ];

        $this->enqueue_base_assets();
        echo \WBCD\Render\Donate_Button_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
