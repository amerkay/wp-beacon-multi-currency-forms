<?php

namespace WBCD\Render;

if (! defined('ABSPATH')) exit;

class Donate_Form_Render
{

    public static function render($form_name = '', $args = [])
    {
        // Parse arguments with defaults
        $args = wp_parse_args($args, [
            'customParams' => [],
        ]);

        $currencies = \WBCD\Settings::get_forms_by_currency($form_name);
        $symbols = \WBCD\Settings::get_currency_symbols();

        // If no currencies configured, show a message
        if (empty($currencies)) {
            if (!empty($form_name)) {
                return '<div class="wpbcd-wrap"><p>' .
                    sprintf(
                        esc_html__('Form "%s" not found or has no currencies configured.', 'wp-beacon-crm-donate'),
                        esc_html($form_name)
                    ) .
                    '</p></div>';
            }
            return '<div class="wpbcd-wrap"><p>' . esc_html__('Please configure donation forms in the Beacon Donate settings.', 'wp-beacon-crm-donate') . '</p></div>';
        }

        // Prepare custom params for JavaScript redirect check
        $custom_params_json = !empty($args['customParams']) ? wp_json_encode($args['customParams']) : '{}';

        // Render a minimal, accessible shell; JS fills in the Beacon form and currency behavior.
        ob_start();
?>
        <?php if (!empty($args['customParams'])): ?>
            <script>
                (function() {
                    try {
                        var requiredParams = <?php echo $custom_params_json; ?>;
                        if (requiredParams && typeof requiredParams === 'object' && !Array.isArray(requiredParams)) {
                            var currentUrl = new URL(window.location.href);
                            var currentParams = new URLSearchParams(currentUrl.search);
                            var missing = [];

                            // Check each required parameter
                            for (var key in requiredParams) {
                                if (requiredParams.hasOwnProperty(key)) {
                                    var currentValue = currentParams.get(key);
                                    var requiredValue = String(requiredParams[key]);

                                    // If parameter is missing or doesn't match required value
                                    if (!currentValue || currentValue !== requiredValue) {
                                        missing.push(key);
                                        currentParams.set(key, requiredValue);
                                    }
                                }
                            }

                            // If any parameters are missing or incorrect, redirect
                            if (missing.length > 0) {
                                currentUrl.search = currentParams.toString();
                                window.location.href = currentUrl.toString();
                            }
                        }
                    } catch (e) {
                        console.warn('WPBCD: Error checking required parameters', e);
                    }
                })();
            </script>
        <?php endif; ?>
        <div id="wpbcd-page" class="wpbcd-wrap" data-custom-params="<?php echo esc_attr($custom_params_json); ?>">
            <div class="wpbcd-toolbar" role="region" aria-label="<?php esc_attr_e('Donation settings', 'wp-beacon-crm-donate'); ?>">
                <label for="wpbcd-currency" class="wpbcd-label"><?php esc_html_e('Currency', 'wp-beacon-crm-donate'); ?></label>
                <select id="wpbcd-currency" class="wpbcd-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>">
                    <?php foreach ($currencies as $code => $form_id):
                        $symbol = isset($symbols[$code]) ? $symbols[$code] : '';
                        $display = $symbol ? sprintf('%s %s', $code, $symbol) : $code;
                    ?>
                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="wpbcd-form-wrap" class="wpbcd-form-wrap">
                <div id="wpbcd-beacon-slot"></div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
