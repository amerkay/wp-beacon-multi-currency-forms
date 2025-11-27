<?php

namespace BMCF\Utils;

if (!defined('ABSPATH'))
    exit;

/**
 * Utility class for parsing donation frequency options.
 * Provides consistent frequency parsing from CSV strings or boolean toggles.
 * 
 * @uses \BMCF\Constants For valid and default frequencies
 */
class Frequency_Parser
{

    /**
     * Parse allowed frequencies from comma-separated string.
     * 
     * @param string $frequencies_string Comma-separated frequencies like "single,monthly"
     * @return array Array of valid frequency strings
     */
    public static function from_csv($frequencies_string)
    {
        if (empty($frequencies_string) || !is_string($frequencies_string)) {
            return \BMCF\Constants::get_default_frequencies();
        }

        $allowed = array_map('trim', explode(',', $frequencies_string));
        $allowed = array_filter($allowed, function ($f) {
            return in_array($f, \BMCF\Constants::get_valid_frequencies());
        });

        // Return default if none valid
        if (empty($allowed)) {
            return \BMCF\Constants::get_default_frequencies();
        }

        return array_values($allowed);
    }

    /**
     * Parse allowed frequencies from boolean toggles.
     * Used by Divi and Elementor which use individual toggles per frequency.
     * 
     * @param array $toggles Associative array with keys like ['single' => true, 'monthly' => false, ...]
     * @return array Array of enabled frequency strings
     */
    public static function from_toggles($toggles)
    {
        $allowed = [];

        foreach (\BMCF\Constants::get_valid_frequencies() as $freq) {
            if (!empty($toggles[$freq])) {
                $allowed[] = $freq;
            }
        }

        // Return default if none selected
        if (empty($allowed)) {
            return \BMCF\Constants::get_default_frequencies();
        }

        return $allowed;
    }

    /**
     * Get all valid frequency types.
     * 
     * @return array All valid frequencies
     */
    public static function get_valid_frequencies()
    {
        return \BMCF\Constants::get_valid_frequencies();
    }

    /**
     * Get default frequencies.
     * 
     * @return array Default frequencies (all)
     */
    public static function get_defaults()
    {
        return \BMCF\Constants::get_default_frequencies();
    }
}
