<?php

/**
 * Plugin Name:       Beacon Multi Currency Forms
 * Plugin URI:        https://github.com/amerkay/wp-beacon-multi-currency-forms
 * Description:       Embed BeaconCRM donation forms with multi-currency support, geo-location detection, and UTM tracking. Works with Gutenberg, Elementor, and Divi.
 * Version:           0.1.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      6.7
 * Author:            Amer Kawar
 * Author URI:        https://wildamer.com
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wp-beacon-multi-currency-forms
 * Domain Path:       /languages
 */

if (!defined('ABSPATH'))
    exit;

// Extract version from plugin header (single source of truth)
if (!defined('WPBMCF_VERSION')) {
    $plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
    define('WPBMCF_VERSION', $plugin_data['Version']);
}
define('WPBMCF_FILE', __FILE__);
define('WPBMCF_PATH', plugin_dir_path(__FILE__));
define('WPBMCF_URL', plugin_dir_url(__FILE__));

// --- Includes ---
require_once WPBMCF_PATH . 'includes/class-constants.php';
require_once WPBMCF_PATH . 'includes/class-form-validator.php';
require_once WPBMCF_PATH . 'includes/class-form-sanitizer.php';
require_once WPBMCF_PATH . 'includes/class-settings-renderer.php';
require_once WPBMCF_PATH . 'includes/class-settings.php';
require_once WPBMCF_PATH . 'includes/class-assets.php';
require_once WPBMCF_PATH . 'includes/class-geoip-dependency.php';

require_once WPBMCF_PATH . 'includes/utils/class-params-parser.php';
require_once WPBMCF_PATH . 'includes/utils/class-preset-parser.php';
require_once WPBMCF_PATH . 'includes/utils/class-frequency-parser.php';
require_once WPBMCF_PATH . 'includes/utils/class-block-attrs-parser.php';

require_once WPBMCF_PATH . 'includes/render/class-donate-form-render.php';
require_once WPBMCF_PATH . 'includes/render/class-donate-box-render.php';

require_once WPBMCF_PATH . 'includes/shortcodes/class-shortcode-donate-form.php';
require_once WPBMCF_PATH . 'includes/shortcodes/class-shortcode-donate-box.php';

// Elementor & Divi adapters are loaded conditionally in their respective hooks below.

// --- Bootstrap ---
add_action('plugins_loaded', function () {
    load_plugin_textdomain('wp-beacon-multi-currency-forms', false, dirname(plugin_basename(WPBMCF_FILE)) . '/languages');
});

