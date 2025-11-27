<?php

namespace BMCF\Integrations\Divi;

if (!class_exists('ET_Builder_Module')) {
    return;
}

if (!defined('ABSPATH'))
    exit;

/**
 * Abstract base class for all BMCF Divi modules.
 * Provides shared functionality to eliminate code duplication.
 */
abstract class Abstract_BMCF_Divi_Module extends \ET_Builder_Module
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
        $forms = \BMCF\Settings::get_forms_for_dropdown();
        $options = ['' => __('Default (First form)', 'beacon-multi-currency-forms')];

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
        $all_currencies = \BMCF\Settings::get_forms_by_currency();
        $currency_options = ['' => __('None (use default)', 'beacon-multi-currency-forms')];

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
            '' => __('None (use default)', 'beacon-multi-currency-forms'),
            'single' => __('Single', 'beacon-multi-currency-forms'),
            'monthly' => __('Monthly', 'beacon-multi-currency-forms'),
            'annual' => __('Annual', 'beacon-multi-currency-forms'),
        ];
    }

    /**
     * Get button size options for select field.
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
     * Parse custom parameters from Divi module props.
     * Handles the URL-encoded format used by Divi text fields.
     * 
     * @return array Parsed custom parameters as key-value pairs.
     */
    protected function parse_custom_params_from_props()
    {
        return \BMCF\Utils\Params_Parser::from_url_encoded($this->props['custom_params'] ?? '');
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

        return \BMCF\Utils\Frequency_Parser::from_toggles($frequency_toggles);
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
        \BMCF\Assets::enqueue_front_base();
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
            $description = __('Choose which donation form to use', 'beacon-multi-currency-forms');
        }

        return [
            'label' => __('Select Form', 'beacon-multi-currency-forms'),
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
            $description = __('Enter custom parameters in URL format: bcn_c_adopted_animal=12345&key2=value2. This will be added to the URL of the donation form.', 'beacon-multi-currency-forms');
        }

        return [
            'label' => __('Custom URL Parameters', 'beacon-multi-currency-forms'),
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
            'single' => __('Single', 'beacon-multi-currency-forms'),
            'monthly' => __('Monthly', 'beacon-multi-currency-forms'),
            'annual' => __('Annual', 'beacon-multi-currency-forms'),
        ];

        $fields = [];

        foreach ($frequencies as $freq => $label) {
            $fields['frequency_' . $freq] = [
                /* translators: %s: Frequency label (One-time, Monthly, or Annual) */
                'label' => sprintf(__('Show %s Frequency', 'beacon-multi-currency-forms'), $label),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => __('Yes', 'beacon-multi-currency-forms'),
                    'off' => __('No', 'beacon-multi-currency-forms'),
                ],
                'default' => 'on',
                /* translators: 1: Frequency label (one-time, monthly, or annual), 2: Notice text */
                'description' => sprintf(__('Show %1$s donation frequency option. %2$s', 'beacon-multi-currency-forms'), strtolower($label), $this->replace_notice),
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

        $fields = [];

        foreach ($presets as $freq => $data) {
            $fields['presets_' . $freq] = [
                /* translators: %s: Frequency label (One-time, Monthly, or Annual) */
                'label' => sprintf(__('%s Preset Amounts', 'beacon-multi-currency-forms'), $data['label']),
                'type' => 'text',
                'default' => $data['default'],
                /* translators: 1: Frequency label (one-time, monthly, or annual), 2: Default preset amounts, 3: Notice text */
                'description' => sprintf(__('Comma-separated amounts for %1$s donations (e.g., %2$s). %3$s', 'beacon-multi-currency-forms'), strtolower($data['label']), $data['default'], $this->replace_notice),
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
        // Use integer 0 for the placeholder to match the default value
        $options = [0 => __('— Select Page —', 'beacon-multi-currency-forms')];

        foreach ($pages as $page) {
            $permalink = get_permalink($page->ID);
            $path = str_replace(home_url(), '', $permalink);
            $label = $page->post_title . ' (' . $path . ')';
            $options[$page->ID] = wp_kses_post($label);
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
            $description = __('Page where donors will be sent to complete the donation (optional). Start typing to search.', 'beacon-multi-currency-forms');
        }

        return [
            'label' => __('Donation Form Page', 'beacon-multi-currency-forms'),
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
                'label' => __('Primary Color', 'beacon-multi-currency-forms'),
                'type' => 'color-alpha',
                'default' => '',
                /* translators: %s: Notice text */
                'description' => sprintf(__('Default primary color. %s', 'beacon-multi-currency-forms'), $this->replace_notice),
            ],
            'brand_color' => [
                'label' => __('Brand Color', 'beacon-multi-currency-forms'),
                'type' => 'color-alpha',
                'default' => '',
                /* translators: %s: Notice text */
                'description' => sprintf(__('Default brand color. %s', 'beacon-multi-currency-forms'), $this->replace_notice),
            ],
        ];
    }
}
