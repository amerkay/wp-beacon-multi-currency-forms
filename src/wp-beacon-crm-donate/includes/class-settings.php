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

    // Validation patterns
    const PATTERN_ACCOUNT_NAME = '/^[a-z0-9_-]+$/';
    const PATTERN_FORM_ID = '/^[a-zA-Z0-9]{6,12}$/';

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
            'targetPageDesc' => __('Page where the CTA box will send donors (hosts the full donation form).', self::TEXT_DOMAIN),
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
     * Add a validation error for forms
     * 
     * @param string $code Error code
     * @param string $message Error message
     */
    private static function add_form_validation_error($code, $message)
    {
        add_settings_error(
            self::OPTION_FORMS,
            $code,
            $message,
            'error'
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

        // Validate: must be a slug (lowercase letters, numbers, hyphens, underscores only)
        if (!empty($value) && !preg_match(self::PATTERN_ACCOUNT_NAME, $value)) {
            add_settings_error(
                self::OPTION_BEACON_ACCOUNT,
                'invalid_beacon_account',
                __('Beacon Account Name must be lowercase and contain only letters, numbers, hyphens, and underscores (no spaces).', self::TEXT_DOMAIN),
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
        if (!is_array($input)) {
            return [self::get_default_form()];
        }

        $sanitized = [];
        $has_validation_errors = false;

        foreach ($input as $form_index => $form) {
            if (!is_array($form)) continue;

            $form_name = self::get_form_property($form, 'name');
            $form_name = sanitize_text_field($form_name);
            if (empty($form_name)) {
                $form_name = 'Unnamed Form';
            }

            $currencies = [];
            $form_used_currencies = []; // Track currencies within THIS form only to prevent duplicates

            if (isset($form['currencies']) && is_array($form['currencies'])) {
                foreach ($form['currencies'] as $code => $form_id) {
                    $code = strtoupper(sanitize_text_field($code));
                    $form_id = sanitize_text_field($form_id);

                    if (empty($code) || empty($form_id)) continue;

                    // Validate form ID: alphanumeric only, 6-12 characters
                    if (!preg_match(self::PATTERN_FORM_ID, $form_id)) {
                        self::add_form_validation_error(
                            'invalid_form_id_' . $code,
                            sprintf(
                                __('Invalid Beacon form ID "%s" for currency %s in form "%s". Form IDs must be 6-12 alphanumeric characters only (no spaces or special characters).', self::TEXT_DOMAIN),
                                $form_id,
                                $code,
                                $form_name
                            )
                        );
                        continue; // Skip this invalid form ID
                    }

                    // Check for duplicate currency within the same form only
                    if (isset($form_used_currencies[$code])) {
                        self::add_form_validation_error(
                            'duplicate_currency_in_form',
                            sprintf(
                                __('Currency %s appears multiple times in form "%s". Each currency can only be used once per form.', self::TEXT_DOMAIN),
                                $code,
                                $form_name
                            )
                        );
                        continue;
                    }

                    $currencies[$code] = $form_id;
                    $form_used_currencies[$code] = true;
                }
            }

            // Sanitize default currency
            $default_currency = self::get_form_property($form, 'default_currency');
            $default_currency = strtoupper(sanitize_text_field($default_currency));

            // If default currency is set but not in currencies list, clear it
            if (!empty($default_currency) && !isset($currencies[$default_currency])) {
                $default_currency = '';
            }

            // If no default currency but currencies exist, use first one
            if (empty($default_currency) && !empty($currencies)) {
                $default_currency = array_key_first($currencies);
            }

            // Sanitize target page ID
            $target_page_id = absint(self::get_form_property($form, 'target_page_id', 0));

            // Server-side validation: check if target page is set
            if ($target_page_id === 0) {
                self::add_form_validation_error(
                    'missing_target_page_' . $form_index,
                    sprintf(
                        __('Form "%s": A donation form page must be selected.', self::TEXT_DOMAIN),
                        $form_name
                    )
                );
                $has_validation_errors = true;
            }

            // Server-side validation: check if at least one currency is added
            if (empty($currencies)) {
                self::add_form_validation_error(
                    'missing_currencies_' . $form_index,
                    sprintf(
                        __('Form "%s": At least one currency with a form ID must be added.', self::TEXT_DOMAIN),
                        $form_name
                    )
                );
                $has_validation_errors = true;
            }

            $sanitized[] = [
                'name' => $form_name,
                'currencies' => $currencies,
                'default_currency' => $default_currency,
                'target_page_id' => $target_page_id
            ];
        }

        // Ensure at least one form exists
        if (empty($sanitized)) {
            $sanitized[] = self::get_default_form();
        }

        // If validation errors occurred, return the previous valid data
        if ($has_validation_errors) {
            return get_option(self::OPTION_FORMS, [self::get_default_form()]);
        }

        return $sanitized;
    }

    /**
     * Format currency display string
     * 
     * @param string $code Currency code (e.g., 'USD')
     * @param array|null $info Currency info array with 'name' and 'symbol'
     * @return string Formatted string (e.g., 'USD - US Dollar ($)')
     */
    private static function format_currency_display($code, $info = null)
    {
        if ($info && isset($info['name']) && isset($info['symbol'])) {
            return sprintf('%s - %s (%s)', $code, $info['name'], $info['symbol']);
        }
        return $code;
    }

    /**
     * Render the beacon account field
     */
    public static function field_beacon_account()
    {
        $value = get_option(self::OPTION_BEACON_ACCOUNT, '');
        echo '<input type="text" id="' . self::OPTION_BEACON_ACCOUNT . '" name="' . self::OPTION_BEACON_ACCOUNT . '" value="' . esc_attr($value) . '" class="regular-text" required pattern="[a-z0-9_-]+" title="' . esc_attr__('Must be lowercase with only letters, numbers, hyphens, and underscores (no spaces)', self::TEXT_DOMAIN) . '" />';
        echo '<p class="description">' . esc_html__('Enter your BeaconCRM account name (e.g., "yourorg"). Must be lowercase with only letters, numbers, hyphens, and underscores. No spaces allowed.', self::TEXT_DOMAIN) . '</p>';
        echo '<p class="description"><strong>' . esc_html__('How to find your account name:', self::TEXT_DOMAIN) . '</strong></p>';
        echo '<ol class="description wbcd-instructions-list">';
        echo '<li>' . esc_html__('Navigate to any of your forms on BeaconCRM\'s interface.', self::TEXT_DOMAIN) . '</li>';
        echo '<li>' . esc_html__('Click it, then click "Embed".', self::TEXT_DOMAIN) . '</li>';
        echo '<li>' . wp_kses_post(__('The form code should look like <code>&lt;div class="beacon-form" data-account="yourorg" data-form="000000"&gt;&lt;/div&gt;</code>. In this example, the account name is <code>yourorg</code>.', self::TEXT_DOMAIN)) . '</li>';
        echo '</ol>';
    }

    /**
     * Render currency table for a form
     * 
     * @param int $form_index Form index
     * @param array $form_currencies Array of currency code => form_id mappings
     * @param string $default_currency Default currency code
     * @param array $currencies_data All available currencies data
     */
    private static function render_currency_table($form_index, $form_currencies, $default_currency, $currencies_data)
    {
        echo '<table class="widefat wbcd-currencies-table"><thead><tr>';
        echo '<th class="wbcd-col-default">' . esc_html__('Default', self::TEXT_DOMAIN) . '</th>';
        echo '<th>' . esc_html__('Currency', self::TEXT_DOMAIN) . '</th>';
        echo '<th>' . esc_html__('Beacon Form ID', self::TEXT_DOMAIN) . '</th>';
        echo '<th class="wbcd-col-action">' . esc_html__('Action', self::TEXT_DOMAIN) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($form_currencies as $code => $form_id) {
            $currency_info = isset($currencies_data[$code]) ? $currencies_data[$code] : null;
            $display_name = $currency_info
                ? sprintf('%s (%s) %s', $code, $currency_info['name'], $currency_info['symbol'])
                : $code;
            $is_default = ($default_currency === $code);

            echo '<tr>';
            echo '<td>';
            echo '<input type="radio" name="wbcd_forms[' . $form_index . '][default_currency]" value="' . esc_attr($code) . '" ' . checked($is_default, true, false) . ' title="' . esc_attr__('Set as default currency', self::TEXT_DOMAIN) . '" />';
            echo '</td>';
            echo '<td><strong>' . esc_html($display_name) . '</strong></td>';
            echo '<td><input type="text" name="wbcd_forms[' . $form_index . '][currencies][' . esc_attr($code) . ']" value="' . esc_attr($form_id) . '" class="regular-text" placeholder="' . esc_attr__('Beacon form ID', self::TEXT_DOMAIN) . '" /></td>';
            echo '<td><button type="button" class="button wbcd-remove-currency" data-form="' . $form_index . '" data-currency="' . esc_attr($code) . '">' . esc_html__('Remove', self::TEXT_DOMAIN) . '</button></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<p class="description">' . esc_html__('Select a default currency by clicking the radio button. This currency will be used when geo-detection fails or detects an unsupported currency.', self::TEXT_DOMAIN) . '</p>';
    }

    /**
     * Render add currency section for a form
     * 
     * @param int $form_index Form index
     * @param array $form_currencies Current form currencies
     * @param array $currencies_data All available currencies data
     */
    private static function render_add_currency_section($form_index, $form_currencies, $currencies_data)
    {
        echo '<div class="wbcd-add-currency">';
        echo '<label for="wbcd_new_currency_' . $form_index . '"><strong>' . esc_html__('Add Currency:', self::TEXT_DOMAIN) . '</strong></label><br>';
        echo '<select id="wbcd_new_currency_' . $form_index . '" class="wbcd-currency-select" data-form-index="' . $form_index . '">';
        echo '<option value="">' . esc_html__('-- Select a currency --', self::TEXT_DOMAIN) . '</option>';

        // Get currencies already used in THIS form only
        $current_form_currencies = array_keys($form_currencies);

        foreach ($currencies_data as $code => $info) {
            // Only show currencies not already used in THIS specific form
            if (!in_array($code, $current_form_currencies)) {
                echo '<option value="' . esc_attr($code) . '">' . esc_html(self::format_currency_display($code, $info)) . '</option>';
            }
        }

        echo '</select> ';
        echo '<input type="text" id="wbcd_new_currency_id_' . $form_index . '" class="wbcd-currency-id" placeholder="' . esc_attr__('Beacon form ID', self::TEXT_DOMAIN) . '" /> ';
        echo '<button type="button" class="button wbcd-add-currency-btn" data-form-index="' . $form_index . '">' . esc_html__('Add Currency', self::TEXT_DOMAIN) . '</button>';
        echo '</div>';
    }

    /**
     * Render a single form item
     * 
     * @param int $form_index Form index
     * @param array $form Form data
     * @param int $total_forms Total number of forms
     * @param array $currencies_data All available currencies data
     */
    private static function render_form_item($form_index, $form, $total_forms, $currencies_data)
    {
        $form_name = esc_attr(self::get_form_property($form, 'name'));
        $form_currencies = self::get_form_property($form, 'currencies', []);
        $default_currency = self::get_form_property($form, 'default_currency');
        $target_page_id = (int) self::get_form_property($form, 'target_page_id', 0);

        echo '<div class="wbcd-form-item">';
        echo '<h3>' . esc_html__('Form', self::TEXT_DOMAIN) . ' #' . ($form_index + 1) . '</h3>';

        // Form name
        echo '<p>';
        echo '<label for="wbcd_form_name_' . $form_index . '"><strong>' . esc_html__('Form Name:', self::TEXT_DOMAIN) . '</strong></label><br>';
        echo '<input type="text" id="wbcd_form_name_' . $form_index . '" name="wbcd_forms[' . $form_index . '][name]" value="' . $form_name . '" class="regular-text" required placeholder="' . esc_attr__('e.g., General Donations', self::TEXT_DOMAIN) . '" />';
        echo '</p>';

        // Target page selection
        echo '<p>';
        echo '<label for="wbcd_form_target_page_' . $form_index . '"><strong>' . esc_html__('Donation Form Page:', self::TEXT_DOMAIN) . '</strong></label><br>';
        wp_dropdown_pages([
            'name' => 'wbcd_forms[' . $form_index . '][target_page_id]',
            'id' => 'wbcd_form_target_page_' . $form_index,
            'selected' => $target_page_id,
            'show_option_none' => __('— Select Page —', self::TEXT_DOMAIN),
            'option_none_value' => '0',
        ]);
        echo '<br><span class="description">' . esc_html__('Page where the CTA box will send donors (hosts the full donation form).', self::TEXT_DOMAIN) . '</span>';
        echo '</p>';

        // Currencies section
        echo '<div class="wbcd-currencies-section">';
        echo '<h4>' . esc_html__('Supported Currencies:', self::TEXT_DOMAIN) . '</h4>';

        if (!empty($form_currencies)) {
            self::render_currency_table($form_index, $form_currencies, $default_currency, $currencies_data);
        } else {
            echo '<p><em>' . esc_html__('No currencies added yet.', self::TEXT_DOMAIN) . '</em></p>';
        }

        self::render_add_currency_section($form_index, $form_currencies, $currencies_data);

        echo '</div>'; // .wbcd-currencies-section

        // Remove form button
        if ($total_forms > 1) {
            echo '<p class="wbcd-remove-form-wrapper">';
            echo '<button type="button" class="button button-link-delete wbcd-remove-form" data-form-index="' . $form_index . '">' . esc_html__('Remove This Form', self::TEXT_DOMAIN) . '</button>';
            echo '</p>';
        }

        echo '</div>'; // .wbcd-form-item
    }

    /**
     * Render the donation forms field
     */
    public static function field_forms()
    {
        $forms = get_option(self::OPTION_FORMS, []);
        $currencies_data = self::load_currencies_data();

        echo '<div id="wbcd-forms-container">';

        foreach ($forms as $form_index => $form) {
            self::render_form_item($form_index, $form, count($forms), $currencies_data);
        }

        echo '</div>'; // #wbcd-forms-container

        // Add new form button
        echo '<p><button type="button" id="wbcd-add-form" class="button">' . esc_html__('+ Add Another Form', self::TEXT_DOMAIN) . '</button></p>';

        echo '<p class="description">' . esc_html__('Create donation forms and assign Beacon CRM form IDs for each currency. Each form can have multiple currencies, a default currency (used as fallback), and a dedicated target page. Multiple forms can use the same currency (useful for different campaigns or regions). Each currency can only appear once per form. Forms without any currencies will not appear on the frontend.', self::TEXT_DOMAIN) . '</p>';
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
}
