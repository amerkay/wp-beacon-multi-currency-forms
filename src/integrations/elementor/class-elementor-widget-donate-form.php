<?php

namespace WBCD\Integrations\Elementor;

// If Elementor isn't loaded, bail before defining the class.
if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Form_Widget extends Abstract_WBCD_Elementor_Widget
{
    public function get_name()
    {
        return 'wbcd_donate_form';
    }

    public function get_title()
    {
        return __('Beacon Donation Form', 'wp-beacon-crm-donate');
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
                'label' => __('Form Settings', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_form_selection_control(__('Choose which donation form to display', 'wp-beacon-crm-donate'));

        $this->end_controls_section();

        // Custom Parameters Section - with custom notice for required params
        $this->add_custom_params_section(__('Add required parameters that must be in the URL. If missing, users will be redirected to include them.', 'wp-beacon-crm-donate'));
    }

    protected function render()
    {
        // Show editor placeholder
        if ($this->render_editor_placeholder(
            __('Beacon Donation Form', 'wp-beacon-crm-donate'),
            '📋',
            [
                __('Form', 'wp-beacon-crm-donate') => $this->get_settings_for_display()['form_name'] ?: __('Default', 'wp-beacon-crm-donate')
            ]
        )) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';
        $custom_params = $this->parse_custom_params_from_settings($settings);

        $render_args = [
            'customParams' => $custom_params
        ];

        $this->enqueue_base_assets();
        \WBCD\Assets::enqueue_donation_form($form_name);
        echo \WBCD\Render\Donate_Form_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
