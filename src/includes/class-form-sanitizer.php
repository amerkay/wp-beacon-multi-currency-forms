<?php

namespace WBCD;

if (! defined('ABSPATH')) exit;

/**
 * Form Sanitizer Class
 * 
 * Handles all form data sanitization with focused, single-responsibility methods.
 * Breaks down the monolithic sanitize_forms() method into maintainable pieces.
 */
class Form_Sanitizer
{
    // Text domain
    const TEXT_DOMAIN = 'wp-beacon-crm-donate';

    /**
     * Sanitize a form name
     * 
     * @param string $name The form name to sanitize
     * @return string Sanitized form name
     */
    public static function sanitize_form_name($name)
    {
        $name = sanitize_text_field($name);

        if (empty($name)) {
            return 'Unnamed Form';
        }

        return $name;
    }

    /**
     * Sanitize target page ID
     * 
     * @param mixed $target_page_id The target page ID to sanitize
     * @return int Sanitized page ID
     */
    public static function sanitize_target_page_id($target_page_id)
    {
        return absint($target_page_id);
    }

    /**
     * Sanitize currencies array for a form
     * 
     * @param array $currencies Raw currencies data
     * @param string $form_name Form name for error messages
     * @return array ['currencies' => array, 'errors' => array]
     */
    public static function sanitize_currencies($currencies, $form_name = '')
    {
        $sanitized = [];
        $errors = [];
        $form_used_currencies = [];

        if (!is_array($currencies)) {
            return ['currencies' => $sanitized, 'errors' => $errors];
        }

        foreach ($currencies as $code => $form_id) {
            $code = strtoupper(sanitize_text_field($code));
            $form_id = sanitize_text_field($form_id);

            if (empty($code) || empty($form_id)) {
                continue;
            }

            // Validate form ID
            $validation = Form_Validator::validate_form_id($form_id);
            if (!$validation['valid']) {
                $errors[] = sprintf(
                    __('Invalid Beacon form ID "%s" for currency %s in form "%s". %s', self::TEXT_DOMAIN),
                    $form_id,
                    $code,
                    $form_name,
                    $validation['message']
                );
                continue;
            }

            // Check for duplicate currency within the same form
            if (isset($form_used_currencies[$code])) {
                $errors[] = sprintf(
                    __('Currency %s appears multiple times in form "%s". Each currency can only be used once per form.', self::TEXT_DOMAIN),
                    $code,
                    $form_name
                );
                continue;
            }

            $sanitized[$code] = $form_id;
            $form_used_currencies[$code] = true;
        }

        return ['currencies' => $sanitized, 'errors' => $errors];
    }

    /**
     * Sanitize default currency
     * 
     * @param string $default_currency The default currency code
     * @param array $currencies Available currencies for this form
     * @return string Sanitized default currency code
     */
    public static function sanitize_default_currency($default_currency, $currencies)
    {
        $default_currency = strtoupper(sanitize_text_field($default_currency));

        // If default currency is set but not in currencies list, clear it
        if (!empty($default_currency) && !isset($currencies[$default_currency])) {
            $default_currency = '';
        }

        // If no default currency but currencies exist, use first one
        if (empty($default_currency) && !empty($currencies)) {
            $default_currency = array_key_first($currencies);
        }

        return $default_currency;
    }

    /**
     * Sanitize a single form
     * 
     * @param array $form Raw form data
     * @param int $form_index Form index for error messages
     * @return array ['form' => array, 'errors' => array, 'has_validation_errors' => bool]
     */
    public static function sanitize_form($form, $form_index = 0)
    {
        if (!is_array($form)) {
            return [
                'form' => null,
                'errors' => [],
                'has_validation_errors' => false
            ];
        }

        $errors = [];
        $has_validation_errors = false;

        // Sanitize form name
        $form_name = self::sanitize_form_name(
            isset($form['name']) ? $form['name'] : ''
        );

        // Sanitize currencies
        $currency_result = self::sanitize_currencies(
            isset($form['currencies']) ? $form['currencies'] : [],
            $form_name
        );
        $currencies = $currency_result['currencies'];
        $errors = array_merge($errors, $currency_result['errors']);

        // Sanitize default currency
        $default_currency = self::sanitize_default_currency(
            isset($form['default_currency']) ? $form['default_currency'] : '',
            $currencies
        );

        // Sanitize target page ID
        $target_page_id = self::sanitize_target_page_id(
            isset($form['target_page_id']) ? $form['target_page_id'] : 0
        );

        // Validate target page
        $page_validation = Form_Validator::validate_target_page($target_page_id);
        if (!$page_validation['valid']) {
            $errors[] = sprintf(
                __('Form "%s": %s', self::TEXT_DOMAIN),
                $form_name,
                $page_validation['message']
            );
            $has_validation_errors = true;
        }

        // Validate currencies
        $currency_validation = Form_Validator::validate_currencies($currencies);
        if (!$currency_validation['valid']) {
            $errors[] = sprintf(
                __('Form "%s": %s', self::TEXT_DOMAIN),
                $form_name,
                $currency_validation['message']
            );
            $has_validation_errors = true;
        }

        $sanitized_form = [
            'name' => $form_name,
            'currencies' => $currencies,
            'default_currency' => $default_currency,
            'target_page_id' => $target_page_id
        ];

        return [
            'form' => $sanitized_form,
            'errors' => $errors,
            'has_validation_errors' => $has_validation_errors
        ];
    }

    /**
     * Sanitize array of forms
     * 
     * @param mixed $input Raw forms data
     * @param array $default_form Default form structure
     * @return array Sanitized forms array
     */
    public static function sanitize_forms($input, $default_form = null)
    {
        if (!is_array($input)) {
            return $default_form ? [$default_form] : [];
        }

        $sanitized = [];
        $has_validation_errors = false;

        foreach ($input as $form_index => $form) {
            $result = self::sanitize_form($form, $form_index);

            if ($result['form'] !== null) {
                $sanitized[] = $result['form'];
            }

            // Log errors
            foreach ($result['errors'] as $error) {
                self::add_settings_error('form_error_' . $form_index, $error);
            }

            if ($result['has_validation_errors']) {
                $has_validation_errors = true;
            }
        }

        // Ensure at least one form exists
        if (empty($sanitized) && $default_form) {
            $sanitized[] = $default_form;
        }

        // If validation errors occurred, return the previous valid data
        if ($has_validation_errors) {
            $previous_value = get_option(Settings::OPTION_FORMS);
            return $previous_value ? $previous_value : ($default_form ? [$default_form] : []);
        }

        return $sanitized;
    }

    /**
     * Add a settings error
     * 
     * @param string $code Error code
     * @param string $message Error message
     */
    private static function add_settings_error($code, $message)
    {
        add_settings_error(
            Settings::OPTION_FORMS,
            $code,
            $message,
            'error'
        );
    }
}
