<?php

namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Box_Module extends Abstract_WBCD_Divi_Module
{
    public $slug       = 'wbcd_divi_donate_box';
    public $vb_support = 'off';
    public $name       = '';

    function init()
    {
        $this->name = esc_html__('Beacon Donation Box', 'wp-beacon-crm-donate');
    }

    public function get_fields()
    {
        return array_merge(
            [
                'form_name' => $this->get_form_selection_field(),
                'target_page_id' => $this->get_target_page_field(),
                'title' => [
                    'label' => __('Title', 'wp-beacon-crm-donate'),
                    'type' => 'text',
                    'default' => __('Make a donation', 'wp-beacon-crm-donate'),
                ],
                'subtitle' => [
                    'label' => __('Subtitle', 'wp-beacon-crm-donate'),
                    'type' => 'text',
                    'default' => __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
                ],
                'notice_text' => [
                    'label' => __('Notice Text', 'wp-beacon-crm-donate'),
                    'type' => 'text',
                    'default' => __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
                ],
                'button_text' => [
                    'label' => __('Button Text', 'wp-beacon-crm-donate'),
                    'type' => 'text',
                    'default' => __('Donate now →', 'wp-beacon-crm-donate'),
                    'description' => __('Text shown on the donate button', 'wp-beacon-crm-donate'),
                ],
                'custom_params' => $this->get_custom_params_field(__('Enter custom parameters in URL format: bcn_c_adopted_animal=12345&key2=value2. This will be added to the URL of the full page form on redirect.', 'wp-beacon-crm-donate')),
            ],
            $this->get_frequency_fields(),
            $this->get_preset_fields(),
            $this->get_color_fields()
        );
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        $form_name = $this->get_form_name_from_props();
        $custom_params = $this->parse_custom_params_from_props();
        $allowed_frequencies = $this->parse_frequencies_from_props();
        $default_presets = \WBCD\Utils\Preset_Parser::parse_all_presets($this->props);

        // Get target page URL
        $target_page_url = '';
        $target_page_id = isset($this->props['target_page_id']) ? absint($this->props['target_page_id']) : 0;
        if ($target_page_id > 0) {
            $permalink = get_permalink($target_page_id);
            if ($permalink) {
                $target_page_url = $permalink;
            }
        }

        $render_args = [
            'primaryColor' => isset($this->props['primary_color']) ? $this->props['primary_color'] : '',
            'brandColor' => isset($this->props['brand_color']) ? $this->props['brand_color'] : '',
            'title' => isset($this->props['title']) ? $this->props['title'] : __('Make a donation', 'wp-beacon-crm-donate'),
            'subtitle' => isset($this->props['subtitle']) ? $this->props['subtitle'] : __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
            'noticeText' => isset($this->props['notice_text']) ? $this->props['notice_text'] : __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
            'buttonText' => isset($this->props['button_text']) ? $this->props['button_text'] : __('Donate now →', 'wp-beacon-crm-donate'),
            'customParams' => $custom_params,
            'allowedFrequencies' => $allowed_frequencies,
            'defaultPresets' => $default_presets,
            'targetPageUrl' => $target_page_url
        ];

        $this->enqueue_base_assets();
        return \WBCD\Render\Donate_Box_Render::render($form_name, $render_args);
    }
}