add_action('init', function () {

    // Register style handle early so block.json can reference it
    wp_register_style(
        'wbcd-front',
        WPBMCF_URL . 'public/css/donate.css',
        [],
        WPBMCF_VERSION
    );

    // Register dynamic blocks from metadata
    register_block_type_from_metadata(
        WPBMCF_PATH . 'blocks/donation-form',
        array(
            'render_callback' => function ($attrs, $content) {
                // Get form name from block attributes
                $form_name = isset($attrs['formName']) ? $attrs['formName'] : '';

                // Build args array from block attributes using parser
                $render_args = [
                    'customParams' => WBCD\Utils\Block_Attrs_Parser::parse_custom_params($attrs),
                    'defaultFrequency' => isset($attrs['defaultFrequency']) ? $attrs['defaultFrequency'] : '',
                    'defaultAmount' => isset($attrs['defaultAmount']) ? $attrs['defaultAmount'] : ''
                ];

                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                WBCD\Assets::enqueue_donation_form($form_name);

                // Call the render method
                return WBCD\Render\Donate_Form_Render::render($form_name, $render_args);
            },
        )
    );

    register_block_type_from_metadata(
        WPBMCF_PATH . 'blocks/donation-box',
        array(
            'render_callback' => function ($attrs, $content) {
                // Get form name from block attributes
                $form_name = isset($attrs['formName']) ? $attrs['formName'] : '';

                // Get target page URL from block attributes
                $target_page_url = '';
                if (isset($attrs['targetPageId']) && $attrs['targetPageId'] > 0) {
                    $permalink = get_permalink($attrs['targetPageId']);
                    if ($permalink) {
                        $target_page_url = $permalink;
                    }
                }

                // Build args array from block attributes using parsers
                $render_args = [
                    'primaryColor' => isset($attrs['primaryColor']) ? $attrs['primaryColor'] : '',
                    'brandColor' => isset($attrs['brandColor']) ? $attrs['brandColor'] : '',
                    'title' => isset($attrs['title']) ? $attrs['title'] : 'Make a donation',
                    'subtitle' => isset($attrs['subtitle']) ? $attrs['subtitle'] : 'Pick your currency, frequency, and amount',
                    'noticeText' => isset($attrs['noticeText']) ? $attrs['noticeText'] : "You'll be taken to our secure donation form to complete your gift.",
                    'buttonText' => isset($attrs['buttonText']) ? $attrs['buttonText'] : 'Donate now →',
                    'customParams' => WBCD\Utils\Block_Attrs_Parser::parse_custom_params($attrs),
                    'allowedFrequencies' => isset($attrs['allowedFrequencies']) ? $attrs['allowedFrequencies'] : ['single', 'monthly', 'annual'],
                    'defaultPresets' => isset($attrs['defaultPresets']) ? $attrs['defaultPresets'] : WBCD\Utils\Preset_Parser::get_all_defaults(),
                    'targetPageUrl' => $target_page_url
                ];

                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                // Note: enqueue_donation_box is called inside Donate_Box_Render::render with the target URL
        
                // Call the render method
                return WBCD\Render\Donate_Box_Render::render($form_name, $render_args);
            },
        )
    );

    // Shortcodes
    WBCD\Shortcodes\Shortcode_Donate_Form::register();
    WBCD\Shortcodes\Shortcode_Donate_Box::register();

    // Pre-process content to normalize shortcodes with line breaks and tabs
    add_filter('the_content', function ($content) {
        // Only process if content contains our shortcodes
        if (strpos($content, '[beaconcrm_donate_') === false) {
            return $content;
        }

        // Normalize line breaks and tabs within our shortcodes
        $content = preg_replace_callback(
            '/\[beaconcrm_donate_(box|form|button)\s+([^\]]*?)\]/s',
            function ($matches) {
                $shortcode_name = $matches[1];
                $attributes = $matches[2];

                // Remove line breaks and tabs, but preserve spaces around = signs
                $attributes = preg_replace('/[\r\n\t]+/', ' ', $attributes);
                // Collapse multiple spaces into one
                $attributes = preg_replace('/\s+/', ' ', $attributes);
                // Trim leading/trailing spaces
                $attributes = trim($attributes);

                return '[beaconcrm_donate_' . $shortcode_name . ' ' . $attributes . ']';
            },
            $content
        );

        return $content;
    }, 8); // Priority 8 to run before do_shortcode (which is at 11)
});

// Localize forms data for Gutenberg block editor
add_action('enqueue_block_editor_assets', function () {
    $forms = WBCD\Settings::get_forms_for_dropdown();
    $form_options = [['value' => '', 'label' => __('Default (First form)', 'wp-beacon-multi-currency-forms')]];
    $forms_data = [];

    foreach ($forms as $name => $label) {
        $form_options[] = [
            'value' => $name,
            'label' => $label
        ];

        $forms_data[$name] = [
            'name' => $name,
            'label' => $label
        ];
    }

    // Also get currencies for the default form (empty string)
    $default_currencies = WBCD\Settings::get_forms_by_currency('');
    $forms_data[''] = [
        'name' => '',
        'label' => __('Default (First form)', 'wp-beacon-multi-currency-forms'),
        'currencies' => array_keys($default_currencies)
    ];

    // Get all pages for dropdown with permalink
    $pages = get_pages();
    $pages_data = [];
    foreach ($pages as $page) {
        $permalink = get_permalink($page->ID);
        $path = str_replace(home_url(), '', $permalink);
        $pages_data[] = [
            'id' => $page->ID,
            'title' => $page->post_title,
            'permalink' => $permalink,
            'path' => $path
        ];
    }

    wp_localize_script('wp-blocks', 'wbcdForms', $form_options);
    wp_localize_script('wp-blocks', 'wbcdPages', $pages_data);
    wp_localize_script('wp-blocks', 'wbcdPages', $pages_data);

    // Localize constants for block editor
    wp_localize_script('wp-blocks', 'WBCD_CONSTANTS', [
        'colors' => WBCD\Constants::get_all_colors(),
        'presets' => WBCD\Constants::get_all_presets(),
        'frequencies' => WBCD\Constants::get_default_frequencies(),
        'buttonSizes' => WBCD\Constants::get_valid_button_sizes(),
        'version' => WBCD\Constants::get_version(),
    ]);
});

