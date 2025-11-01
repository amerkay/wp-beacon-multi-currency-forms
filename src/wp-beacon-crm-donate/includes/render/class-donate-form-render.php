<?php

namespace WBCD\Render;

if (! defined('ABSPATH')) exit;

class Donate_Form_Render
{

    public static function render()
    {
        // Render a minimal, accessible shell; JS fills in the Beacon form and currency behavior.
        ob_start();
?>
        <div id="wpbcd-page" class="wpbcd-wrap">
            <div class="wpbcd-toolbar" role="region" aria-label="<?php esc_attr_e('Donation settings', 'wp-beacon-crm-donate'); ?>">
                <label for="wpbcd-currency" class="wpbcd-label"><?php esc_html_e('Currency', 'wp-beacon-crm-donate'); ?></label>
                <select id="wpbcd-currency" class="wpbcd-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>">
                    <option value="GBP">GBP £</option>
                    <option value="EUR">EUR €</option>
                    <option value="USD">USD $</option>
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
