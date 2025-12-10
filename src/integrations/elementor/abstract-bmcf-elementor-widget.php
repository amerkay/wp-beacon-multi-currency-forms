<?php

namespace BMCF\Integrations\Elementor;

if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

if (!defined('ABSPATH'))
    exit;

/**
 * Abstract base class for all BMCF Elementor widgets.
 * Provides shared functionality to eliminate code duplication.
 */
abstract class Abstract_BMCF_Elementor_Widget extends \Elementor\Widget_Base
{
    /**
     * Replacement notice text for backup fields.
     * 
     * @var string
     */
    protected $replace_notice = 'Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.';

    /**
     * Get widget categories.
     * All BMCF widgets belong to the 'general' category.
     * 
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return ['general'];
    }

    /**
     * Show widget in Elementor panel.
     * All BMCF widgets are visible in the panel.
     * 
     * @return bool
     */
    public function show_in_panel()
    {
        return true;
    }

    /**
     * Safely output form HTML with proper escaping.
     * Uses wp_kses with allowed form elements since render methods already escape content.
     * 
     * @param string $html HTML content from render method
     * @return void
     */
    protected function safe_echo_form_html($html)
    {
        $allowed_html = [
            'div' => ['class' => [], 'id' => [], 'style' => [], 'data-*' => [], 'role' => [], 'aria-label' => [], 'hidden' => [], 'aria-hidden' => [], 'aria-expanded' => [], 'aria-controls' => []],
            'header' => ['class' => []],
            'h3' => ['class' => []],
            'h4' => ['class' => []],
            'p' => ['class' => []],
            'span' => ['class' => [], 'aria-hidden' => []],
            'label' => ['for' => [], 'class' => [], 'aria-label' => []],
            'select' => ['id' => [], 'class' => [], 'aria-label' => [], 'name' => []],
            'option' => ['value' => [], 'selected' => []],
            'input' => ['type' => [], 'id' => [], 'class' => [], 'name' => [], 'value' => [], 'placeholder' => [], 'min' => [], 'max' => [], 'step' => [], 'inputmode' => []],
            'button' => ['type' => [], 'class' => [], 'data-*' => [], 'aria-selected' => [], 'aria-expanded' => [], 'aria-controls' => [], 'aria-label' => [], 'disabled' => []],
            'strong' => [],
            'em' => [],
        ];
        echo wp_kses($html, $allowed_html);
    }

