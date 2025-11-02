<?php

namespace WBCD\Integrations\Elementor;

if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Box_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wbcd_donate_box';
    }
    public function get_title()
    {
        return __('Beacon Donation Box', 'wp-beacon-crm-donate');
    }
    public function get_icon()
    {
        return 'eicon-button';
    }
    public function get_categories()
    {
        return ['general'];
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
                'description' => __('Choose which donation form to use', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();

        // Text Content Section
        $this->start_controls_section(
            'text_section',
            [
                'label' => __('Text Content', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Make a donation', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'subtitle',
            [
                'label' => __('Subtitle', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'notice_text',
            [
                'label' => __('Notice Text', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
                'rows' => 3,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate now →', 'wp-beacon-crm-donate'),
                'placeholder' => __('Donate now →', 'wp-beacon-crm-donate'),
                'description' => __('Text shown on the donate button', 'wp-beacon-crm-donate'),
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
                'raw' => '<p style="font-size: 12px; color: #666;">' . __('Enter custom parameters in URL format: bcn_c_adopted_animals=elephant-123&key2=value2. This will be added to the URL of the full page form on redirect.', 'wp-beacon-crm-donate') . '</p>',
            ]
        );

        $this->add_control(
            'custom_params',
            [
                'label' => __('Parameters', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'param_key',
                        'label' => __('Parameter Name', 'wp-beacon-crm-donate'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g., bcn_c_adopted_animals',
                    ],
                    [
                        'name' => 'param_value',
                        'label' => __('Parameter Value', 'wp-beacon-crm-donate'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g., elephant-123',
                    ],
                ],
                'title_field' => '{{{ param_key }}}',
                'default' => [],
            ]
        );

        $this->end_controls_section();

        // Frequencies Section
        $this->start_controls_section(
            'frequencies_section',
            [
                'label' => __('Frequencies', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'frequencies_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . __('Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.', 'wp-beacon-crm-donate') . '</p>',
            ]
        );

        $this->add_control(
            'frequency_single',
            [
                'label' => __('Show Single Frequency', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-beacon-crm-donate'),
                'label_off' => __('No', 'wp-beacon-crm-donate'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Show single donation frequency option', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'frequency_monthly',
            [
                'label' => __('Show Monthly Frequency', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-beacon-crm-donate'),
                'label_off' => __('No', 'wp-beacon-crm-donate'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Show monthly donation frequency option', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'frequency_annual',
            [
                'label' => __('Show Annual Frequency', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'wp-beacon-crm-donate'),
                'label_off' => __('No', 'wp-beacon-crm-donate'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Show annual donation frequency option', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();

        // Default Preset Amounts Section
        $this->start_controls_section(
            'presets_section',
            [
                'label' => __('Default Preset Amounts', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'presets_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . __('Set default donation amounts per frequency. API settings will override these if configured. Enter numbers separated by commas.', 'wp-beacon-crm-donate') . '</p>',
            ]
        );

        $this->add_control(
            'presets_single',
            [
                'label' => __('Single Preset Amounts', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '10, 20, 30',
                'placeholder' => '10, 20, 30',
                'description' => __('Comma-separated amounts for single donations (e.g., 10, 20, 30)', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'presets_monthly',
            [
                'label' => __('Monthly Preset Amounts', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '5, 10, 15',
                'placeholder' => '5, 10, 15',
                'description' => __('Comma-separated amounts for monthly donations (e.g., 5, 10, 15)', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'presets_annual',
            [
                'label' => __('Annual Preset Amounts', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '50, 100, 200',
                'placeholder' => '50, 100, 200',
                'description' => __('Comma-separated amounts for annual donations (e.g., 50, 100, 200)', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();

        // Colors Section
        $this->start_controls_section(
            'colors_section',
            [
                'label' => __('Colors', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'colors_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . __('Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.', 'wp-beacon-crm-donate') . '</p>',
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'description' => __('Default primary color', 'wp-beacon-crm-donate'),
            ]
        );

        $this->add_control(
            'brand_color',
            [
                'label' => __('Brand Color', 'wp-beacon-crm-donate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'description' => __('Default brand color', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';

        // Parse custom params from repeater
        $custom_params = [];
        if (isset($settings['custom_params']) && is_array($settings['custom_params'])) {
            foreach ($settings['custom_params'] as $param) {
                if (!empty($param['param_key']) && isset($param['param_value'])) {
                    $custom_params[$param['param_key']] = $param['param_value'];
                }
            }
        }

        // Parse allowed frequencies
        $allowed_frequencies = [];
        if (isset($settings['frequency_single']) && $settings['frequency_single'] === 'yes') {
            $allowed_frequencies[] = 'single';
        }
        if (isset($settings['frequency_monthly']) && $settings['frequency_monthly'] === 'yes') {
            $allowed_frequencies[] = 'monthly';
        }
        if (isset($settings['frequency_annual']) && $settings['frequency_annual'] === 'yes') {
            $allowed_frequencies[] = 'annual';
        }
        // Fallback to all if none selected
        if (empty($allowed_frequencies)) {
            $allowed_frequencies = ['single', 'monthly', 'annual'];
        }

        // Parse default presets
        $default_presets = [];
        foreach (['single', 'monthly', 'annual'] as $freq) {
            $preset_key = 'presets_' . $freq;
            if (isset($settings[$preset_key]) && !empty($settings[$preset_key])) {
                $amounts = array_map('trim', explode(',', $settings[$preset_key]));
                $amounts = array_map('floatval', $amounts);
                $amounts = array_filter($amounts, function ($n) {
                    return $n > 0;
                });
                $default_presets[$freq] = array_values($amounts);
            }
        }
        // Set defaults if not specified
        if (empty($default_presets['single'])) $default_presets['single'] = [10, 20, 30];
        if (empty($default_presets['monthly'])) $default_presets['monthly'] = [5, 10, 15];
        if (empty($default_presets['annual'])) $default_presets['annual'] = [50, 100, 200];

        $render_args = [
            'primaryColor' => isset($settings['primary_color']) ? $settings['primary_color'] : '',
            'brandColor' => isset($settings['brand_color']) ? $settings['brand_color'] : '',
            'title' => isset($settings['title']) ? $settings['title'] : __('Make a donation', 'wp-beacon-crm-donate'),
            'subtitle' => isset($settings['subtitle']) ? $settings['subtitle'] : __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
            'noticeText' => isset($settings['notice_text']) ? $settings['notice_text'] : __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
            'buttonText' => isset($settings['button_text']) ? $settings['button_text'] : __('Donate now →', 'wp-beacon-crm-donate'),
            'customParams' => $custom_params,
            'allowedFrequencies' => $allowed_frequencies,
            'defaultPresets' => $default_presets
        ];

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta($form_name);
        echo \WBCD\Render\Donate_CTA_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
