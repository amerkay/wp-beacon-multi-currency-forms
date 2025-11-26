<?php

namespace WBCD\Render;

if (!defined('ABSPATH'))
    exit;

class Donate_Form_Render
{

    public static function render($form_name = '', $args = [])
    {
        // Parse arguments with defaults
        $args = wp_parse_args($args, [
            'customParams' => [],
            'defaultFrequency' => '',
            'defaultAmount' => '',
        ]);

        $currencies = \WBCD\Settings::get_forms_by_currency($form_name);
        $symbols = \WBCD\Settings::get_currency_symbols();

        // If no currencies configured, show a message
        if (empty($currencies)) {
            if (!empty($form_name)) {
                return '<div class="wpbmcf-wrap"><p>' .
                    sprintf(
                        esc_html__('Form "%s" not found or has no currencies configured.', 'wp-beacon-multi-currency-forms'),
                        esc_html($form_name)
                    ) .
                    '</p></div>';
            }
            return '<div class="wpbmcf-wrap"><p>' . esc_html__('Please configure donation forms in the Beacon Donate settings.', 'wp-beacon-multi-currency-forms') . '</p></div>';
        }

        // Prepare custom params for JavaScript (handled by donate-form.js)
        $custom_params_json = !empty($args['customParams']) ? wp_json_encode($args['customParams']) : '{}';

        // Render a minimal, accessible shell; JS fills in the Beacon form and currency behavior.
        ob_start();
        ?>
                <div id="wpbmcf-page" class="wpbmcf-wrap" data-custom-params="<?php echo esc_attr($custom_params_json); ?>"
                    data-default-frequency="<?php echo esc_attr($args['defaultFrequency']); ?>"
                    data-default-amount="<?php echo esc_attr($args['defaultAmount']); ?>">
                    <div class="wpbmcf-toolbar" role="region"
                        aria-label="<?php esc_attr_e('Donation settings', 'wp-beacon-multi-currency-forms'); ?>">
                        <label for="wpbmcf-currency"
                            class="wpbmcf-label"><?php esc_html_e('Currency', 'wp-beacon-multi-currency-forms'); ?></label>
                        <select id="wpbmcf-currency" class="wpbmcf-select"
                            aria-label="<?php esc_attr_e('Currency', 'wp-beacon-multi-currency-forms'); ?>">
                            <?php foreach ($currencies as $code => $form_id):
                                $symbol = isset($symbols[$code]) ? $symbols[$code] : '';
                                $display = $symbol ? sprintf('%s %s', $code, $symbol) : $code;
                                ?>
                                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="wpbmcf-form-wrap" class="wpbmcf-form-wrap">
                        <div id="wpbmcf-beacon-slot"></div>
                    </div>
                </div>
                <?php
                return ob_get_clean();
    }
}
