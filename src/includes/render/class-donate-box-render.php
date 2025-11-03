<?php

namespace WBCD\Render;

if (! defined('ABSPATH')) exit;

class Donate_Box_Render
{

    public static function render($form_name = '', $args = [])
    {
        // Parse arguments with defaults
        $args = wp_parse_args($args, [
            'primaryColor' => '',
            'brandColor' => '',
            'customParams' => [],
            'allowedFrequencies' => \WBCD\Constants::get_default_frequencies(),
            'defaultPresets' => \WBCD\Constants::get_all_presets(),
            'title' => __('Make a donation', 'wp-beacon-crm-donate'),
            'subtitle' => __('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'),
            'noticeText' => __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-crm-donate'),
            'buttonText' => __('Donate now →', 'wp-beacon-crm-donate'),
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

        // Build inline style for custom colors
        $inline_style = '';
        if (!empty($args['brandColor'])) {
            $inline_style .= '--wpbcd-brand: ' . esc_attr($args['brandColor']) . '; ';
        }
        if (!empty($args['primaryColor'])) {
            $inline_style .= '--wpbcd-primary: ' . esc_attr($args['primaryColor']) . '; ';
        }

        // Prepare custom params for JavaScript
        $custom_params_json = !empty($args['customParams']) ? wp_json_encode($args['customParams']) : '{}';

        // Prepare allowed frequencies for JavaScript
        $allowed_frequencies = !empty($args['allowedFrequencies']) ? $args['allowedFrequencies'] : ['single', 'monthly', 'annual'];
        $allowed_frequencies_json = wp_json_encode($allowed_frequencies);

        // Prepare default presets for JavaScript
        $default_presets = !empty($args['defaultPresets']) ? $args['defaultPresets'] : \WBCD\Constants::get_all_presets();
        $default_presets_json = wp_json_encode($default_presets);

        ob_start();
?>
        <div id="wpbcd-donate" class="wpbcd-wrap" style="<?php echo esc_attr($inline_style); ?>"
            data-custom-params="<?php echo esc_attr($custom_params_json); ?>"
            data-allowed-frequencies="<?php echo esc_attr($allowed_frequencies_json); ?>"
            data-default-presets="<?php echo esc_attr($default_presets_json); ?>">
            <div class="wpbcd-card">
                <header class="wpbcd-header">
                    <h3 class="wpbcd-title"><?php echo esc_html($args['title']); ?></h3>
                    <p class="wpbcd-sub"><?php echo esc_html($args['subtitle']); ?></p>
                </header>

                <div class="wpbcd-section">
                    <div class="wpbcd-tabs wpbcd-tabs--row" role="tablist" aria-label="<?php esc_attr_e('Donation frequency', 'wp-beacon-crm-donate'); ?>">
                        <button class="wpbcd-tab wpbcd-btn-frequency" data-frequency="single" type="button" aria-selected="false"><?php esc_html_e('Single', 'wp-beacon-crm-donate'); ?></button>
                        <button class="wpbcd-tab wpbcd-btn-frequency" data-frequency="monthly" type="button" aria-selected="true"><?php esc_html_e('Monthly', 'wp-beacon-crm-donate'); ?></button>
                        <button class="wpbcd-tab wpbcd-btn-frequency" data-frequency="annual" type="button" aria-selected="false"><?php esc_html_e('Annual', 'wp-beacon-crm-donate'); ?></button>
                    </div>
                </div>

                <div class="wpbcd-amount">
                    <div class="wpbcd-amount-header">
                        <div class="wpbcd-amount-label"><?php esc_html_e('Amount', 'wp-beacon-crm-donate'); ?></div>
                        <label class="wpbcd-currency-select-label" for="wpbcd-currency-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>"></label>
                        <select id="wpbcd-currency-select" class="wpbcd-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>">
                            <?php foreach ($currencies as $code => $form_id):
                                $symbol = isset($symbols[$code]) ? $symbols[$code] : '';
                                $display = $symbol ? sprintf('%s %s', $code, $symbol) : $code;
                            ?>
                                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="wpbcd-tabs wpbcd-tabs--grid" id="wpbcd-amount-buttons" role="group" aria-label="<?php esc_attr_e('Preset amounts', 'wp-beacon-crm-donate'); ?>">
                        <!-- JS fills preset buttons -->
                    </div>

                    <div class="wpbcd-amount-custom">
                        <button id="wpbcd-toggle-custom" class="wpbcd-link" type="button" aria-expanded="false" aria-controls="wpbcd-custom-wrap"><?php esc_html_e('Custom amount', 'wp-beacon-crm-donate'); ?></button>
                        <div id="wpbcd-custom-wrap" class="wpbcd-input-wrap" hidden>
                            <span id="wpbcd-currency-symbol" aria-hidden="true"><?php
                                                                                // Use first currency's symbol as default
                                                                                $first_code = array_key_first($currencies);
                                                                                echo esc_html(isset($symbols[$first_code]) ? $symbols[$first_code] : $first_code);
                                                                                ?></span>
                            <input id="wpbcd-custom-amount" type="number" min="1" step="1" inputmode="decimal" placeholder="0" />
                        </div>
                    </div>
                </div>

                <div class="wpbcd-actions">
                    <button id="wpbcd-next" class="wpbcd-button wpbcd-button--md" type="button" aria-label="<?php esc_attr_e('Continue to secure form', 'wp-beacon-crm-donate'); ?>" disabled><?php echo esc_html($args['buttonText']); ?></button>
                    <div class="wpbcd-note"><?php echo esc_html($args['noticeText']); ?></div>
                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
