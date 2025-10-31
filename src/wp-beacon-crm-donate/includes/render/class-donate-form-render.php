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
        <div id="pgt-page" class="pgt-wrap">
            <div class="pgt-toolbar" role="region" aria-label="<?php esc_attr_e('Donation settings', 'wp-beacon-crm-donate'); ?>">
                <label for="pgt-currency" class="pgt-label"><?php esc_html_e('Currency', 'wp-beacon-crm-donate'); ?></label>
                <select id="pgt-currency" class="pgt-select" aria-label="<?php esc_attr_e('Currency', 'wp-beacon-crm-donate'); ?>">
                    <option value="GBP">GBP £</option>
                    <option value="EUR">EUR €</option>
                    <option value="USD">USD $</option>
                </select>
            </div>
            <div id="pgt-form-wrap" class="pgt-form-wrap">
                <div id="pgt-beacon-slot"></div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
