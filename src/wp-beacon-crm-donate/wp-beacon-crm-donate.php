<?php

/**
 * Plugin Name:  Beacon CRM Donate (Ultra-Lean)
 * Description:  Two Beacon donation blocks (full page form + CTA box) with shortcodes and Elementor/Divi adapters. No bundlers; DRY PHP renderers; simple Settings.
 * Version:      1.0.0
 * Author:       Pangea Trust / Amer Kawar
 * Text Domain:  wp-beacon-crm-donate
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) exit;

define('WBCD_VERSION',        '1.0.0');
define('WBCD_FILE',           __FILE__);
define('WBCD_PATH',           plugin_dir_path(__FILE__));
define('WBCD_URL',            plugin_dir_url(__FILE__));
define('WBCD_BEACON_ACCOUNT', 'pangeatrust'); // keep the tested account (requirement: do not add new features)

// --- Includes (manual, ultra-lean) ---
require_once WBCD_PATH . 'includes/class-settings.php';
require_once WBCD_PATH . 'includes/class-assets.php';
require_once WBCD_PATH . 'includes/class-geoip-dependency.php';

require_once WBCD_PATH . 'includes/render/class-donate-form-render.php';
require_once WBCD_PATH . 'includes/render/class-donate-cta-render.php';

require_once WBCD_PATH . 'includes/shortcodes/class-shortcode-donate-form.php';
require_once WBCD_PATH . 'includes/shortcodes/class-shortcode-donate-box.php';

// Elementor & Divi adapters are loaded conditionally in their respective hooks below.

// --- Bootstrap ---
add_action('plugins_loaded', function () {
    load_plugin_textdomain('wp-beacon-crm-donate', false, dirname(plugin_basename(WBCD_FILE)) . '/languages');
});

add_action('init', function () {

    // Register style handle early so block.json can reference it
    wp_register_style(
        'wbcd-front',
        WBCD_URL . 'public/css/donate.css',
        [],
        WBCD_VERSION
    );

    // Register dynamic blocks from metadata
    register_block_type_from_metadata(
        WBCD_PATH . 'blocks/donation-form',
        array(
            'render_callback' => function ($attrs, $content) {
                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                WBCD\Assets::enqueue_donation_form();
                // Call the render method
                return WBCD\Render\Donate_Form_Render::render();
            },
        )
    );

    register_block_type_from_metadata(
        WBCD_PATH . 'blocks/donation-box',
        array(
            'render_callback' => function ($attrs, $content) {
                // Enqueue assets before rendering
                wp_enqueue_style('wbcd-front');
                WBCD\Assets::enqueue_donation_cta();
                // Call the render method
                return WBCD\Render\Donate_CTA_Render::render();
            },
        )
    );

    // Shortcodes
    WBCD\Shortcodes\Shortcode_Donate_Form::register();
    WBCD\Shortcodes\Shortcode_Donate_Box::register();
});

// Settings page + admin notices
add_action('admin_menu',  ['WBCD\\Settings', 'add_menu']);
add_action('admin_init',  ['WBCD\\Settings', 'register']);
add_action('admin_notices', ['WBCD\\GeoIP_Dependency', 'admin_notices']);

// Elementor Widgets
add_action('elementor/widgets/register', function ($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    require_once WBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-form.php';
    require_once WBCD_PATH . 'integrations/elementor/class-elementor-widget-donate-box.php';
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Form_Widget());
    $widgets_manager->register(new \WBCD\Integrations\Elementor\Donate_Box_Widget());
});

// Divi Modules
add_action('et_builder_ready', function () {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }
    require_once WBCD_PATH . 'integrations/divi/class-divi-module-donate-form.php';
    require_once WBCD_PATH . 'integrations/divi/class-divi-module-donate-box.php';
    new \WBCD\Integrations\Divi\Donate_Form_Module();
    new \WBCD\Integrations\Divi\Donate_Box_Module();
});
