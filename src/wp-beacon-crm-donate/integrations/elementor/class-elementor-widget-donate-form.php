<?php


namespace WBCD\Integrations\Elementor;

// If Elementor isn't loaded, bail before defining the class.
if (! class_exists('\Elementor\Widget_Base')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Form_Widget extends \Elementor\Widget_Base
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
                'description' => __('Choose which donation form to display', 'wp-beacon-crm-donate'),
            ]
        );

        $this->end_controls_section();

        // Custom Parameters Section
        $this->start_controls_section(
            'params_section',
            [
                'label' => __('Required URL Parameters', 'wp-beacon-crm-donate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'params_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="font-size: 12px; color: #666;">' . __('Add required parameters that must be in the URL. If missing, users will be redirected to include them.', 'wp-beacon-crm-donate') . '</p>',
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
                        'placeholder' => 'e.g., campaign',
                    ],
                    [
                        'name' => 'param_value',
                        'label' => __('Parameter Value', 'wp-beacon-crm-donate'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'placeholder' => 'e.g., spring2025',
                    ],
                ],
                'title_field' => '{{{ param_key }}}',
                'default' => [],
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

        $render_args = [
            'customParams' => $custom_params
        ];

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_form($form_name);
        echo \WBCD\Render\Donate_Form_Render::render($form_name, $render_args); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}
