<?php

namespace WBCD;

if (!defined('ABSPATH'))
    exit;

class Settings
{
    // Option names
    const OPTION_BEACON_ACCOUNT = 'wbcd_beacon_account';
    const OPTION_FORMS = 'wbcd_forms';
    const OPTION_LOAD_BEACON_GLOBALLY = 'wbcd_load_beacon_globally';
    const OPTION_TRACK_UTM = 'wbcd_track_utm';
    const OPTION_UTM_PARAMS = 'wbcd_utm_params';

    // Text domain
    const TEXT_DOMAIN = 'wp-beacon-multi-currency-forms';

    // Cached data
    private static $currencies_data = null;

    /**
     * Get default form structure
     * 
     * @return array Default form array structure
     */
    private static function get_default_form()
    {
        return [
            'name' => 'Default Donation Form',
            'currencies' => [],
            'default_currency' => ''
        ];
    }

    /**
     * Add admin menu page
     */
    public static function add_menu()
    {
        add_options_page(
            __('Beacon Donate', self::TEXT_DOMAIN),
            __('Beacon Donate', self::TEXT_DOMAIN),
            'manage_options',
            'wbcd-settings',
            [__CLASS__, 'render_page']
        );
    }

    /**
     * Enqueue admin assets for settings page
     * 
     * @param string $hook Current admin page hook
     */
    public static function enqueue_admin_assets($hook)
    {
        // Only enqueue on our settings page
        if ($hook !== 'settings_page_wbcd-settings') {
            return;
        }

        // Enqueue admin CSS
        wp_enqueue_style(
            'wbcd-admin-settings',
            WPBMCF_URL . 'admin/css/settings-admin.css',
            [],
            WPBMCF_VERSION
        );

        // Enqueue admin JS with jQuery dependency
        wp_enqueue_script(
            'wbcd-admin-settings',
            WPBMCF_URL . 'admin/js/settings-admin.js',
            ['jquery'],
            WPBMCF_VERSION,
            true
        );

        // Localize script with data and translations
        $forms = get_option(self::OPTION_FORMS, []);
        $currencies_data = self::load_currencies_data();

        wp_localize_script(
            'wbcd-admin-settings',
            'wbcdAdminSettings',
            [
                'formCount' => count($forms),
                'currencies' => $currencies_data,
                'i18n' => self::get_localized_strings(),
                'validation' => Form_Validator::get_js_validation_rules(),
                'validationMessages' => Form_Validator::get_js_validation_messages(),
            ],
        );
    }