// Settings page + admin notices
add_action('admin_menu', ['WBCD\\Settings', 'add_menu']);
add_action('admin_init', ['WBCD\\Settings', 'register']);
add_action('admin_init', ['WBCD\\Settings', 'on_settings_updated']);
add_action('admin_enqueue_scripts', ['WBCD\\Settings', 'enqueue_admin_assets']);
add_action('admin_notices', ['WBCD\\Settings', 'settings_updated_notice']);
add_action('admin_notices', ['WBCD\\GeoIP_Dependency', 'admin_notices']);
add_action('admin_footer', ['WBCD\\GeoIP_Dependency', 'enqueue_dismiss_script']);
add_action('wp_ajax_wbcd_dismiss_geoip_notice', ['WBCD\\GeoIP_Dependency', 'dismiss_notice']);

// Add settings link to plugins page
add_filter('plugin_action_links_' . plugin_basename(WPBMCF_FILE), function ($links) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('options-general.php?page=wbcd-settings'),
        __('Settings', 'wp-beacon-multi-currency-forms')
    );
    array_unshift($links, $settings_link);
    return $links;
});

// Frontend scripts - Beacon SDK and UTM Tracking
add_action('wp_enqueue_scripts', function () {
    $load_beacon_globally = WBCD\Settings::get_load_beacon_globally();
    $track_utm = WBCD\Settings::get_utm_tracking_enabled();

    // Load Beacon SDK globally if enabled
    // This enables proper cross-domain attribution tracking per Beacon docs
    if ($load_beacon_globally) {
        WBCD\Assets::enqueue_beacon_sdk();
    }
    // When disabled, SDK is loaded only on pages with donate modules
    // (handled in Assets::enqueue_donation_form() and Assets::enqueue_donation_box())

    // Load UTM tracker if enabled
    if ($track_utm) {
        wp_enqueue_script(
            'wbcd-utm-tracker',
            WPBMCF_URL . 'public/js/utm-tracker.js',
            [],
            WPBMCF_VERSION,
            true // Load in footer
        );
    }
});

// Elementor Widgets// Elementor Widgets
add_action('elementor/widgets/register', function ($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    // Load abstract base class first
    require_once WPBMCF_PATH . 'integrations/elementor/abstract-wbcd-elementor-widget.php';
    // Load widget classes
    require_once WPBMCF_PATH . 'integrations/elementor/class-elementor-widget-donate-form.php';
    require_once WPBMCF_PATH . 'integrations/elementor/class-elementor-widget-donate-box.php';
    // Register widgets
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Form_Widget());
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Box_Widget());
});

// Divi Modules
add_action('et_builder_ready', function () {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }
    // Load abstract base class first
    require_once WPBMCF_PATH . 'integrations/divi/abstract-wbcd-divi-module.php';
    // Load module classes
    require_once WPBMCF_PATH . 'integrations/divi/class-divi-module-donate-form.php';
    require_once WPBMCF_PATH . 'integrations/divi/class-divi-module-donate-box.php';
    // Instantiate modules
    new \WBCD\Integrations\Divi\Donate_Form_Module();
    new \WBCD\Integrations\Divi\Donate_Box_Module();
});
