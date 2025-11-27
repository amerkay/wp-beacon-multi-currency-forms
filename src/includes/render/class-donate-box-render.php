<?php

namespace BMCF\Render;

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
            'allowedFrequencies' => \BMCF\Constants::get_default_frequencies(),
            'defaultPresets' => \BMCF\Constants::get_all_presets(),
            'title' => __('Make a donation', 'beacon-multi-currency-forms'),
            'subtitle' => __('Pick your currency, frequency, and amount', 'beacon-multi-currency-forms'),
            'noticeText' => __("You'll be taken to our secure donation form to complete your gift.", 'beacon-multi-currency-forms'),
            'buttonText' => __('Donate now →', 'beacon-multi-currency-forms'),
            'targetPageUrl' => '', // Optional target page URL
        ]);

        // Validate that targetPageUrl is set
        if (empty($args['targetPageUrl'])) {
            return '<div class="bmcf-wrap"><p class="bmcf-error">' .
                esc_html__('Error: No target page selected for the donation box. Please select a page from the Target Page dropdown in the block settings.', 'beacon-multi-currency-forms') .
                '</p></div>';
        }

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

        // Build inline style for custom colors
        $inline_style = '';
        if (!empty($args['brandColor'])) {
            $inline_style .= '--bmcf-brand: ' . esc_attr($args['brandColor']) . '; ';
        }
        if (!empty($args['primaryColor'])) {
            $inline_style .= '--bmcf-primary: ' . esc_attr($args['primaryColor']) . '; ';
        }

        // Prepare custom params for JavaScript
        $custom_params_json = !empty($args['customParams']) ? wp_json_encode($args['customParams']) : '{}';

        // Prepare allowed frequencies for JavaScript
        $allowed_frequencies = !empty($args['allowedFrequencies']) ? $args['allowedFrequencies'] : ['single', 'monthly', 'annual'];
        $allowed_frequencies_json = wp_json_encode($allowed_frequencies);

        // Prepare default presets for JavaScript
        $default_presets = !empty($args['defaultPresets']) ? $args['defaultPresets'] : \BMCF\Constants::get_all_presets();
        $default_presets_json = wp_json_encode($default_presets);

        // Enqueue assets with target page URL
        \BMCF\Assets::enqueue_donation_box($form_name, $args['targetPageUrl']);

        ob_start();
        ?>
        <div id="bmcf-donate" class="bmcf-wrap" style="<?php echo esc_attr($inline_style); ?>"
            data-custom-params="<?php echo esc_attr($custom_params_json); ?>"
            data-allowed-frequencies="<?php echo esc_attr($allowed_frequencies_json); ?>"
            data-default-presets="<?php echo esc_attr($default_presets_json); ?>">
            <div class="bmcf-card">
                <header class="bmcf-header">
                    <h3 class="bmcf-title"><?php echo esc_html($args['title']); ?></h3>
                    <p class="bmcf-sub"><?php echo esc_html($args['subtitle']); ?></p>
                </header>

                <div class="bmcf-section">
                    <div class="bmcf-tabs bmcf-tabs--row" role="tablist"
                        aria-label="<?php esc_attr_e('Donation frequency', 'beacon-multi-currency-forms'); ?>">
                        <button class="bmcf-tab bmcf-btn-frequency" data-frequency="single" type="button"
                            aria-selected="false"><?php esc_html_e('Single', 'beacon-multi-currency-forms'); ?></button>
                        <button class="bmcf-tab bmcf-btn-frequency" data-frequency="monthly" type="button"
                            aria-selected="true"><?php esc_html_e('Monthly', 'beacon-multi-currency-forms'); ?></button>
                        <button class="bmcf-tab bmcf-btn-frequency" data-frequency="annual" type="button"
                            aria-selected="false"><?php esc_html_e('Annual', 'beacon-multi-currency-forms'); ?></button>
                    </div>
                </div>

                <div class="bmcf-amount">
                    <div class="bmcf-amount-header">
                        <div class="bmcf-amount-label"><?php esc_html_e('Amount', 'beacon-multi-currency-forms'); ?></div>
                        <label class="bmcf-currency-select-label" for="bmcf-currency-select"
                            aria-label="<?php esc_attr_e('Currency', 'beacon-multi-currency-forms'); ?>"></label>
                        <select id="bmcf-currency-select" class="bmcf-select"
                            aria-label="<?php esc_attr_e('Currency', 'beacon-multi-currency-forms'); ?>">
                            <?php foreach ($currencies as $code => $form_id):
                                $symbol = isset($symbols[$code]) ? $symbols[$code] : '';
                                $display = $symbol ? sprintf('%s %s', $code, $symbol) : $code;
                                ?>
                                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bmcf-tabs bmcf-tabs--grid" id="bmcf-amount-buttons" role="group"
                        aria-label="<?php esc_attr_e('Preset amounts', 'beacon-multi-currency-forms'); ?>">
                        <!-- JS fills preset buttons -->
                    </div>

                    <div class="bmcf-amount-custom">
                        <button id="bmcf-toggle-custom" class="bmcf-link" type="button" aria-expanded="false"
                            aria-controls="bmcf-custom-wrap"><?php esc_html_e('Custom amount', 'beacon-multi-currency-forms'); ?></button>
                        <div id="bmcf-custom-wrap" class="bmcf-input-wrap" hidden>
                            <span id="bmcf-currency-symbol" aria-hidden="true"><?php
                            // Use first currency's symbol as default
                            $first_code = array_key_first($currencies);
                            echo esc_html(isset($symbols[$first_code]) ? $symbols[$first_code] : $first_code);
                            ?></span>
                            <input id="bmcf-custom-amount" type="number" min="1" step="1" inputmode="decimal" placeholder="0" />
                        </div>
                    </div>
                </div>

                <div class="bmcf-actions">
                    <button id="bmcf-next" class="bmcf-button bmcf-button--md" type="button"
                        aria-label="<?php esc_attr_e('Continue to secure form', 'beacon-multi-currency-forms'); ?>"
                        disabled><?php echo esc_html($args['buttonText']); ?></button>
                    <div class="bmcf-note"><?php echo esc_html($args['noticeText']); ?></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