    /**
     * Get all localized strings for JavaScript
     * 
     * @return array Localized strings
     */
    private static function get_localized_strings()
    {
        return [
            'selectCurrency' => __('Please select a currency.', self::TEXT_DOMAIN),
            'enterFormId' => __('Please enter a Beacon form ID.', self::TEXT_DOMAIN),
            'formIdLengthError' => __('Beacon form ID must be between 6 and 12 characters long.', self::TEXT_DOMAIN),
            'formIdAlphanumericError' => __('Beacon form ID must contain only letters and numbers (no spaces or special characters).', self::TEXT_DOMAIN),
            'confirmRemoveCurrency' => __('Remove this currency?', self::TEXT_DOMAIN),
            'confirmRemoveForm' => __('Remove this entire form and all its currencies?', self::TEXT_DOMAIN),
            'default' => __('Default', self::TEXT_DOMAIN),
            'currency' => __('Currency', self::TEXT_DOMAIN),
            'beaconFormId' => __('Beacon Form ID', self::TEXT_DOMAIN),
            'action' => __('Action', self::TEXT_DOMAIN),
            'remove' => __('Remove', self::TEXT_DOMAIN),
            'noCurrencies' => __('No currencies added yet.', self::TEXT_DOMAIN),
            'form' => __('Form', self::TEXT_DOMAIN),
            'formName' => __('Form Name:', self::TEXT_DOMAIN),
            'formNamePlaceholder' => __('e.g., General Donations', self::TEXT_DOMAIN),
            'supportedCurrencies' => __('Supported Currencies:', self::TEXT_DOMAIN),
            'addCurrency' => __('Add Currency:', self::TEXT_DOMAIN),
            'selectCurrencyOption' => __('-- Select a currency --', self::TEXT_DOMAIN),
            'beaconFormIdPlaceholder' => __('Beacon form ID', self::TEXT_DOMAIN),
            'addCurrencyBtn' => __('Add Currency', self::TEXT_DOMAIN),
            'removeForm' => __('Remove This Form', self::TEXT_DOMAIN),
            'setAsDefault' => __('Set as default currency', self::TEXT_DOMAIN),
            'defaultCurrencyDesc' => __('Select a default currency by clicking the radio button. This currency will be used when geo-detection fails or detects an unsupported currency.', self::TEXT_DOMAIN),
            'currenciesRequired' => __('At least one currency with a form ID must be added.', self::TEXT_DOMAIN),
            'validationFailed' => __('Please fix the following errors before saving:', self::TEXT_DOMAIN),
            'addMoreCurrencies' => __('Add more currencies', self::TEXT_DOMAIN),
            'hideCurrencyForm' => __('Hide', self::TEXT_DOMAIN),
            'loadBeaconGlobally' => __('Load Beacon JavaScript globally', self::TEXT_DOMAIN),
            'utmTracking' => __('UTM Parameter Tracking', self::TEXT_DOMAIN),
            'enableUtmTracking' => __('Enable tracking and passing UTM parameters to donation forms', self::TEXT_DOMAIN),
        ];
    }

    /**
     * Register settings and fields
     */
    public static function register()
    {
        // New structure: array of forms, each with name, currency=>formId mappings, and default_currency
        $defaults = [self::get_default_form()];
        $default_utm_params = self::get_default_utm_params();

        add_option(self::OPTION_BEACON_ACCOUNT, '');
        add_option(self::OPTION_FORMS, $defaults);
        add_option(self::OPTION_LOAD_BEACON_GLOBALLY, false);
        add_option(self::OPTION_TRACK_UTM, false);
        add_option(self::OPTION_UTM_PARAMS, $default_utm_params);

        register_setting('wbcd_group', self::OPTION_BEACON_ACCOUNT, [
            'type' => 'string',
            'sanitize_callback' => [__CLASS__, 'sanitize_account'],
            'default' => '',
        ]);

        register_setting('wbcd_group', self::OPTION_FORMS, [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_forms'],
            'default' => $defaults,
        ]);

        register_setting('wbcd_group', self::OPTION_LOAD_BEACON_GLOBALLY, [
            'type' => 'boolean',
            'default' => false,
        ]);

        register_setting('wbcd_group', self::OPTION_TRACK_UTM, [
            'type' => 'boolean',
            'default' => false,
        ]);

        register_setting('wbcd_group', self::OPTION_UTM_PARAMS, [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_utm_params'],
            'default' => $default_utm_params,
        ]);

        add_settings_section(
            'wbcd_section_main',
            __('Donation Settings', self::TEXT_DOMAIN),
            function () {
                echo '<p>' . esc_html__('Configure your Beacon account name and donation forms. Each form can have multiple currencies, a default currency (used when geo-detection fails or detects an unsupported currency), and a dedicated page for the full donation form.', self::TEXT_DOMAIN) . '</p>';
            },
            'wbcd-settings'
        );

        add_settings_field(
            'wbcd_field_beacon_account',
            __('Beacon Account Name', self::TEXT_DOMAIN),
            [__CLASS__, 'field_beacon_account'],
            'wbcd-settings',
            'wbcd_section_main'
        );

        add_settings_field(
            'wbcd_field_forms',
            __('Donation Forms', self::TEXT_DOMAIN),
            [__CLASS__, 'field_forms'],
            'wbcd-settings',
            'wbcd_section_main'
        );

        add_settings_field(
            'wbcd_field_load_beacon_globally',
            __('Beacon JavaScript', self::TEXT_DOMAIN),
            [__CLASS__, 'field_load_beacon_globally'],
            'wbcd-settings',
            'wbcd_section_main'
        );

        add_settings_field(
            'wbcd_field_track_utm',
            __('UTM Parameter Tracking', self::TEXT_DOMAIN),
            [__CLASS__, 'field_track_utm'],
            'wbcd-settings',
            'wbcd_section_main'
        );
    }

