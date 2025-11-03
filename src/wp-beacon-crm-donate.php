<?php

/**
 * Plugin Name:  Beacon CRM Donate
 * Description:  Two Beacon donation blocks (full page form + donate box) with shortcodes and Elementor/Divi adapters. No bundlers; DRY PHP renderers; simple Settings.
 * Version:      0.1.0
 * Author:       Amer Kawar @ WildAmer.com
 * Text Domain:  wp-beacon-crm-donate
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) exit;

define('WPBCD_VERSION',        '0.1.0');
define('WPBCD_FILE',           __FILE__);
define('WPBCD_PATH',           plugin_dir_path(__FILE__));
define('WPBCD_URL',            plugin_dir_url(__FILE__));

// --- Includes ---
require_once WPBCD_PATH . 'includes/class-constants.php';
require_once WPBCD_PATH . 'includes/class-form-validator.php';
require_once WPBCD_PATH . 'includes/class-form-sanitizer.php';
require_once WPBCD_PATH . 'includes/class-settings-renderer.php';
require_once WPBCD_PATH . 'includes/class-settings.php';
require_once WPBCD_PATH . 'includes/class-assets.php';
require_once WPBCD_PATH . 'includes/class-geoip-dependency.php';

require_once WPBCD_PATH . 'includes/utils/class-params-parser.php';
require_once WPBCD_PATH . 'includes/utils/class-preset-parser.php';
require_once WPBCD_PATH . 'includes/utils/class-frequency-parser.php';
require_once WPBCD_PATH . 'includes/utils/class-block-attrs-parser.php';

require_once WPBCD_PATH . 'includes/render/class-donate-form-render.php';
require_once WPBCD_PATH . 'includes/render/class-donate-box-render.php';
require_once WPBCD_PATH . 'includes/render/class-donate-button-render.php';

require_once WPBCD_PATH . 'includes/shortcodes/class-shortcode-donate-form.php';
require_once WPBCD_PATH . 'includes/shortcodes/class-shortcode-donate-box.php';
require_once WPBCD_PATH . 'includes/shortcodes/class-shortcode-donate-button.php';

// Elementor & Divi adapters are loaded conditionally in their respective hooks below.

// --- Bootstrap ---
add_action('plugins_loaded', function () {
    load_plugin_textdomain('wp-beacon-crm-donate', false, dirname(plugin_basename(WPBCD_FILE)) . '/languages');
});

add_action('init', function () {

    // Register style handle early so block.json can reference it
    wp_register_style(
        'wbcd-front',
        WPBCD_URL . 'public/css/donate.css',
        [],
        WPBCD_VERSION
    );

    // Register dynamic blocks from metadata
    register_block_type_from_metadata(
        WPBCD_PATH . 'blocks/donation-form',
        array(
            'render_callback' => function ($attrs, $content) {
                // Get form name from block attributes
                $form_name = isset($attrs['formName']) ? $attrs['formName'] : '';

                // Build args array from block attributes using parser
                $render_args = [
                    'customParams' => WBCD\Utils\Block_Attrs_Parser::parse_custom_params($attrs)
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
        WPBCD_PATH . 'blocks/donation-box',
        array(
            'render_callback' => function ($attrs, $content) {
                // Get form name from block attributes
                $form_name = isset($attrs['formName']) ? $attrs['formName'] : '';

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
                    'defaultPresets' => isset($attrs['defaultPresets']) ? $attrs['defaultPresets'] : WBCD\Utils\Preset_Parser::get_all_defaults()
                ];

                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                WBCD\Assets::enqueue_donation_box($form_name);

                // Call the render method
                return WBCD\Render\Donate_Box_Render::render($form_name, $render_args);
            },
        )
    );

    register_block_type_from_metadata(
        WPBCD_PATH . 'blocks/donation-button',
        array(
            'render_callback' => function ($attrs, $content) {
                // Get form name from block attributes
                $form_name = isset($attrs['formName']) ? $attrs['formName'] : '';

                // Build args array from block attributes using parser
                $render_args = [
                    'color' => isset($attrs['color']) ? $attrs['color'] : '',
                    'text' => isset($attrs['text']) ? $attrs['text'] : 'Donate',
                    'size' => isset($attrs['size']) ? $attrs['size'] : 'md',
                    'amount' => isset($attrs['amount']) ? $attrs['amount'] : '',
                    'frequency' => isset($attrs['frequency']) ? $attrs['frequency'] : '',
                    'currency' => isset($attrs['currency']) ? $attrs['currency'] : '',
                    'customParams' => WBCD\Utils\Block_Attrs_Parser::parse_custom_params($attrs)
                ];

                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');

                // Call the render method
                return WBCD\Render\Donate_Button_Render::render($form_name, $render_args);
            },
        )
    );

    // Shortcodes
    WBCD\Shortcodes\Shortcode_Donate_Form::register();
    WBCD\Shortcodes\Shortcode_Donate_Box::register();
    WBCD\Shortcodes\Shortcode_Donate_Button::register();

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
    $form_options = [['value' => '', 'label' => __('Default (First form)', 'wp-beacon-crm-donate')]];
    $forms_data = [];

    foreach ($forms as $name => $label) {
        $form_options[] = ['value' => $name, 'label' => $label];
        // Get currencies for each form
        $currencies = WBCD\Settings::get_forms_by_currency($name);
        $forms_data[$name] = [
            'label' => $label,
            'currencies' => array_keys($currencies)
        ];
    }

    // Also get currencies for the default form (empty string)
    $default_currencies = WBCD\Settings::get_forms_by_currency('');
    $forms_data[''] = [
        'label' => __('Default (First form)', 'wp-beacon-crm-donate'),
        'currencies' => array_keys($default_currencies)
    ];

    wp_localize_script('wp-blocks', 'wbcdForms', $form_options);
    wp_localize_script('wp-blocks', 'wbcdFormsData', $forms_data);

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
add_action('admin_menu',  ['WBCD\\Settings', 'add_menu']);
add_action('admin_init',  ['WBCD\\Settings', 'register']);
add_action('admin_enqueue_scripts', ['WBCD\\Settings', 'enqueue_admin_assets']);
add_action('admin_notices', ['WBCD\\GeoIP_Dependency', 'admin_notices']);
add_action('admin_footer', ['WBCD\\GeoIP_Dependency', 'enqueue_dismiss_script']);
add_action('wp_ajax_wbcd_dismiss_geoip_notice', ['WBCD\\GeoIP_Dependency', 'dismiss_notice']);
add_action('wp_ajax_wbcd_create_page', ['WBCD\\Settings', 'ajax_create_page']);

// Elementor Widgets
add_action('elementor/widgets/register', function ($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    // Load abstract base class first
    require_once WPBCD_PATH . 'integrations/elementor/abstract-wbcd-elementor-widget.php';
    // Load widget classes
    require_once WPBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-form.php';
    require_once WPBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-box.php';
    require_once WPBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-button.php';
    // Register widgets
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Form_Widget());
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Box_Widget());
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Button_Widget());
});

// Divi Modules
add_action('et_builder_ready', function () {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }
    // Load abstract base class first
    require_once WPBCD_PATH . 'integrations/divi/abstract-wbcd-divi-module.php';
    // Load module classes
    require_once WPBCD_PATH . 'integrations/divi/class-divi-module-donate-form.php';
    require_once WPBCD_PATH . 'integrations/divi/class-divi-module-donate-box.php';
    require_once WPBCD_PATH . 'integrations/divi/class-divi-module-donate-button.php';
    // Instantiate modules
    new \WBCD\Integrations\Divi\Donate_Form_Module();
    new \WBCD\Integrations\Divi\Donate_Box_Module();
    new \WBCD\Integrations\Divi\Donate_Button_Module();
});