    /**
     * Get form dropdown options for Elementor select control.
     * 
     * @return array Form options with empty default.
     */
    protected function get_form_options()
    {
        $forms = \BMCF\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'beacon-multi-currency-forms')];

        foreach ($forms as $name => $label) {
            $options[$name] = $label;
        }

        return $options;
    }

    /**
     * Get currency options for Elementor select control.
     * 
     * @return array Currency options.
     */
    protected function get_currency_options()
    {
        $all_currencies = \BMCF\Settings::get_forms_by_currency();
        $currency_options = ['' => __('None (use default)', 'beacon-multi-currency-forms')];

        foreach (array_keys($all_currencies) as $code) {
            $currency_options[$code] = $code;
        }

        return $currency_options;
    }

    /**
     * Get frequency options for select control.
     * 
     * @return array Frequency options.
     */
    protected function get_frequency_options()
    {
        return [
            '' => __('None (use default)', 'beacon-multi-currency-forms'),
            'single' => __('Single', 'beacon-multi-currency-forms'),
            'monthly' => __('Monthly', 'beacon-multi-currency-forms'),
            'annual' => __('Annual', 'beacon-multi-currency-forms'),
        ];
    }

    /**
     * Get button size options for select control.
     * 
     * @return array Button size options.
     */
    protected function get_button_size_options()
    {
        $sizes = \BMCF\Constants::get_valid_button_sizes();
        $labels = [
            'md' => __('Medium', 'beacon-multi-currency-forms'),
            'lg' => __('Large', 'beacon-multi-currency-forms'),
            'xl' => __('Extra Large', 'beacon-multi-currency-forms'),
        ];

        $options = [];
        foreach ($sizes as $size) {
            $options[$size] = $labels[$size] ?? ucfirst($size);
        }

        return $options;
    }

    /**
     * Parse custom parameters from Elementor settings.
     * Handles the repeater format used by Elementor controls.
     * 
     * @param array $settings Widget settings from get_settings_for_display().
     * @return array Parsed custom parameters as key-value pairs.
     */
    protected function parse_custom_params_from_settings($settings)
    {
        return \BMCF\Utils\Params_Parser::from_array($settings['custom_params'] ?? []);
    }

    /**
     * Parse frequency settings from Elementor toggle controls.
     * 
     * @param array $settings Widget settings from get_settings_for_display().
     * @return array Allowed frequencies.
     */
    protected function parse_frequencies_from_settings($settings)
    {
        $frequency_toggles = [
            'single' => isset($settings['frequency_single']) && $settings['frequency_single'] === 'yes',
            'monthly' => isset($settings['frequency_monthly']) && $settings['frequency_monthly'] === 'yes',
            'annual' => isset($settings['frequency_annual']) && $settings['frequency_annual'] === 'yes',
        ];

        return \BMCF\Utils\Frequency_Parser::from_toggles($frequency_toggles);
    }

    /**
     * Render an editor placeholder for the widget.
     * Shows widget info in Elementor editor instead of the actual widget.
     * 
     * @param string $widget_title The widget title to display.
     * @param string $icon The icon emoji to display.
     * @param array $info_lines Array of label => value pairs to display.
     */
    protected function render_editor_placeholder($widget_title, $icon, $info_lines = [])
    {
        if (!\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return false;
        }

        echo '<div style="padding: 40px; background: #f0f0f1; border: 2px dashed #8c8f94; border-radius: 4px; text-align: center;">';
        echo '<p style="margin: 0 0 10px; font-size: 18px; font-weight: 600; color: #1e1e1e;">' . esc_html($icon) . ' ' . esc_html($widget_title) . '</p>';

        foreach ($info_lines as $label => $value) {
            echo '<p style="margin: 0 0 5px; font-size: 14px; color: #50575e;"><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
        }

        echo '<p style="margin: 10px 0 0; font-size: 12px; color: #8c8f94;">' . esc_html__('Preview on frontend', 'beacon-multi-currency-forms') . '</p>';
        echo '</div>';

        return true;
    }

    /**
     * Enqueue base assets required for all widgets.
     * Child classes should call this before rendering.
     */
    protected function enqueue_base_assets()
    {
        \BMCF\Assets::enqueue_front_base();
    }

    /**
     * Add form selection control to widget.
     * Common control used by all donation widgets.
     * 
     * @param string $description Optional description override.
     */
    protected function add_form_selection_control($description = null)
    {
        if ($description === null) {
            $description = __('Choose which donation form to use', 'beacon-multi-currency-forms');
        }

        $this->add_control(
            'form_name',
            [
                'label' => __('Select Form', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_form_options(),
                'description' => $description,
            ]
        );
    }

    /**
     * Add custom parameters section and control to widget.
     * Common section used by all donation widgets.
     * 
     * @param string $notice_text Optional custom notice text.
     */
    protected function add_custom_params_section($notice_text = null)
    {
        if ($notice_text === null) {
            $notice_text = __('Add custom parameters to include in the donation form URL. Each parameter will be appended as key=value pairs.', 'beacon-multi-currency-forms');
        }

        $this->start_controls_section(
            'params_section',
            [
                'label' => __('Custom URL Parameters', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'params_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . esc_html($notice_text) . '</p>',
            ]
        );

        $this->add_control(
            'custom_params',
            [
                'label' => __('Parameters', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'param_key',
                        'label' => __('Parameter Name', 'beacon-multi-currency-forms'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g., bcn_c_adopted_animal',
                        'default' => '',
                    ],
                    [
                        'name' => 'param_value',
                        'label' => __('Parameter Value', 'beacon-multi-currency-forms'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g., 12345',
                        'default' => '',
                    ],
                ],
                'default' => [
                    [
                        'param_key' => '',
                        'param_value' => '',
                    ],
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Add frequency toggle controls to widget.
     * Common controls for donation box widgets.
     * 
     * @param string $notice The replacement notice to display.
     */
    protected function add_frequency_controls($notice)
    {
        $this->start_controls_section(
            'frequencies_section',
            [
                'label' => __('Frequencies', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'frequencies_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . esc_html($notice) . '</p>',
            ]
        );

        $frequencies = [
            'single' => __('Single', 'beacon-multi-currency-forms'),
            'monthly' => __('Monthly', 'beacon-multi-currency-forms'),
            'annual' => __('Annual', 'beacon-multi-currency-forms'),
        ];

        foreach ($frequencies as $freq => $label) {
            $this->add_control(
                'frequency_' . $freq,
                [
                    /* translators: %s: Frequency label (One-time, Monthly, or Annual) */
                    'label' => sprintf(__('Show %s Frequency', 'beacon-multi-currency-forms'), $label),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Yes', 'beacon-multi-currency-forms'),
                    'label_off' => __('No', 'beacon-multi-currency-forms'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                    /* translators: %s: Frequency label (one-time, monthly, or annual) */
                    'description' => sprintf(__('Show %s donation frequency option', 'beacon-multi-currency-forms'), strtolower($label)),
                ]
            );
        }

        $this->end_controls_section();
    }

    /**
     * Add preset amount controls to widget.
     * Common controls for donation box widgets.
     * 
     * @param string $notice The replacement notice to display.
     */
    protected function add_preset_controls($notice)
    {
        $this->start_controls_section(
            'presets_section',
            [
                'label' => __('Default Preset Amounts', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'presets_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . esc_html($notice) . '</p>',
            ]
        );

        // Get defaults from Constants
        $default_presets = \BMCF\Constants::get_all_presets();

        $presets = [
            'single' => [
                'label' => __('Single', 'beacon-multi-currency-forms'),
                'default' => implode(', ', $default_presets['single'])
            ],
            'monthly' => [
                'label' => __('Monthly', 'beacon-multi-currency-forms'),
                'default' => implode(', ', $default_presets['monthly'])
            ],
            'annual' => [
                'label' => __('Annual', 'beacon-multi-currency-forms'),
                'default' => implode(', ', $default_presets['annual'])
            ],
        ];

        foreach ($presets as $freq => $data) {
            $this->add_control(
                'presets_' . $freq,
                [
                    /* translators: %s: Frequency label (One-time, Monthly, or Annual) */
                    'label' => sprintf(__('%s Preset Amounts', 'beacon-multi-currency-forms'), $data['label']),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => $data['default'],
                    'placeholder' => $data['default'],
                    /* translators: 1: Frequency label (one-time, monthly, or annual), 2: Default preset amounts */
                    'description' => sprintf(__('Comma-separated amounts for %1$s donations (e.g., %2$s)', 'beacon-multi-currency-forms'), strtolower($data['label']), $data['default']),
                ]
            );
        }

        $this->end_controls_section();
    }

    /**
     * Add color controls to widget.
     * Common controls for donation box widgets.
     * 
     * @param string $notice The replacement notice to display.
     */
    protected function add_color_controls($notice)
    {
        $this->start_controls_section(
            'colors_section',
            [
                'label' => __('Colors', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'colors_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . esc_html($notice) . '</p>',
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'description' => __('Default primary color', 'beacon-multi-currency-forms'),
            ]
        );

        $this->add_control(
            'brand_color',
            [
                'label' => __('Brand Color', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'description' => __('Default brand color', 'beacon-multi-currency-forms'),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get page options for Elementor select control.
     * Returns formatted array with page title and permalink.
     * 
     * @return array Page options with empty default.
     */
    protected function get_page_options()
    {
        $pages = get_pages();
        $options = [0 => __('— Select Page —', 'beacon-multi-currency-forms')];

        foreach ($pages as $page) {
            $permalink = get_permalink($page->ID);
            $path = str_replace(home_url(), '', $permalink);
            $options[$page->ID] = $page->post_title . ' (' . $path . ')';
        }

        return $options;
    }

    /**
     * Add target page control to widget.
     * Common control for donation box widgets.
     * Uses SELECT2 type for searchable dropdown.
     */
    protected function add_target_page_control()
    {
        $this->add_control(
            'target_page_id',
            [
                'label' => __('Donation Form Page', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'default' => 0,
                'options' => $this->get_page_options(),
                'description' => __('Page where donors will be sent to complete the donation (optional). Start typing to search.', 'beacon-multi-currency-forms'),
            ]
        );
    }
}