    /**
     * Sanitize and validate account name
     * 
     * @param string $input User input
     * @return string Sanitized value
     */
    public static function sanitize_account($input)
    {
        // Trim and convert to lowercase
        $value = strtolower(trim($input));

        // Validate using Form_Validator
        $validation = Form_Validator::validate_account_name($value);

        if (!$validation['valid']) {
            add_settings_error(
                self::OPTION_BEACON_ACCOUNT,
                'invalid_beacon_account',
                $validation['message'],
                'error'
            );
            // Return the previous valid value
            return get_option(self::OPTION_BEACON_ACCOUNT, '');
        }

        return $value;
    }

    /**
     * Get a property from form array with default fallback
     * 
     * @param array $form Form data array
     * @param string $key Property key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Property value or default
     */
    private static function get_form_property($form, $key, $default = '')
    {
        return isset($form[$key]) ? $form[$key] : $default;
    }

    /**
     * Sanitize and validate forms array
     * 
     * @param array|mixed $input User input
     * @return array Sanitized forms array
     */
    public static function sanitize_forms($input)
    {
        return Form_Sanitizer::sanitize_forms($input, self::get_default_form());
    }

    /**
     * Sanitize and validate UTM parameters
     * 
     * @param array|mixed $input User input
     * @return array Sanitized UTM params array
     */
    public static function sanitize_utm_params($input)
    {
        if (!is_array($input)) {
            return self::get_default_utm_params();
        }

        $sanitized = [];
        $utm_fields = self::get_utm_field_names();
        $has_error = false;

        foreach ($utm_fields as $utm_field) {
            if (isset($input[$utm_field]) && is_array($input[$utm_field])) {
                $payment = sanitize_text_field($input[$utm_field]['payment'] ?? '');
                $subscription = sanitize_text_field($input[$utm_field]['subscription'] ?? '');

                // Validate that parameter names start with 'bcn_'
                if (!empty($payment) && strpos($payment, 'bcn_') !== 0) {
                    add_settings_error(
                        self::OPTION_UTM_PARAMS,
                        'invalid_payment_param_' . $utm_field,
                        sprintf(__('Payment parameter for %s must start with "bcn_".', self::TEXT_DOMAIN), $utm_field),
                        'error'
                    );
                    $has_error = true;
                    $payment = '';
                }

                if (!empty($subscription) && strpos($subscription, 'bcn_') !== 0) {
                    add_settings_error(
                        self::OPTION_UTM_PARAMS,
                        'invalid_subscription_param_' . $utm_field,
                        sprintf(__('Subscription parameter for %s must start with "bcn_".', self::TEXT_DOMAIN), $utm_field),
                        'error'
                    );
                    $has_error = true;
                    $subscription = '';
                }

                $sanitized[$utm_field] = [
                    'payment' => $payment,
                    'subscription' => $subscription,
                ];
            } else {
                // Use defaults if not provided
                $defaults = self::get_default_utm_params();
                $sanitized[$utm_field] = $defaults[$utm_field];
            }
        }

        // If there were errors, return the previous valid values
        if ($has_error) {
            return get_option(self::OPTION_UTM_PARAMS, self::get_default_utm_params());
        }

        return $sanitized;
    }

