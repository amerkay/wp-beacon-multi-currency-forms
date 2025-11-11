<?php

namespace WBCD;

if (!defined('ABSPATH'))
    exit;

/**
 * Form Validator Class
 * 
 * Centralizes all form validation logic for both PHP and JavaScript.
 * This is the single source of truth for validation rules.
 */
class Form_Validator
{
    // Validation patterns
    const PATTERN_FORM_ID = '/^[a-zA-Z0-9]{6,12}$/';
    const PATTERN_ACCOUNT_NAME = '/^[a-z0-9_-]+$/';

    // Validation constraints
    const FORM_ID_MIN_LENGTH = 6;
    const FORM_ID_MAX_LENGTH = 12;

    // Text domain
    const TEXT_DOMAIN = 'wp-beacon-crm-donate';

    /**
     * Validate a Beacon form ID
     * 
     * @param string $form_id The form ID to validate
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_form_id($form_id)
    {
        // Remove whitespace
        $form_id = trim($form_id);

        // Check if empty
        if (empty($form_id)) {
            return [
                'valid' => false,
                'message' => __('Please enter a Beacon form ID.', self::TEXT_DOMAIN)
            ];
        }

        // Check length
        if (strlen($form_id) < self::FORM_ID_MIN_LENGTH || strlen($form_id) > self::FORM_ID_MAX_LENGTH) {
            return [
                'valid' => false,
                'message' => sprintf(
                    __('Beacon form ID must be between %d and %d characters long.', self::TEXT_DOMAIN),
                    self::FORM_ID_MIN_LENGTH,
                    self::FORM_ID_MAX_LENGTH
                )
            ];
        }

        // Check alphanumeric only
        if (!preg_match(self::PATTERN_FORM_ID, $form_id)) {
            return [
                'valid' => false,
                'message' => __('Beacon form ID must contain only letters and numbers (no spaces or special characters).', self::TEXT_DOMAIN)
            ];
        }

        return [
            'valid' => true,
            'message' => ''
        ];
    }

    /**
     * Validate account name
     * 
     * @param string $account_name The account name to validate
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_account_name($account_name)
    {
        // Trim and convert to lowercase
        $account_name = strtolower(trim($account_name));

        // Empty is allowed
        if (empty($account_name)) {
            return ['valid' => true, 'message' => ''];
        }

        // Must be a slug (lowercase letters, numbers, hyphens, underscores only)
        if (!preg_match(self::PATTERN_ACCOUNT_NAME, $account_name)) {
            return [
                'valid' => false,
                'message' => __('Beacon Account Name must be lowercase and contain only letters, numbers, hyphens, and underscores (no spaces).', self::TEXT_DOMAIN)
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate that target page is selected
     * 
     * @param int $target_page_id The target page ID
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_target_page($target_page_id)
    {
        if (empty($target_page_id) || $target_page_id === 0) {
            return [
                'valid' => false,
                'message' => __('A donation form page must be selected.', self::TEXT_DOMAIN)
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate that at least one currency exists
     * 
     * @param array $currencies Array of currencies
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validate_currencies($currencies)
    {
        if (empty($currencies) || !is_array($currencies)) {
            return [
                'valid' => false,
                'message' => __('At least one currency with a form ID must be added.', self::TEXT_DOMAIN)
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Get validation rules for JavaScript
     * Returns an array of validation rules that can be passed to JavaScript
     * 
     * @return array Validation rules
     */
    public static function get_js_validation_rules()
    {
        return [
            'formId' => [
                'pattern' => '^[a-zA-Z0-9]{6,12}$',
                'minLength' => self::FORM_ID_MIN_LENGTH,
                'maxLength' => self::FORM_ID_MAX_LENGTH,
            ],
            'accountName' => [
                'pattern' => '^[a-z0-9_-]+$',
            ],
        ];
    }

    /**
     * Get validation messages for JavaScript
     * Returns all validation error messages
     * 
     * @return array Validation messages
     */
    public static function get_js_validation_messages()
    {
        return [
            'enterFormId' => __('Please enter a Beacon form ID.', self::TEXT_DOMAIN),
            'formIdLengthError' => sprintf(
                __('Beacon form ID must be between %d and %d characters long.', self::TEXT_DOMAIN),
                self::FORM_ID_MIN_LENGTH,
                self::FORM_ID_MAX_LENGTH
            ),
            'formIdAlphanumericError' => __('Beacon form ID must contain only letters and numbers (no spaces or special characters).', self::TEXT_DOMAIN),
            'targetPageRequired' => __('A donation form page must be selected.', self::TEXT_DOMAIN),
            'currenciesRequired' => __('At least one currency with a form ID must be added.', self::TEXT_DOMAIN),
            'validationFailed' => __('Please fix the following errors before saving:', self::TEXT_DOMAIN),
        ];
    }
}
