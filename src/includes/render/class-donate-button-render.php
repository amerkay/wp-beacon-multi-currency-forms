<?php

namespace WBCD\Render;

if (! defined('ABSPATH')) exit;

class Donate_Button_Render
{

    public static function render($form_name = '', $args = [])
    {
        // Parse arguments with defaults
        $args = wp_parse_args($args, [
            'color' => '',
            'text' => __('Donate', 'wp-beacon-crm-donate'),
            'size' => \WBCD\Constants::get_default_button_size(),
            'amount' => '',
            'frequency' => '',
            'currency' => '',
            'customParams' => [],
        ]);

        // Validate size
        $valid_sizes = \WBCD\Constants::get_valid_button_sizes();
        $size = in_array($args['size'], $valid_sizes, true) ? $args['size'] : \WBCD\Constants::get_default_button_size();

        // Get the target URL from settings
        $base_url = \WBCD\Settings::get_target_page_url($form_name);

        // Build URL with donation-specific params and custom params
        $params = [];

        // Add currency if specified
        if (!empty($args['currency'])) {
            $params['currency'] = $args['currency'];
        }

        // Add frequency if specified
        if (!empty($args['frequency'])) {
            $valid_frequencies = ['single', 'monthly', 'annual'];
            if (in_array($args['frequency'], $valid_frequencies, true)) {
                $params['bcn_donation_frequency'] = $args['frequency'];
            }
        }

        // Add amount if specified
        if (!empty($args['amount']) && is_numeric($args['amount']) && $args['amount'] > 0) {
            $params['bcn_donation_amount'] = $args['amount'];
        }

        // Add custom params
        if (!empty($args['customParams']) && is_array($args['customParams'])) {
            foreach ($args['customParams'] as $key => $value) {
                if (!empty($key)) {
                    $params[$key] = $value;
                }
            }
        }

        // Build final URL
        $url = $base_url;
        if (!empty($params)) {
            $query_string = http_build_query($params);
            $url .= (strpos($base_url, '?') !== false ? '&' : '?') . $query_string;
        }

        // Build inline style for custom color
        $inline_style = '';
        if (!empty($args['color'])) {
            $inline_style = 'style="--wpbcd-button-color: ' . esc_attr($args['color']) . ';"';
        }

        // Build button classes
        $button_classes = 'wpbcd-button wpbcd-button--' . esc_attr($size);

        ob_start();
?>
        <div class="wpbcd-button-wrap">
            <a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr($button_classes); ?>" <?php echo $inline_style; // phpcs:ignore WordPress.Security.EscapeOutput 
                                                                                                    ?>>
                <?php echo esc_html($args['text']); ?>
            </a>
        </div>
<?php
        return ob_get_clean();
    }
}