    /**
     * Get default UTM parameter mappings
     * 
     * @return array Default UTM params
     */
    private static function get_default_utm_params()
    {
        $defaults = [];
        foreach (self::get_utm_field_names() as $field) {
            $defaults[$field] = [
                'payment' => 'bcn_pay_c_' . $field,
                'subscription' => 'bcn_sub_c_' . $field,
            ];
        }
        return $defaults;
    }

    /**
     * Get list of supported UTM field names
     * 
     * @return array List of UTM field names
     */
    public static function get_utm_field_names()
    {
        return ['utm_source', 'utm_medium', 'utm_campaign'];
    }

    /**
     * Render the beacon account field
     */
    public static function field_beacon_account()
    {
        $value = get_option(self::OPTION_BEACON_ACCOUNT, '');
        Settings_Renderer::render_beacon_account_field($value);
    }

    /**
     * Render the donation forms field
     */
    public static function field_forms()
    {
        $forms = get_option(self::OPTION_FORMS, []);
        $currencies_data = self::load_currencies_data();
        Settings_Renderer::render_forms_field($forms, $currencies_data);
    }

    /**
     * Render the load Beacon globally field
     */
    public static function field_load_beacon_globally()
    {
        $value = get_option(self::OPTION_LOAD_BEACON_GLOBALLY, true);
        Settings_Renderer::render_load_beacon_globally_field($value);
    }

    /**
     * Render the UTM tracking field
     */
    public static function field_track_utm()
    {
        $value = get_option(self::OPTION_TRACK_UTM, true);
        $utm_params = get_option(self::OPTION_UTM_PARAMS, self::get_default_utm_params());
        Settings_Renderer::render_utm_tracking_field($value, $utm_params);
    }

    /**
     * Load and cache currencies data from JSON file
     * 
     * @return array Currency data array
     */
    private static function load_currencies_data()
    {
        if (self::$currencies_data !== null) {
            return self::$currencies_data;
        }

        $json_path = WPBMCF_PATH . 'assets/currencies-iso-4217.json';
        if (!file_exists($json_path)) {
            self::$currencies_data = [];
            return self::$currencies_data;
        }

        $json_content = file_get_contents($json_path);
        $data = json_decode($json_content, true);

        self::$currencies_data = is_array($data) ? $data : [];
        return self::$currencies_data;
    }

