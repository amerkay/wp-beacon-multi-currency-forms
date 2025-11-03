<?php

namespace WBCD\Integrations\Elementor;

if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Box_Widget extends Abstract_WBCD_Elementor_Widget
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

        $this->add_form_selection_control();

        $this->add_target_page_control();

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
        if ($this->render_editor_placeholder(
            __('Beacon Donation Box', 'wp-beacon-crm-donate'),
            '📦',
            [
                __('Form', 'wp-beacon-crm-donate') => $settings['form_name'] ?: __('Default', 'wp-beacon-crm-donate'),
                __('Title', 'wp-beacon-crm-donate') => $settings['title'] ?? __('Make a donation', 'wp-beacon-crm-donate'),
            ]
        )) {
            return;
        }

        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';
        $custom_params = $this->parse_custom_params_from_settings($settings);
        $allowed_frequencies = $this->parse_frequencies_from_settings($settings);
        $default_presets = \WBCD\Utils\Preset_Parser::parse_all_presets($settings);

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
            'primaryColor' => isset($settings['primary_color']) ? $settings['primary_color'] : '',
            'brandColor' => isset($settings['brand_color']) ? $settings['brand_color'] : '',
            'title' => isset($settings['title']) ? $settings['title'] : __('Make a donation', 'wp-beacon-crm-donate'),
            'subtitle' => isset($settings['subtitle']) ? $settings['subtitle'] : __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
            'noticeText' => isset($settings['notice_text']) ? $settings['notice_text'] : __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
            'buttonText' => isset($settings['button_text']) ? $settings['button_text'] : __('Donate now →', 'wp-beacon-crm-donate'),
            'customParams' => $custom_params,
            'allowedFrequencies' => $allowed_frequencies,
            'defaultPresets' => $default_presets,
            'targetPageUrl' => $target_page_url
        ];

        $this->enqueue_base_assets();
        echo \WBCD\Render\Donate_Box_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
