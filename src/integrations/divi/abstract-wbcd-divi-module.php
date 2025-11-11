<?php

namespace WBCD\Integrations\Divi;

if (!class_exists('ET_Builder_Module')) {
    return;
}

if (!defined('ABSPATH'))
    exit;

/**
 * Abstract base class for all WBCD Divi modules.
 * Provides shared functionality to eliminate code duplication.
 */
abstract class Abstract_WBCD_Divi_Module extends \ET_Builder_Module
{
    /**
     * Replacement notice text for backup fields.
     * 
     * @var string
     */
    protected $replace_notice = 'Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.';

    /**
     * Get form dropdown options for Divi select field.
     * 
     * @return array Form options with empty default.
     */
    protected function get_form_options()
    {
        $forms = \WBCD\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'wp-beacon-crm-donate')];

        foreach ($forms as $name => $label) {
            $options[$name] = $label;
        }

        return $options;
    }

    /**
     * Get currency options for Divi select field.
     * 
     * @return array Currency options.
     */
    protected function get_currency_options()
    {
        $all_currencies = \WBCD\Settings::get_forms_by_currency();
        $currency_options = ['' => __('None (use default)', 'wp-beacon-crm-donate')];

        foreach (array_keys($all_currencies) as $code) {
            $currency_options[$code] = $code;
        }

        return $currency_options;
    }

    /**
     * Get frequency options for select field.
     * 
     * @return array Frequency options.
     */
    protected function get_frequency_options()
    {
        return [
            '' => __('None (use default)', 'wp-beacon-crm-donate'),
            'single' => __('Single', 'wp-beacon-crm-donate'),
            'monthly' => __('Monthly', 'wp-beacon-crm-donate'),
            'annual' => __('Annual', 'wp-beacon-crm-donate'),
        ];
    }

    /**
     * Get button size options for select field.
     * 
     * @return array Button size options.
     */
    protected function get_button_size_options()
    {
        $sizes = \WBCD\Constants::get_valid_button_sizes();
        $labels = [
            'md' => __('Medium', 'wp-beacon-crm-donate'),
            'lg' => __('Large', 'wp-beacon-crm-donate'),
            'xl' => __('Extra Large', 'wp-beacon-crm-donate'),
        ];

        $options = [];
        foreach ($sizes as $size) {
            $options[$size] = $labels[$size] ?? ucfirst($size);
        }

        return $options;
    }

    /**
     * Parse custom parameters from Divi module props.
     * Handles the URL-encoded format used by Divi text fields.
     * 
     * @return array Parsed custom parameters as key-value pairs.
     */
    protected function parse_custom_params_from_props()
    {
        return \WBCD\Utils\Params_Parser::from_url_encoded($this->props['custom_params'] ?? '');
    }

    /**
     * Parse frequency settings from Divi toggle controls.
     * 
     * @return array Allowed frequencies.
     */
    protected function parse_frequencies_from_props()
    {
        $frequency_toggles = [
            'single' => isset($this->props['frequency_single']) && $this->props['frequency_single'] === 'on',
            'monthly' => isset($this->props['frequency_monthly']) && $this->props['frequency_monthly'] === 'on',
            'annual' => isset($this->props['frequency_annual']) && $this->props['frequency_annual'] === 'on',
        ];

        return \WBCD\Utils\Frequency_Parser::from_toggles($frequency_toggles);
    }

    /**
     * Get form name from props.
     * 
     * @return string Form name.
     */
    protected function get_form_name_from_props()
    {
        return isset($this->props['form_name']) ? $this->props['form_name'] : '';
    }

    /**
     * Enqueue base assets required for all modules.
     * Child classes should call this before rendering.
     */
    protected function enqueue_base_assets()
    {
        \WBCD\Assets::enqueue_front_base();
    }

    /**
     * Get form selection field definition.
     * Common field used by all donation modules.
     * 
     * @param string|null $description Optional description override.
     * @return array Field definition array.
     */
    protected function get_form_selection_field($description = null)
    {
        if ($description === null) {
            $description = __('Choose which donation form to use', 'wp-beacon-crm-donate');
        }

        return [
            'label' => __('Select Form', 'wp-beacon-crm-donate'),
            'type' => 'select',
            'options' => $this->get_form_options(),
            'default' => '',
            'description' => $description,
        ];
    }

    /**
     * Get custom parameters field definition.
     * Common field used by all donation modules.
     * 
     * @param string|null $description Optional description override.
     * @return array Field definition array.
     */
    protected function get_custom_params_field($description = null)
    {
        if ($description === null) {
            $description = __('Enter custom parameters in URL format: bcn_c_adopted_animal=12345&key2=value2. This will be added to the URL of the donation form.', 'wp-beacon-crm-donate');
        }

        return [
            'label' => __('Custom URL Parameters', 'wp-beacon-crm-donate'),
            'type' => 'text',
            'default' => '',
            'description' => $description,
        ];
    }

    /**
     * Get frequency toggle field definitions.
     * Common fields for donation box modules.
     * 
     * @return array Field definitions.
     */
    protected function get_frequency_fields()
    {
        $frequencies = [
            'single' => __('Single', 'wp-beacon-crm-donate'),
            'monthly' => __('Monthly', 'wp-beacon-crm-donate'),
            'annual' => __('Annual', 'wp-beacon-crm-donate'),
        ];

        $fields = [];

        foreach ($frequencies as $freq => $label) {
            $fields['frequency_' . $freq] = [
                'label' => sprintf(__('Show %s Frequency', 'wp-beacon-crm-donate'), $label),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => __('Yes', 'wp-beacon-crm-donate'),
                    'off' => __('No', 'wp-beacon-crm-donate'),
                ],
                'default' => 'on',
                'description' => sprintf(__('Show %s donation frequency option. %s', 'wp-beacon-crm-donate'), strtolower($label), $this->replace_notice),
            ];
        }

        return $fields;
    }

    /**
     * Get preset amount field definitions.
     * Common fields for donation box modules.
     * 
     * @return array Field definitions.
     */
    protected function get_preset_fields()
    {
        // Get defaults from Constants
        $default_presets = \WBCD\Constants::get_all_presets();

        $presets = [
            'single' => [
                'label' => __('Single', 'wp-beacon-crm-donate'),
                'default' => implode(', ', $default_presets['single'])
            ],
            'monthly' => [
                'label' => __('Monthly', 'wp-beacon-crm-donate'),
                'default' => implode(', ', $default_presets['monthly'])
            ],
            'annual' => [
                'label' => __('Annual', 'wp-beacon-crm-donate'),
                'default' => implode(', ', $default_presets['annual'])
            ],
        ];

        $fields = [];

        foreach ($presets as $freq => $data) {
            $fields['presets_' . $freq] = [
                'label' => sprintf(__('%s Preset Amounts', 'wp-beacon-crm-donate'), $data['label']),
                'type' => 'text',
                'default' => $data['default'],
                'description' => sprintf(__('Comma-separated amounts for %s donations (e.g., %s). %s', 'wp-beacon-crm-donate'), strtolower($data['label']), $data['default'], $this->replace_notice),
            ];
        }

        return $fields;
    }

    /**
     * Get page options for Divi select field.
     * Returns formatted array with page title and permalink.
     * 
     * @return array Page options.
     */
    protected function get_page_options()
    {
        $pages = get_pages();
        // use an empty-string key for the placeholder and make all keys strings
        $options = ['' => __('— Select Page —', 'wp-beacon-crm-donate')];

        foreach ($pages as $page) {
            $permalink = get_permalink($page->ID);
            $path = str_replace(home_url(), '', $permalink);
            // cast the ID to string to guarantee string keys
            $key = (string) $page->ID;
            $label = $page->post_title . ' (' . $path . ')';
            // optional: sanitize label for safety
            $options[$key] = wp_kses_post($label);
        }

        return $options;
    }


    /**
     * Get target page field definition.
     * Common field for donation box modules.
     * Uses 'select' type with 'search' => true for searchable dropdown.
     * 
     * @param string|null $description Optional description override.
     * @return array Field definition array.
     */
    protected function get_target_page_field($description = null)
    {
        if ($description === null) {
            $description = __('Page where donors will be sent to complete the donation (optional). Start typing to search.', 'wp-beacon-crm-donate');
        }

        return [
            'label' => __('Donation Form Page', 'wp-beacon-crm-donate'),
            'type' => 'select',
            'options' => $this->get_page_options(),
            'default' => 0,
            'description' => $description,
            'search' => true, // Enable search functionality in Divi
        ];
    }

    /**
     * Get color field definitions.
     * Common fields for donation box modules.
     * 
     * @return array Field definitions.
     */
    protected function get_color_fields()
    {
        return [
            'primary_color' => [
                'label' => __('Primary Color', 'wp-beacon-crm-donate'),
                'type' => 'color-alpha',
                'default' => '',
                'description' => sprintf(__('Default primary color. %s', 'wp-beacon-crm-donate'), $this->replace_notice),
            ],
            'brand_color' => [
                'label' => __('Brand Color', 'wp-beacon-crm-donate'),
                'type' => 'color-alpha',
                'default' => '',
                'description' => sprintf(__('Default brand color. %s', 'wp-beacon-crm-donate'), $this->replace_notice),
            ],
        ];
    }
}
