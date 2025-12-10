<?php

namespace BMCF\Integrations\Elementor;

if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

if (!defined('ABSPATH'))
    exit;

class Donate_Box_Widget extends Abstract_BMCF_Elementor_Widget
{
    public function get_name()
    {
        return 'bmcf_donate_box';
    }

    public function get_title()
    {
        return __('Beacon Donation Box', 'beacon-multi-currency-forms');
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
                'label' => __('Form Settings', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_form_selection_control();

        $this->add_target_page_control();

        $this->end_controls_section();

        // Text Content Section
        $this->start_controls_section(
            'text_section',
            [
                'label' => __('Text Content', 'beacon-multi-currency-forms'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Make a donation', 'beacon-multi-currency-forms'),
            ]
        );

        $this->add_control(
            'subtitle',
            [
                'label' => __('Subtitle', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Pick your currency, frequency, and amount', 'beacon-multi-currency-forms'),
            ]
        );

        $this->add_control(
            'notice_text',
            [
                'label' => __('Notice Text', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __("You'll be taken to our secure donation form to complete your gift.", 'beacon-multi-currency-forms'),
                'rows' => 3,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'beacon-multi-currency-forms'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate now →', 'beacon-multi-currency-forms'),
                'placeholder' => __('Donate now →', 'beacon-multi-currency-forms'),
                'description' => __('Text shown on the donate button', 'beacon-multi-currency-forms'),
            ]
        );

        $this->end_controls_section();

        // Custom Parameters Section
        $this->add_custom_params_section();

        // Frequencies Section
        $this->add_frequency_controls($this->replace_notice);

        // Presets Section
        $this->add_preset_controls($this->replace_notice);

        // Colors Section
        $this->add_color_controls($this->replace_notice);
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Show editor placeholder
        if (
            $this->render_editor_placeholder(
                __('Beacon Donation Box', 'beacon-multi-currency-forms'),
                '📦',
                [
                    __('Form', 'beacon-multi-currency-forms') => $settings['form_name'] ?: __('Default', 'beacon-multi-currency-forms'),
                    __('Title', 'beacon-multi-currency-forms') => $settings['title'] ?? __('Make a donation', 'beacon-multi-currency-forms'),
                ]
            )
        ) {
            return;
        }

        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';
        $custom_params = $this->parse_custom_params_from_settings($settings);
        $allowed_frequencies = $this->parse_frequencies_from_settings($settings);
        $default_presets = \BMCF\Utils\Preset_Parser::parse_all_presets($settings);

        // Get target page URL
        $target_page_url = '';
        $target_page_id = isset($settings['target_page_id']) ? absint($settings['target_page_id']) : 0;
        if ($target_page_id > 0) {
            $permalink = get_permalink($target_page_id);
            if ($permalink) {
                $target_page_url = $permalink;
            }
        }

        $render_args = [
            'primaryColor' => isset($settings['primary_color']) ? sanitize_hex_color($settings['primary_color']) : '',
            'brandColor' => isset($settings['brand_color']) ? sanitize_hex_color($settings['brand_color']) : '',
            'title' => isset($settings['title']) ? sanitize_text_field($settings['title']) : __('Make a donation', 'beacon-multi-currency-forms'),
            'subtitle' => isset($settings['subtitle']) ? sanitize_text_field($settings['subtitle']) : __('Pick your currency, frequency, and amount', 'beacon-multi-currency-forms'),
            'noticeText' => isset($settings['notice_text']) ? sanitize_textarea_field($settings['notice_text']) : __("You'll be taken to our secure donation form to complete your gift.", 'beacon-multi-currency-forms'),
            'buttonText' => isset($settings['button_text']) ? sanitize_text_field($settings['button_text']) : __('Donate now →', 'beacon-multi-currency-forms'),
            'customParams' => $custom_params,
            'allowedFrequencies' => $allowed_frequencies,
            'defaultPresets' => $default_presets,
            'targetPageUrl' => esc_url_raw($target_page_url)
        ];

        $this->enqueue_base_assets();
        $this->safe_echo_form_html(\BMCF\Render\Donate_Box_Render::render($form_name, $render_args));
    }
}
