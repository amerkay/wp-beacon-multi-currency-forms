<?php

/**
 * Plugin Name:  Beacon CRM Donate
 * Description:  Two Beacon donation blocks (full page form + CTA box) with shortcodes and Elementor/Divi adapters. No bundlers; DRY PHP renderers; simple Settings.
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
require_once WPBCD_PATH . 'includes/class-settings.php';
require_once WPBCD_PATH . 'includes/class-assets.php';
require_once WPBCD_PATH . 'includes/class-geoip-dependency.php';

require_once WPBCD_PATH . 'includes/render/class-donate-form-render.php';
require_once WPBCD_PATH . 'includes/render/class-donate-cta-render.php';

require_once WPBCD_PATH . 'includes/shortcodes/class-shortcode-donate-form.php';
require_once WPBCD_PATH . 'includes/shortcodes/class-shortcode-donate-box.php';

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

                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                WBCD\Assets::enqueue_donation_form($form_name);

                // Call the render method
                return WBCD\Render\Donate_Form_Render::render($form_name);
            },
        )
    );

    register_block_type_from_metadata(
        WPBCD_PATH . 'blocks/donation-box',
        array(
            'render_callback' => function ($attrs, $content) {
                // Get form name from block attributes
                $form_name = isset($attrs['formName']) ? $attrs['formName'] : '';

                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                WBCD\Assets::enqueue_donation_cta($form_name);

                // Call the render method
                return WBCD\Render\Donate_CTA_Render::render($form_name);
            },
        )
    );

    // Shortcodes
    WBCD\Shortcodes\Shortcode_Donate_Form::register();
    WBCD\Shortcodes\Shortcode_Donate_Box::register();
});

// Localize forms data for Gutenberg block editor
add_action('enqueue_block_editor_assets', function () {
    $forms = WBCD\Settings::get_forms_for_dropdown();
    $form_options = [['value' => '', 'label' => __('Default (First form)', 'wp-beacon-crm-donate')]];

    foreach ($forms as $name => $label) {
        $form_options[] = ['value' => $name, 'label' => $label];
    }

    wp_localize_script('wp-blocks', 'wbcdForms', $form_options);
});

// Settings page + admin notices
add_action('admin_menu',  ['WBCD\\Settings', 'add_menu']);
add_action('admin_init',  ['WBCD\\Settings', 'register']);
add_action('admin_enqueue_scripts', ['WBCD\\Settings', 'enqueue_admin_assets']);
add_action('admin_notices', ['WBCD\\GeoIP_Dependency', 'admin_notices']);
add_action('admin_footer', ['WBCD\\GeoIP_Dependency', 'enqueue_dismiss_script']);
add_action('wp_ajax_wbcd_dismiss_geoip_notice', ['WBCD\\GeoIP_Dependency', 'dismiss_notice']);

// Elementor Widgets
add_action('elementor/widgets/register', function ($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    require_once WPBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-form.php';
    require_once WPBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-box.php';
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Form_Widget());
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Box_Widget());
});

// Divi Modules
add_action('et_builder_ready', function () {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }
    require_once WPBCD_PATH . 'integrations/divi/class-divi-module-donate-form.php';
    require_once WPBCD_PATH . 'integrations/divi/class-divi-module-donate-box.php';
    new \WBCD\Integrations\Divi\Donate_Form_Module();
    new \WBCD\Integrations\Divi\Donate_Box_Module();
});
