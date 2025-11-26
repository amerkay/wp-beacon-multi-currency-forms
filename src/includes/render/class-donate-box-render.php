<?php

namespace WBCD\Render;

if (!defined('ABSPATH'))
    exit;

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
            'title' => __('Make a donation', 'wp-beacon-multi-currency-forms'),
            'subtitle' => __('Pick your currency, frequency, and amount', 'wp-beacon-multi-currency-forms'),
            'noticeText' => __("You'll be taken to our secure donation form to complete your gift.", 'wp-beacon-multi-currency-forms'),
            'buttonText' => __('Donate now →', 'wp-beacon-multi-currency-forms'),
            'targetPageUrl' => '', // Optional target page URL
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
            return '<div class="wpbmcf-wrap"><p>' . esc_html__('Please configure donation forms in the Beacon Multi-Currency Forms settings.', 'wp-beacon-multi-currency-forms') . '</p></div>';
        }

        // Build inline style for custom colors
        $inline_style = '';
        if (!empty($args['brandColor'])) {
            $inline_style .= '--wpbmcf-brand: ' . esc_attr($args['brandColor']) . '; ';
        }
        if (!empty($args['primaryColor'])) {
            $inline_style .= '--wpbmcf-primary: ' . esc_attr($args['primaryColor']) . '; ';
        }

        // Prepare custom params for JavaScript
        $custom_params_json = !empty($args['customParams']) ? wp_json_encode($args['customParams']) : '{}';

        // Prepare allowed frequencies for JavaScript
        $allowed_frequencies = !empty($args['allowedFrequencies']) ? $args['allowedFrequencies'] : ['single', 'monthly', 'annual'];
        $allowed_frequencies_json = wp_json_encode($allowed_frequencies);

        // Prepare default presets for JavaScript
        $default_presets = !empty($args['defaultPresets']) ? $args['defaultPresets'] : \WBCD\Constants::get_all_presets();
        $default_presets_json = wp_json_encode($default_presets);

        // Enqueue assets with target page URL
        \WBCD\Assets::enqueue_donation_box($form_name, $args['targetPageUrl']);

        ob_start();
        ?>
        <div id="wpbmcf-donate" class="wpbmcf-wrap" style="<?php echo esc_attr($inline_style); ?>"
            data-custom-params="<?php echo esc_attr($custom_params_json); ?>"
            data-allowed-frequencies="<?php echo esc_attr($allowed_frequencies_json); ?>"
            data-default-presets="<?php echo esc_attr($default_presets_json); ?>">
            <div class="wpbmcf-card">
                <header class="wpbmcf-header">
                    <h3 class="wpbmcf-title"><?php echo esc_html($args['title']); ?></h3>
                    <p class="wpbmcf-sub"><?php echo esc_html($args['subtitle']); ?></p>
                </header>

                <div class="wpbmcf-section">
                    <div class="wpbmcf-tabs wpbmcf-tabs--row" role="tablist"
                        aria-label="<?php esc_attr_e('Donation frequency', 'wp-beacon-multi-currency-forms'); ?>">
                        <button class="wpbmcf-tab wpbmcf-btn-frequency" data-frequency="single" type="button"
                            aria-selected="false"><?php esc_html_e('Single', 'wp-beacon-multi-currency-forms'); ?></button>
                        <button class="wpbmcf-tab wpbmcf-btn-frequency" data-frequency="monthly" type="button"
                            aria-selected="true"><?php esc_html_e('Monthly', 'wp-beacon-multi-currency-forms'); ?></button>
                        <button class="wpbmcf-tab wpbmcf-btn-frequency" data-frequency="annual" type="button"
                            aria-selected="false"><?php esc_html_e('Annual', 'wp-beacon-multi-currency-forms'); ?></button>
                    </div>
                </div>

                <div class="wpbmcf-amount">
                    <div class="wpbmcf-amount-header">
                        <div class="wpbmcf-amount-label"><?php esc_html_e('Amount', 'wp-beacon-multi-currency-forms'); ?></div>
                        <label class="wpbmcf-currency-select-label" for="wpbmcf-currency-select"
                            aria-label="<?php esc_attr_e('Currency', 'wp-beacon-multi-currency-forms'); ?>"></label>
                        <select id="wpbmcf-currency-select" class="wpbmcf-select"
                            aria-label="<?php esc_attr_e('Currency', 'wp-beacon-multi-currency-forms'); ?>">
                            <?php foreach ($currencies as $code => $form_id):
                                $symbol = isset($symbols[$code]) ? $symbols[$code] : '';
                                $display = $symbol ? sprintf('%s %s', $code, $symbol) : $code;
                                ?>
                                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="wpbmcf-tabs wpbmcf-tabs--grid" id="wpbmcf-amount-buttons" role="group"
                        aria-label="<?php esc_attr_e('Preset amounts', 'wp-beacon-multi-currency-forms'); ?>">
                        <!-- JS fills preset buttons -->
                    </div>

                    <div class="wpbmcf-amount-custom">
                        <button id="wpbmcf-toggle-custom" class="wpbmcf-link" type="button" aria-expanded="false"
                            aria-controls="wpbmcf-custom-wrap"><?php esc_html_e('Custom amount', 'wp-beacon-multi-currency-forms'); ?></button>
                        <div id="wpbmcf-custom-wrap" class="wpbmcf-input-wrap" hidden>
                            <span id="wpbmcf-currency-symbol" aria-hidden="true"><?php
                            // Use first currency's symbol as default
                            $first_code = array_key_first($currencies);
                            echo esc_html(isset($symbols[$first_code]) ? $symbols[$first_code] : $first_code);
                            ?></span>
                            <input id="wpbmcf-custom-amount" type="number" min="1" step="1" inputmode="decimal"
                                placeholder="0" />
                        </div>
                    </div>
                </div>

                <div class="wpbmcf-actions">
                    <button id="wpbmcf-next" class="wpbmcf-button wpbmcf-button--md" type="button"
                        aria-label="<?php esc_attr_e('Continue to secure form', 'wp-beacon-multi-currency-forms'); ?>"
                        disabled><?php echo esc_html($args['buttonText']); ?></button>
                    <div class="wpbmcf-note"><?php echo esc_html($args['noticeText']); ?></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
