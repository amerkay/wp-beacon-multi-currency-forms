<?php

namespace WBCD\Render;

if (! defined('ABSPATH')) exit;

class Donate_CTA_Render
{

    public static function render()
    {
        ob_start();
?>
        <div id="pangea-donate" class="pgt-wrap">
            <div class="pgt-card">
                <header class="pgt-header">
                    <h3 class="pgt-title"><?php esc_html_e('Make a donation', 'wp-beacon-crm-donate'); ?></h3>
                    <p class="pgt-sub"><?php esc_html_e('Pick your currency, frequency, and amount', 'wp-beacon-crm-donate'); ?></p>
                </header>

                <div class="pgt-section">
                    <div class="pgt-tabs pgt-tabs--row" role="tablist" aria-label="<?php esc_attr_e('Donation frequency', 'wp-beacon-crm-donate'); ?>">
                        <button class="pgt-tab pgt-btn-frequency" data-frequency="single" type="button" aria-selected="false"><?php esc_html_e('Single', 'wp-beacon-crm-donate'); ?></button>
                        <button class="pgt-tab pgt-btn-frequency" data-frequency="monthly" type="button" aria-selected="true"><?php esc_html_e('Monthly', 'wp-beacon-crm-donate'); ?></button>
                        <button class="pgt-tab pgt-btn-frequency" data-frequency="annual" type="button" aria-selected="false"><?php esc_html_e('Annual', 'wp-beacon-crm-donate'); ?></button>
                    </div>
                </div>

                <div class="pgt-amount">
                    <div class="pgt-amount-header">
                        <div class="pgt-amount-label"><?php esc_html_e('Amount', 'wp-beacon-crm-donate'); ?></div>
                        <label class="pgt-currency-select-label" for="pgt-currency-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>"></label>
                        <select id="pgt-currency-select" class="pgt-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>">
                            <option value="GBP">GBP £</option>
                            <option value="EUR">EUR €</option>
                            <option value="USD">USD $</option>
                        </select>
                    </div>

                    <div class="pgt-tabs pgt-tabs--grid" id="pgt-amount-buttons" role="group" aria-label="<?php esc_attr_e('Preset amounts', 'wp-beacon-crm-donate'); ?>">
                        <!-- JS fills preset buttons -->
                    </div>

                    <div class="pgt-amount-custom">
                        <button id="pgt-toggle-custom" class="pgt-link" type="button" aria-expanded="false" aria-controls="pgt-custom-wrap"><?php esc_html_e('Custom amount', 'wp-beacon-crm-donate'); ?></button>
                        <div id="pgt-custom-wrap" class="pgt-input-wrap" hidden style="display:none;">
                            <span id="pgt-currency-symbol" aria-hidden="true">£</span>
                            <input id="pgt-custom-amount" type="number" min="1" step="1" inputmode="decimal" placeholder="0" />
                        </div>
                    </div>
                </div>

                <div class="pgt-actions">
                    <button id="pgt-next" class="pgt-btn pgt-btn-next" type="button" aria-label="<?php esc_attr_e('Continue to secure form', 'wp-beacon-crm-donate'); ?>" disabled><?php esc_html_e('Donate now →', 'wp-beacon-crm-donate'); ?></button>
                    <div class="pgt-note"><?php esc_html_e('You’ll be taken to our secure donation form to complete your gift.', 'wp-beacon-crm-donate'); ?></div>
                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
