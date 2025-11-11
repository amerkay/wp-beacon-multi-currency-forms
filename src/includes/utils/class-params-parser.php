<?php

namespace WBCD\Utils;

if (!defined('ABSPATH'))
    exit;

/**
 * Utility class for parsing custom parameters from various input formats.
 * Provides consistent parameter parsing across shortcodes, blocks, and integrations.
 */
class Params_Parser
{
    /**
     * Parse custom parameters from URL-encoded string format.
     * 
     * @param string $params_string URL-encoded string like "key1=value1&key2=value2"
     * @return array Associative array of parsed parameters
     */
    public static function from_url_encoded($params_string)
    {
        $custom_params = [];

        if (!empty($params_string) && is_string($params_string)) {
            parse_str($params_string, $custom_params);

            // Sanitize the parsed values
            $custom_params = array_map('\\sanitize_text_field', $custom_params);
        }

        return $custom_params;
    }

    /**
     * Parse custom parameters from array format (used by Gutenberg blocks and Elementor).
     * Handles multiple formats:
     * - Gutenberg: [['key'=>'foo', 'value'=>'bar'], ...]
     * - Elementor: [['param_key'=>'foo', 'param_value'=>'bar'], ...]
     * 
     * @param array $params_array Array of parameter objects
     * @return array Associative array of parsed parameters
     */
    public static function from_array($params_array)
    {
        $custom_params = [];

        if (is_array($params_array)) {
            foreach ($params_array as $param) {
                // Try Gutenberg format first (key/value)
                if (isset($param['key']) && !empty($param['key']) && isset($param['value'])) {
                    $custom_params[\sanitize_key($param['key'])] = \sanitize_text_field($param['value']);
                }
                // Try Elementor format (param_key/param_value)
                elseif (isset($param['param_key']) && !empty($param['param_key']) && isset($param['param_value'])) {
                    $custom_params[\sanitize_key($param['param_key'])] = \sanitize_text_field($param['param_value']);
                }
            }
        }

        return $custom_params;
    }

    /**
     * Auto-detect format and parse custom parameters.
     * Handles both URL-encoded strings and array formats.
     * 
     * @param mixed $params Parameters in any supported format
     * @return array Associative array of parsed parameters
     */
    public static function parse($params)
    {
        if (is_array($params)) {
            // Check if it's a key-value array or array of objects
            if (self::is_associative_array($params)) {
                // Already in key-value format, just sanitize
                return array_map('\\sanitize_text_field', $params);
            }
            // Array of objects format
            return self::from_array($params);
        }

        if (is_string($params)) {
            return self::from_url_encoded($params);
        }

        return [];
    }

    /**
     * Check if array is associative (key-value pairs) vs indexed.
     * 
     * @param array $arr Array to check
     * @return bool True if associative, false if indexed
     */
    private static function is_associative_array($arr)
    {
        if (empty($arr)) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
