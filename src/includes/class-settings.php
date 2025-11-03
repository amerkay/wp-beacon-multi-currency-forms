<?php

namespace WBCD;

if (! defined('ABSPATH')) exit;

class Settings
{
    // Option names
    const OPTION_BEACON_ACCOUNT = 'wbcd_beacon_account';
    const OPTION_FORMS = 'wbcd_forms';

    // Text domain
    const TEXT_DOMAIN = 'wp-beacon-crm-donate';

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
            'default_currency' => '',
            'target_page_id' => 0
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
            WPBCD_URL . 'admin/css/settings-admin.css',
            [],
            WPBCD_VERSION
        );

        // Enqueue admin JS with jQuery dependency
        wp_enqueue_script(
            'wbcd-admin-settings',
            WPBCD_URL . 'admin/js/settings-admin.js',
            ['jquery'],
            WPBCD_VERSION,
            true
        );

        // Localize script with data and translations
        $forms = get_option(self::OPTION_FORMS, []);
        $currencies_data = self::load_currencies_data();

        // Get all pages for dropdown
        $pages = get_pages();
        $pages_data = [];
        foreach ($pages as $page) {
            $pages_data[] = [
                'id' => $page->ID,
                'title' => $page->post_title,
            ];
        }

        wp_localize_script('wbcd-admin-settings', 'wbcdAdminSettings', [
            'formCount' => count($forms),
            'currencies' => $currencies_data,
            'pages' => $pages_data,
            'i18n' => self::get_localized_strings(),
            'validation' => Form_Validator::get_js_validation_rules(),
            'validationMessages' => Form_Validator::get_js_validation_messages(),
            'ajax' => [
                'url' => admin_url('admin-ajax.php'),
                'createPageNonce' => wp_create_nonce('wbcd_create_page'),
            ],
        ]);
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
            'donationFormPage' => __('Donation Form Page:', self::TEXT_DOMAIN),
            'targetPageDesc' => __('Page where the donate box will send donors (hosts the full donation form).', self::TEXT_DOMAIN),
            'supportedCurrencies' => __('Supported Currencies:', self::TEXT_DOMAIN),
            'addCurrency' => __('Add Currency:', self::TEXT_DOMAIN),
            'selectCurrencyOption' => __('-- Select a currency --', self::TEXT_DOMAIN),
            'beaconFormIdPlaceholder' => __('Beacon form ID', self::TEXT_DOMAIN),
            'addCurrencyBtn' => __('Add Currency', self::TEXT_DOMAIN),
            'removeForm' => __('Remove This Form', self::TEXT_DOMAIN),
            'selectPage' => __('— Select Page —', self::TEXT_DOMAIN),
            'setAsDefault' => __('Set as default currency', self::TEXT_DOMAIN),
            'defaultCurrencyDesc' => __('Select a default currency by clicking the radio button. This currency will be used when geo-detection fails or detects an unsupported currency.', self::TEXT_DOMAIN),
            'targetPageRequired' => __('A donation form page must be selected.', self::TEXT_DOMAIN),
            'currenciesRequired' => __('At least one currency with a form ID must be added.', self::TEXT_DOMAIN),
            'validationFailed' => __('Please fix the following errors before saving:', self::TEXT_DOMAIN),
            'addMoreCurrencies' => __('Add more currencies', self::TEXT_DOMAIN),
            'hideCurrencyForm' => __('Hide', self::TEXT_DOMAIN),
            'createPagePrompt' => __('Enter the title for the new page:', self::TEXT_DOMAIN),
            'createNewPageOption' => __('+ Create New Page...', self::TEXT_DOMAIN),
            'createPageError' => __('Failed to create page. Please try again.', self::TEXT_DOMAIN),
        ];
    }

    /**
     * Register settings and fields
     */
    public static function register()
    {
        // New structure: array of forms, each with name, currency=>formId mappings, default_currency, and target_page_id
        $defaults = [self::get_default_form()];

        add_option(self::OPTION_BEACON_ACCOUNT, '');
        add_option(self::OPTION_FORMS, $defaults);

        register_setting('wbcd_group', self::OPTION_BEACON_ACCOUNT, [
            'type'              => 'string',
            'sanitize_callback' => [__CLASS__, 'sanitize_account'],
            'default'           => '',
        ]);

        register_setting('wbcd_group', self::OPTION_FORMS, [
            'type'              => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_forms'],
            'default'           => $defaults,
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
     * Load and cache currencies data from JSON file
     * 
     * @return array Currency data array
     */
    private static function load_currencies_data()
    {
        if (self::$currencies_data !== null) {
            return self::$currencies_data;
        }

        $json_path = WPBCD_PATH . 'assets/currencies-iso-4217.json';
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
        if (! current_user_can('manage_options')) return;
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
     * Get the target page URL for a specific form
     * 
     * @param string $form_name The form name to get the target page URL for
     * @return string The target page URL
     */
    public static function get_target_page_url($form_name = '')
    {
        if (!empty($form_name)) {
            $form = self::get_form_by_name($form_name);
            if ($form) {
                $target_page_id = self::get_form_property($form, 'target_page_id', 0);
                if ($target_page_id > 0) {
                    $url = get_permalink($target_page_id);
                    if ($url) return $url;
                }
            }
        }

        // If no form name specified, or form not found, use the first form with a target page
        $forms = self::get_all_forms();
        foreach ($forms as $form) {
            $target_page_id = self::get_form_property($form, 'target_page_id', 0);
            if ($target_page_id > 0) {
                $url = get_permalink($target_page_id);
                if ($url) return $url;
            }
        }

        // Reasonable fallback if not configured: look for /donation-form/; else home.
        $maybe = home_url('/donation-form/');
        return $maybe ?: home_url('/');
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
     * AJAX handler to create a new page
     * Called when user selects "Create New Page..." option
     */
    public static function ajax_create_page()
    {
        // Security check
        check_ajax_referer('wbcd_create_page', 'nonce');

        // Permission check
        if (!current_user_can('manage_options') || !current_user_can('publish_pages')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', self::TEXT_DOMAIN)]);
        }

        // Get and sanitize page title
        $page_title = isset($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : '';

        if (empty($page_title)) {
            wp_send_json_error(['message' => __('Page title is required.', self::TEXT_DOMAIN)]);
        }

        // Create the page
        $page_id = wp_insert_post([
            'post_title'   => $page_title,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
        ]);

        if (is_wp_error($page_id)) {
            wp_send_json_error(['message' => $page_id->get_error_message()]);
        }

        // Return success with page ID and title
        wp_send_json_success([
            'page_id' => $page_id,
            'page_title' => $page_title,
        ]);
    }
}
