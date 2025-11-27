<?php

namespace BMCF;

if (!defined('ABSPATH'))
    exit;

/**
 * Central constants class for all default values.
 * 
 * This class serves as the single source of truth for:
 * - Default colors (brand, primary, text, borders)
 * - Default preset amounts for each frequency
 * - Valid and default frequencies
 * - Button sizes
 * - Text domain and CSS prefixes
 * 
 * All other PHP files, JavaScript (via wp_localize_script), and CSS (via inline styles)
 * should reference these constants to ensure consistency across the plugin.
 * 
 * @package BMCF
 * @since 0.1.0
 */
class Constants
{
    /**
     * WordPress text domain for translations.
     */
    const TEXT_DOMAIN = 'beacon-multi-currency-forms';

    /**
     * CSS class prefix used throughout the plugin.
     */
    const CSS_PREFIX = 'bmcf-';

    // ========================================
    // COLOR CONSTANTS
    // ========================================

    /**
     * Default brand color (used for selected states, hover effects).
     * Used as --bmcf-brand CSS variable.
     */
    const COLOR_BRAND = '#676767';

    /**
     * Default primary/accent color (used for CTAs, buttons).
     * Used as --bmcf-primary CSS variable.
     */
    const COLOR_PRIMARY = '#FF7B1A';

    /**
     * Default text color.
     */
    const COLOR_TEXT = '#111827';

    /**
     * Default border color.
     */
    const COLOR_BORDER = '#e5e7eb';

    // ========================================
    // PRESET AMOUNTS
    // ========================================

    /**
     * Default preset amounts for single (one-time) donations.
     */
    const PRESET_SINGLE = [10, 20, 30];

    /**
     * Default preset amounts for monthly recurring donations.
     */
    const PRESET_MONTHLY = [5, 10, 15];

    /**
     * Default preset amounts for annual recurring donations.
     */
    const PRESET_ANNUAL = [50, 100, 200];

    // ========================================
    // FREQUENCY CONSTANTS
    // ========================================

    /**
     * Valid donation frequency types.
     */
    const VALID_FREQUENCIES = ['single', 'monthly', 'annual'];

    /**
     * Default enabled frequencies (all frequencies enabled by default).
     */
    const DEFAULT_FREQUENCIES = ['single', 'monthly', 'annual'];

    // ========================================
    // BUTTON SIZES
    // ========================================

    /**
     * Valid button size options.
     */
    const VALID_BUTTON_SIZES = ['md', 'lg', 'xl'];

    /**
     * Default button size.
     */
    const DEFAULT_BUTTON_SIZE = 'md';

    // ========================================
    // STATIC GETTER METHODS
    // ========================================

    /**
     * Get a specific color by key.
     * 
     * @param string $key Color key: 'brand', 'primary', 'text', 'border'
     * @return string Hex color value or empty string if invalid key
     */
    public static function get_color($key)
    {
        $colors = self::get_all_colors();
        return isset($colors[$key]) ? $colors[$key] : '';
    }

    /**
     * Get all colors as an associative array.
     * 
     * @return array {
     *     @type string $brand   Brand color hex value
     *     @type string $primary Primary color hex value
     *     @type string $text    Text color hex value
     *     @type string $border  Border color hex value
     * }
     */
    public static function get_all_colors()
    {
        return [
            'brand' => self::COLOR_BRAND,
            'primary' => self::COLOR_PRIMARY,
            'text' => self::COLOR_TEXT,
            'border' => self::COLOR_BORDER,
        ];
    }

    /**
     * Get preset amounts for a specific frequency.
     * 
     * @param string $frequency Frequency type: 'single', 'monthly', 'annual'
     * @return array Array of preset amounts or empty array if invalid frequency
     */
    public static function get_preset_amounts($frequency)
    {
        $presets = self::get_all_presets();
        return isset($presets[$frequency]) ? $presets[$frequency] : [];
    }

    /**
     * Get all preset amounts as an associative array.
     * 
     * @return array {
     *     @type array $single  Single donation presets
     *     @type array $monthly Monthly donation presets
     *     @type array $annual  Annual donation presets
     * }
     */
    public static function get_all_presets()
    {
        return [
            'single' => self::PRESET_SINGLE,
            'monthly' => self::PRESET_MONTHLY,
            'annual' => self::PRESET_ANNUAL,
        ];
    }

    /**
     * Get valid frequency options.
     * 
     * @return array Array of valid frequency strings
     */
    public static function get_valid_frequencies()
    {
        return self::VALID_FREQUENCIES;
    }

    /**
     * Get default enabled frequencies.
     * 
     * @return array Array of default frequency strings
     */
    public static function get_default_frequencies()
    {
        return self::DEFAULT_FREQUENCIES;
    }

    /**
     * Get valid button sizes.
     * 
     * @return array Array of valid button size strings
     */
    public static function get_valid_button_sizes()
    {
        return self::VALID_BUTTON_SIZES;
    }

    /**
     * Get default button size.
     * 
     * @return string Default button size
     */
    public static function get_default_button_size()
    {
        return self::DEFAULT_BUTTON_SIZE;
    }

    /**
     * Get the CSS prefix.
     * 
     * @return string CSS prefix
     */
    public static function get_css_prefix()
    {
        return self::CSS_PREFIX;
    }

    /**
     * Get plugin version.
     * Reads from main plugin file header (single source of truth).
     * 
     * @return string Version string
     */
    public static function get_version()
    {
        return defined('BMCF_VERSION') ? BMCF_VERSION : '0.1.0';
    }

    // ========================================
    // FILTER HOOKS FOR EXTENSIBILITY
    // ========================================

    /**
     * Get a color with filter hook for customization.
     * 
     * @param string $key Color key
     * @return string Filtered color value
     */
    public static function get_filtered_color($key)
    {
        $color = self::get_color($key);
        return apply_filters('bmcf_default_color_' . $key, $color);
    }

    /**
     * Get preset amounts with filter hook for customization.
     * 
     * @param string $frequency Frequency type
     * @return array Filtered preset amounts
     */
    public static function get_filtered_preset_amounts($frequency)
    {
        $amounts = self::get_preset_amounts($frequency);
        return apply_filters('bmcf_default_preset_amounts_' . $frequency, $amounts);
    }

    /**
     * Get all presets with filter hook for customization.
     * 
     * @return array Filtered all presets
     */
    public static function get_filtered_all_presets()
    {
        $presets = self::get_all_presets();
        return apply_filters('bmcf_default_all_presets', $presets);
    }

    /**
     * Get default frequencies with filter hook for customization.
     * 
     * @return array Filtered default frequencies
     */
    public static function get_filtered_default_frequencies()
    {
        $frequencies = self::get_default_frequencies();
        return apply_filters('bmcf_default_frequencies', $frequencies);
    }
}
