<?php

namespace WBCD\Integrations\Elementor;

if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Button_Widget extends \Elementor\Widget_Base
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

    public function get_categories()
    {
        return ['general'];
    }

    public function show_in_panel()
    {
        return true;
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

        $forms = \WBCD\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'wp-beacon-crm-donate')];

        foreach ($forms as $name => $label) {
            $options[$name] = $label;
        }

        $this->add_control(
            'form_name',
            [
                'label' => __('Select Form', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $options,
                'description' => __('Choose which donation form to link to', 'wp-beacon-crm-donate'),
            ]
        );

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
                'options' => [
                    '' => __('None (use default)', 'wp-beacon-crm-donate'),
                    'single' => __('Single', 'wp-beacon-crm-donate'),
                    'monthly' => __('Monthly', 'wp-beacon-crm-donate'),
                    'annual' => __('Annual', 'wp-beacon-crm-donate'),
                ],
                'description' => __('Pre-set donation frequency', 'wp-beacon-crm-donate'),
            ]
        );

        // Get currencies for dynamic currency field
        $all_currencies = \WBCD\Settings::get_forms_by_currency();
        $currency_options = ['' => __('None (use default)', 'wp-beacon-crm-donate')];
        foreach (array_keys($all_currencies) as $code) {
            $currency_options[$code] = $code;
        }

        $this->add_control(
            'currency',
            [
                'label' => __('Currency', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $currency_options,
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
                'options' => [
                    'md' => __('Medium', 'wp-beacon-crm-donate'),
                    'lg' => __('Large', 'wp-beacon-crm-donate'),
                    'xl' => __('Extra Large', 'wp-beacon-crm-donate'),
                ],
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
        $this->start_controls_section(
            'params_section',
            [
                'label' => __('Custom URL Parameters', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'params_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('Add custom parameters to append to the donation form URL.', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'custom_params',
            [
                'label' => __('Parameters', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'key',
                        'label' => __('Parameter Key', 'wp-beacon-crm-donate'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'bcn_c_adopted_animal',
                    ],
                    [
                        'name' => 'value',
                        'label' => __('Parameter Value', 'wp-beacon-crm-donate'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => '12345',
                    ],
                ],
                'title_field' => '{{{ key }}} = {{{ value }}}',
                'default' => [],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        // Don't render in editor mode - just show a placeholder
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $settings = $this->get_settings_for_display();
            $form_name = isset($settings['form_name']) ? $settings['form_name'] : __('Default', 'wp-beacon-crm-donate');
            $text = isset($settings['text']) ? $settings['text'] : __('Donate', 'wp-beacon-crm-donate');
            $size = isset($settings['size']) ? $settings['size'] : 'md';

            echo '<div style="padding: 20px; background: #f0f0f1; border: 2px dashed #8c8f94; border-radius: 4px; text-align: center;">';
            echo '<p style="margin: 0 0 10px; font-size: 16px; font-weight: 600; color: #1e1e1e;">🔘 ' . esc_html__('Beacon Donation Button', 'wp-beacon-crm-donate') . '</p>';
            echo '<p style="margin: 0 0 5px; font-size: 14px; color: #50575e;"><strong>' . esc_html__('Text:', 'wp-beacon-crm-donate') . '</strong> ' . esc_html($text) . '</p>';
            echo '<p style="margin: 0 0 5px; font-size: 14px; color: #50575e;"><strong>' . esc_html__('Size:', 'wp-beacon-crm-donate') . '</strong> ' . esc_html($size) . '</p>';
            echo '<p style="margin: 0 0 5px; font-size: 14px; color: #50575e;"><strong>' . esc_html__('Form:', 'wp-beacon-crm-donate') . '</strong> ' . esc_html($form_name) . '</p>';
            echo '<p style="margin: 10px 0 0; font-size: 12px; color: #8c8f94;">' . esc_html__('Preview on frontend', 'wp-beacon-crm-donate') . '</p>';
            echo '</div>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';

        // Parse custom params from repeater
        $custom_params = [];
        if (isset($settings['custom_params']) && is_array($settings['custom_params'])) {
            foreach ($settings['custom_params'] as $param) {
                if (isset($param['key']) && !empty($param['key']) && isset($param['value'])) {
                    $custom_params[$param['key']] = $param['value'];
                }
            }
        }

        $render_args = [
            'color' => isset($settings['color']) ? $settings['color'] : '',
            'text' => isset($settings['text']) ? $settings['text'] : __('Donate', 'wp-beacon-crm-donate'),
            'size' => isset($settings['size']) ? $settings['size'] : 'md',
            'amount' => isset($settings['amount']) ? $settings['amount'] : '',
            'frequency' => isset($settings['frequency']) ? $settings['frequency'] : '',
            'currency' => isset($settings['currency']) ? $settings['currency'] : '',
            'customParams' => $custom_params,
        ];

        \WBCD\Assets::enqueue_front_base();
        echo \WBCD\Render\Donate_Button_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
