<?php

namespace WBCD\Render;

if (! defined('ABSPATH')) exit;

class Donate_CTA_Render
{

    public static function render()
    {
        ob_start();
?>
        <div id="wpbcd-donate" class="wpbcd-wrap">
            <div class="wpbcd-card">
                <header class="wpbcd-header">
                    <h3 class="wpbcd-title"><?php esc_html_e('Make a donation', 'wp-beacon-crm-donate'); ?></h3>
                    <p class="wpbcd-sub"><?php esc_html_e('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'); ?></p>
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
                            <option value="GBP">GBP £</option>
                            <option value="EUR">EUR €</option>
                            <option value="USD">USD $</option>
                        </select>
                    </div>

                    <div class="wpbcd-tabs wpbcd-tabs--grid" id="wpbcd-amount-buttons" role="group" aria-label="<?php esc_attr_e('Preset amounts', 'wp-beacon-crm-donate'); ?>">
                        <!-- JS fills preset buttons -->
                    </div>

                    <div class="wpbcd-amount-custom">
                        <button id="wpbcd-toggle-custom" class="wpbcd-link" type="button" aria-expanded="false" aria-controls="wpbcd-custom-wrap"><?php esc_html_e('Custom amount', 'wp-beacon-crm-donate'); ?></button>
                        <div id="wpbcd-custom-wrap" class="wpbcd-input-wrap" hidden style="display:none;">
                            <span id="wpbcd-currency-symbol" aria-hidden="true">£</span>
                            <input id="wpbcd-custom-amount" type="number" min="1" step="1" inputmode="decimal" placeholder="0" />
                        </div>
                    </div>
                </div>

                <div class="wpbcd-actions">
                    <button id="wpbcd-next" class="wpbcd-btn wpbcd-btn-next" type="button" aria-label="<?php esc_attr_e('Continue to secure form', 'wp-beacon-crm-donate'); ?>" disabled><?php esc_html_e('Donate now →', 'wp-beacon-crm-donate'); ?></button>
                    <div class="wpbcd-note"><?php esc_html_e('You’ll be taken to our secure donation form to complete your gift.', 'wp-beacon-crm-donate'); ?></div>
                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
