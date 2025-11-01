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
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $form_name = isset($settings['form_name']) ? $settings['form_name'] : '';

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta($form_name);
        echo \WBCD\Render\Donate_CTA_Render::render($form_name); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
