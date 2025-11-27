<?php

namespace BMCF\Utils;

if (!defined('ABSPATH'))
    exit;

/**
 * Utility class for parsing Gutenberg block attributes.
 * Provides consistent attribute parsing and transformation for blocks.
 */
class Block_Attrs_Parser
{
    /**
     * Parse custom parameters from block attributes.
     * Handles the customParams array format from Gutenberg blocks.
     * 
     * @param array $attrs Block attributes array
     * @return array Parsed custom parameters
     */
    public static function parse_custom_params($attrs)
    {
        return Params_Parser::from_array($attrs['customParams'] ?? []);
    }

    /**
     * Build render arguments from block attributes with custom params.
     * Extracts customParams and any other specified attributes.
     * 
     * @param array $attrs Block attributes
     * @param array $attr_map Optional mapping of attribute names to render arg keys
     * @return array Render arguments array
     */
    public static function build_render_args($attrs, $attr_map = [])
    {
        $render_args = [];

        // Always include custom params if present
        $custom_params = self::parse_custom_params($attrs);
        if (!empty($custom_params)) {
            $render_args['customParams'] = $custom_params;
        }

        // Map other attributes
        foreach ($attr_map as $attr_key => $render_key) {
            if (isset($attrs[$attr_key])) {
                $render_args[$render_key] = $attrs[$attr_key];
            }
        }

        return $render_args;
    }

    /**
     * Parse preset amounts from block attributes.
     * Handles presetsSingle, presetsMonthly, presetsAnnual attributes.
     * 
     * @param array $attrs Block attributes
     * @return array Parsed preset amounts by frequency
     */
    public static function parse_presets($attrs)
    {
        $default_presets = [];
        $frequencies = ['single', 'monthly', 'annual'];

        foreach ($frequencies as $freq) {
            $attr_key = 'presets' . ucfirst($freq);

            if (!empty($attrs[$attr_key])) {
                $parsed = Preset_Parser::parse_amounts(
                    $attrs[$attr_key],
                    Preset_Parser::get_defaults($freq)
                );

                if (!empty($parsed)) {
                    $default_presets[$freq] = $parsed;
                }
            }

            // Set default if not specified
            if (empty($default_presets[$freq])) {
                $default_presets[$freq] = Preset_Parser::get_defaults($freq);
            }
        }

        return $default_presets;
    }

    /**
     * Parse allowed frequencies from block attributes.
     * Handles boolean flags like frequencySingle, frequencyMonthly, frequencyAnnual.
     * 
     * @param array $attrs Block attributes
     * @return array Array of allowed frequency strings
     */
    public static function parse_frequencies($attrs)
    {
        $allowed = [];
        $frequency_map = [
            'frequencySingle' => 'single',
            'frequencyMonthly' => 'monthly',
            'frequencyAnnual' => 'annual',
        ];

        foreach ($frequency_map as $attr_key => $freq_name) {
            if (!empty($attrs[$attr_key])) {
                $allowed[] = $freq_name;
            }
        }

        // Default to all frequencies if none specified
        if (empty($allowed)) {
            $allowed = ['single', 'monthly', 'annual'];
        }

        return $allowed;
    }
}
