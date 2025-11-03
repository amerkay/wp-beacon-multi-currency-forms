<?php

namespace WBCD\Utils;

if (! defined('ABSPATH')) exit;

/**
 * Utility class for parsing preset donation amounts.
 * Provides consistent preset amount parsing with validation and defaults.
 * 
 * @uses \WBCD\Constants For default preset amounts
 */
class Preset_Parser
{

    /**
     * Parse preset amounts from comma-separated string.
     * Filters out invalid values and returns positive numbers only.
     * 
     * @param string $amounts_string Comma-separated amounts like "10,20,30"
     * @param array $default Default amounts to use if parsing results in empty array
     * @return array Array of positive float values
     */
    public static function parse_amounts($amounts_string, $default = [])
    {
        if (empty($amounts_string) || !is_string($amounts_string)) {
            return !empty($default) ? $default : [];
        }

        $amounts = array_map('trim', explode(',', $amounts_string));
        $amounts = array_map('floatval', $amounts);
        $amounts = array_filter($amounts, function ($n) {
            return $n > 0;
        });
        $amounts = array_values($amounts); // Re-index

        // Return default if no valid amounts found
        if (empty($amounts) && !empty($default)) {
            return $default;
        }

        return $amounts;
    }

    /**
     * Parse preset amounts for all frequencies from an attributes array.
     * Handles keys like 'presets_single', 'presets_monthly', 'presets_annual'.
     * 
     * @param array $attrs Attributes array with preset keys
     * @param array $frequencies Array of frequencies to parse (e.g., ['single', 'monthly', 'annual'])
     * @return array Associative array with frequency keys and amount arrays as values
     */
    public static function parse_all_presets($attrs, $frequencies = ['single', 'monthly', 'annual'])
    {
        $default_presets = [];

        foreach ($frequencies as $freq) {
            $preset_key = 'presets_' . $freq;

            if (!empty($attrs[$preset_key])) {
                $parsed = self::parse_amounts(
                    $attrs[$preset_key],
                    \WBCD\Constants::get_preset_amounts($freq)
                );

                if (!empty($parsed)) {
                    $default_presets[$freq] = $parsed;
                }
            }

            // Set default if not specified or parsing failed
            if (empty($default_presets[$freq])) {
                $default_presets[$freq] = \WBCD\Constants::get_preset_amounts($freq);
            }
        }

        return $default_presets;
    }

    /**
     * Get default preset amounts for a specific frequency.
     * 
     * @param string $frequency Frequency type ('single', 'monthly', or 'annual')
     * @return array Default amounts for the frequency, or empty array if invalid
     */
    public static function get_defaults($frequency)
    {
        return \WBCD\Constants::get_preset_amounts($frequency);
    }

    /**
     * Get all default preset amounts.
     * 
     * @return array All default presets
     */
    public static function get_all_defaults()
    {
        return \WBCD\Constants::get_all_presets();
    }
}
