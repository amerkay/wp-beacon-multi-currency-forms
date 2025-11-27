<?php

namespace BMCF\Render;

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

        $currencies = \BMCF\Settings::get_forms_by_currency($form_name);
        $symbols = \BMCF\Settings::get_currency_symbols();

        // If no currencies configured, show a message
        if (empty($currencies)) {
            if (!empty($form_name)) {
                return '<div class="bmcf-wrap"><p>' .
                    sprintf(
                        /* translators: %s: Form name */
                        esc_html__('Form "%s" not found or has no currencies configured.', 'beacon-multi-currency-forms'),
                        esc_html($form_name)
                    ) .
                    '</p></div>';
            }
            return '<div class="bmcf-wrap"><p>' . esc_html__('Please configure donation forms in the Beacon Multi-Currency Forms settings.', 'beacon-multi-currency-forms') . '</p></div>';
        }

        // Prepare custom params for JavaScript (handled by donate-form.js)
        $custom_params_json = !empty($args['customParams']) ? wp_json_encode($args['customParams']) : '{}';

        // Render a minimal, accessible shell; JS fills in the Beacon form and currency behavior.
        ob_start();
        ?>
        <div id="bmcf-page" class="bmcf-wrap" data-custom-params="<?php echo esc_attr($custom_params_json); ?>"
            data-default-frequency="<?php echo esc_attr($args['defaultFrequency']); ?>"
            data-default-amount="<?php echo esc_attr($args['defaultAmount']); ?>">
            <div class="bmcf-toolbar" role="region"
                aria-label="<?php esc_attr_e('Donation settings', 'beacon-multi-currency-forms'); ?>">
                <label for="bmcf-currency"
                    class="bmcf-label"><?php esc_html_e('Currency', 'beacon-multi-currency-forms'); ?></label>
                <select id="bmcf-currency" class="bmcf-select"
                    aria-label="<?php esc_attr_e('Currency', 'beacon-multi-currency-forms'); ?>">
                    <?php foreach ($currencies as $code => $form_id):
                        $symbol = isset($symbols[$code]) ? $symbols[$code] : '';
                        $display = $symbol ? sprintf('%s %s', $code, $symbol) : $code;
                        ?>
                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="bmcf-form-wrap" class="bmcf-form-wrap">
                <div id="bmcf-beacon-slot"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