    /**
     * Render the settings page
     */
    public static function render_page()
    {
        if (!current_user_can('manage_options'))
            return;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Beacon Donate', self::TEXT_DOMAIN); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wbcd_group');
                do_settings_sections('wbcd-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display admin notice after settings update
     */
    public static function settings_updated_notice()
    {
        // Check if our transient exists
        if (get_transient('wbcd_settings_updated')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p><?php esc_html_e('Settings saved successfully. Remember to clear your cache for changes to take effect.', self::TEXT_DOMAIN); ?>
                </p>
            </div>
            <?php
            // Delete transient after displaying
            delete_transient('wbcd_settings_updated');
        }
    }

    /**
     * Set transient after successful settings update
     */
    public static function on_settings_updated()
    {
        // Check if we're updating our settings
        if (isset($_POST['option_page']) && $_POST['option_page'] === 'wbcd_group') {
            set_transient('wbcd_settings_updated', true, 60); // 60 seconds
        }
    }

    // ========================================
    // Helper Methods for Other Classes
    // ========================================

    /**
     * Get beacon account name
     * 
     * @return string Account name
     */
    public static function get_beacon_account()
    {
        return get_option(self::OPTION_BEACON_ACCOUNT, '');
    }

    /**
     * Get all forms with their currency mappings
     * 
     * @return array Array of forms, each with 'name' and 'currencies' (code => form_id)
     */
    public static function get_all_forms()
    {
        $forms = get_option(self::OPTION_FORMS, []);

        // Filter out forms with no currencies
        return array_filter($forms, function ($form) {
            return !empty($form['currencies']);
        });
    }

    /**
     * Get a specific form by name
     * 
     * @param string $form_name The name of the form to retrieve
     * @return array|null Form data or null if not found
     */
    public static function get_form_by_name($form_name)
    {
        $forms = self::get_all_forms();

        foreach ($forms as $form) {
            if (self::get_form_property($form, 'name') === $form_name) {
                return $form;
            }
        }

        return null;
    }

    /**
     * Get forms as name => label array for dropdowns
     * 
     * @return array [form_name => form_label]
     */
    public static function get_forms_for_dropdown()
    {
        $forms = self::get_all_forms();
        $options = [];

        foreach ($forms as $form) {
            $name = self::get_form_property($form, 'name');
            if ($name) {
                $currency_count = count(self::get_form_property($form, 'currencies', []));
                $label = sprintf(
                    '%s (%d %s)',
                    $name,
                    $currency_count,
                    _n('currency', 'currencies', $currency_count, self::TEXT_DOMAIN)
                );
                $options[$name] = $label;
            }
        }

        return $options;
    }

    /**
     * Get flattened currency => form_id mapping
     * 
     * @param string $form_name Optional form name to get currencies for specific form
     * @return array [currency_code => beacon_form_id]
     */
    public static function get_forms_by_currency($form_name = '')
    {
        if (!empty($form_name)) {
            // Get currencies for specific form
            $form = self::get_form_by_name($form_name);
            if ($form) {
                $currencies = self::get_form_property($form, 'currencies', []);
                return is_array($currencies) ? array_filter($currencies) : [];
            }
            return [];
        }

        // Get all currencies from all forms (original behavior)
        $forms = self::get_all_forms();
        $currency_map = [];

        foreach ($forms as $form) {
            $currencies = self::get_form_property($form, 'currencies', []);
            if (is_array($currencies)) {
                foreach ($currencies as $code => $form_id) {
                    if (!empty($form_id)) {
                        $currency_map[$code] = $form_id;
                    }
                }
            }
        }

        return $currency_map;
    }

    /**
     * Get currency symbols from ISO 4217 data
     * 
     * @return array [currency_code => symbol]
     */
    public static function get_currency_symbols()
    {
        static $symbols = null;

        if ($symbols === null) {
            $currencies_data = self::load_currencies_data();
            $symbols = [];

            foreach ($currencies_data as $code => $info) {
                $symbols[$code] = isset($info['symbol']) ? $info['symbol'] : $code;
            }
        }

        return $symbols;
    }

    /**
     * Get available currencies (those with form IDs configured)
     * 
     * @return array Array of currency codes
     */
    public static function get_available_currencies()
    {
        return array_keys(self::get_forms_by_currency());
    }

    /**
     * Get the default currency for a specific form
     * 
     * @param string $form_name The form name to get the default currency for
     * @return string The default currency code, or empty string if not set
     */
    public static function get_default_currency($form_name = '')
    {
        if (!empty($form_name)) {
            $form = self::get_form_by_name($form_name);
            if ($form) {
                return self::get_form_property($form, 'default_currency');
            }
        }

        // If no form name specified, use the first form's default currency
        $forms = self::get_all_forms();
        if (!empty($forms)) {
            $first_form = reset($forms);
            return self::get_form_property($first_form, 'default_currency');
        }

        return '';
    }

    /**
     * Check if Beacon SDK should be loaded globally
     * 
     * @return bool Whether to load Beacon SDK globally
     */
    public static function get_load_beacon_globally()
    {
        return (bool) get_option(self::OPTION_LOAD_BEACON_GLOBALLY, true);
    }

    /**
     * Check if UTM tracking is enabled
     * 
     * @return bool Whether UTM tracking is enabled
     */
    public static function get_utm_tracking_enabled()
    {
        return (bool) get_option(self::OPTION_TRACK_UTM, true);
    }

    /**
     * Get UTM parameter mappings
     * 
     * @return array UTM parameter mappings with payment and subscription fields
     */
    public static function get_utm_params()
    {
        return get_option(self::OPTION_UTM_PARAMS, self::get_default_utm_params());
    }
}
