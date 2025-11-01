<?php


namespace WBCD\Integrations\Divi;

if (! class_exists('ET_Builder_Module')) {
    return;
}

if (! defined('ABSPATH')) exit;

class Donate_Box_Module extends \ET_Builder_Module
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
        $forms = \WBCD\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'wp-beacon-crm-donate')];

        foreach ($forms as $name => $label) {
            $options[$name] = $label;
        }

        return [
            'form_name' => [
                'label' => __('Select Form', 'wp-beacon-crm-donate'),
                'type' => 'select',
                'options' => $options,
                'default' => '',
                'description' => __('Choose which donation form to use', 'wp-beacon-crm-donate'),
            ],
            'primary_color' => [
                'label' => __('Primary Color', 'wp-beacon-crm-donate'),
                'type' => 'color-alpha',
                'default' => '',
                'description' => __('Override the default primary color', 'wp-beacon-crm-donate'),
            ],
            'brand_color' => [
                'label' => __('Brand Color', 'wp-beacon-crm-donate'),
                'type' => 'color-alpha',
                'default' => '',
                'description' => __('Override the default brand color', 'wp-beacon-crm-donate'),
            ],
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
                'type' => 'textarea',
                'default' => __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
            ],
            'custom_params' => [
                'label' => __('Custom URL Parameters', 'wp-beacon-crm-donate'),
                'type' => 'textarea',
                'default' => '',
                'description' => __('Enter custom parameters in URL format: bcn_c_adopted_animals=elephant-123&key2=value2', 'wp-beacon-crm-donate'),
            ],
            'frequency_single' => [
                'label' => __('Show Single Frequency', 'wp-beacon-crm-donate'),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => __('Yes', 'wp-beacon-crm-donate'),
                    'off' => __('No', 'wp-beacon-crm-donate'),
                ],
                'default' => 'on',
                'description' => __('Show single donation frequency option', 'wp-beacon-crm-donate'),
            ],
            'frequency_monthly' => [
                'label' => __('Show Monthly Frequency', 'wp-beacon-crm-donate'),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => __('Yes', 'wp-beacon-crm-donate'),
                    'off' => __('No', 'wp-beacon-crm-donate'),
                ],
                'default' => 'on',
                'description' => __('Show monthly donation frequency option', 'wp-beacon-crm-donate'),
            ],
            'frequency_annual' => [
                'label' => __('Show Annual Frequency', 'wp-beacon-crm-donate'),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => __('Yes', 'wp-beacon-crm-donate'),
                    'off' => __('No', 'wp-beacon-crm-donate'),
                ],
                'default' => 'on',
                'description' => __('Show annual donation frequency option', 'wp-beacon-crm-donate'),
            ],
            'presets_single' => [
                'label' => __('Single Preset Amounts', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '10, 20, 30',
                'description' => __('Comma-separated amounts for single donations (e.g., 10, 20, 30)', 'wp-beacon-crm-donate'),
            ],
            'presets_monthly' => [
                'label' => __('Monthly Preset Amounts', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '5, 10, 15',
                'description' => __('Comma-separated amounts for monthly donations (e.g., 5, 10, 15)', 'wp-beacon-crm-donate'),
            ],
            'presets_annual' => [
                'label' => __('Annual Preset Amounts', 'wp-beacon-crm-donate'),
                'type' => 'text',
                'default' => '50, 100, 200',
                'description' => __('Comma-separated amounts for annual donations (e.g., 50, 100, 200)', 'wp-beacon-crm-donate'),
            ],
        ];
    }

    public function render($attrs = [], $content = null, $render_slug = '')
    {
        $form_name = isset($this->props['form_name']) ? $this->props['form_name'] : '';

        // Parse custom params from URL-encoded format
        $custom_params = [];
        if (!empty($this->props['custom_params'])) {
            // Parse URL-encoded format
            parse_str($this->props['custom_params'], $custom_params);
        }

        // Parse allowed frequencies
        $allowed_frequencies = [];
        if (isset($this->props['frequency_single']) && $this->props['frequency_single'] === 'on') {
            $allowed_frequencies[] = 'single';
        }
        if (isset($this->props['frequency_monthly']) && $this->props['frequency_monthly'] === 'on') {
            $allowed_frequencies[] = 'monthly';
        }
        if (isset($this->props['frequency_annual']) && $this->props['frequency_annual'] === 'on') {
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
            if (isset($this->props[$preset_key]) && !empty($this->props[$preset_key])) {
                $amounts = array_map('trim', explode(',', $this->props[$preset_key]));
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
            'primaryColor' => isset($this->props['primary_color']) ? $this->props['primary_color'] : '',
            'brandColor' => isset($this->props['brand_color']) ? $this->props['brand_color'] : '',
            'title' => isset($this->props['title']) ? $this->props['title'] : __('Make a donation', 'wp-beacon-crm-donate'),
            'subtitle' => isset($this->props['subtitle']) ? $this->props['subtitle'] : __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
            'noticeText' => isset($this->props['notice_text']) ? $this->props['notice_text'] : __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
            'customParams' => $custom_params,
            'allowedFrequencies' => $allowed_frequencies,
            'defaultPresets' => $default_presets
        ];

        \WBCD\Assets::enqueue_front_base();
        \WBCD\Assets::enqueue_donation_cta($form_name);
        return \WBCD\Render\Donate_CTA_Render::render($form_name, $render_args);
    }
}
