<?php

namespace WBCD;

if (! defined('ABSPATH')) exit;

/**
 * Settings Renderer Class
 * 
 * Handles all HTML rendering for the settings page.
 * Separates presentation logic from business logic.
 */
class Settings_Renderer
{
    // Text domain
    const TEXT_DOMAIN = 'wp-beacon-crm-donate';

    /**
     * Render the beacon account field
     * 
     * @param string $value Current value
     */
    public static function render_beacon_account_field($value)
    {
?>
        <input type="text"
            id="<?php echo esc_attr(Settings::OPTION_BEACON_ACCOUNT); ?>"
            name="<?php echo esc_attr(Settings::OPTION_BEACON_ACCOUNT); ?>"
            value="<?php echo esc_attr($value); ?>"
            class="regular-text"
            required
            pattern="[a-z0-9_\-]+"
            title="<?php esc_attr_e('Must be lowercase with only letters, numbers, hyphens, and underscores (no spaces)', self::TEXT_DOMAIN); ?>" />

        <p class="description">
            <?php esc_html_e('Enter your BeaconCRM account name (e.g., "yourorg").', self::TEXT_DOMAIN); ?>
            <a href="#" class="wbcd-toggle-instructions" id="wbcd-account-name-toggle">
                <strong><?php esc_html_e('How to find your account name and form IDs?', self::TEXT_DOMAIN); ?></strong>
            </a>
        </p>

        <ol class="description wbcd-instructions-list wbcd-collapsible" id="wbcd-account-name-instructions">
            <li><?php esc_html_e('Navigate to any of your forms on BeaconCRM\'s interface.', self::TEXT_DOMAIN); ?></li>
            <li><?php esc_html_e('Click it, then click "Embed".', self::TEXT_DOMAIN); ?></li>
            <li><?php echo wp_kses_post(__('The form code should look like <code>&lt;div class="beacon-form" data-account="yourorg" data-form="f0rm1d"&gt;&lt;/div&gt;</code>. In this example, the account name is <code>yourorg</code> and the form ID (to fill below) is <code>f0rm1d</code>.', self::TEXT_DOMAIN)); ?></li>
        </ol>
    <?php
    }

    /**
     * Render currency table for a form
     * 
     * @param int $form_index Form index
     * @param array $form_currencies Array of currency code => form_id mappings
     * @param string $default_currency Default currency code
     * @param array $currencies_data All available currencies data
     */
    public static function render_currency_table($form_index, $form_currencies, $default_currency, $currencies_data)
    {
    ?>
        <table class="widefat wbcd-currencies-table">
            <thead>
                <tr>
                    <th class="wbcd-col-default"><?php esc_html_e('Default', self::TEXT_DOMAIN); ?></th>
                    <th><?php esc_html_e('Currency', self::TEXT_DOMAIN); ?></th>
                    <th><?php esc_html_e('Beacon Form ID', self::TEXT_DOMAIN); ?></th>
                    <th class="wbcd-col-action"><?php esc_html_e('Action', self::TEXT_DOMAIN); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($form_currencies as $code => $form_id) :
                    $currency_info = isset($currencies_data[$code]) ? $currencies_data[$code] : null;
                    $display_name = $currency_info
                        ? sprintf('%s (%s) %s', $code, $currency_info['name'], $currency_info['symbol'])
                        : $code;
                    $is_default = ($default_currency === $code);
                ?>
                    <tr>
                        <td data-label="<?php esc_attr_e('Default', self::TEXT_DOMAIN); ?>">
                            <input type="radio"
                                name="wbcd_forms[<?php echo esc_attr($form_index); ?>][default_currency]"
                                value="<?php echo esc_attr($code); ?>"
                                <?php checked($is_default, true); ?>
                                title="<?php esc_attr_e('Set as default currency', self::TEXT_DOMAIN); ?>" />
                        </td>
                        <td data-label="<?php esc_attr_e('Currency', self::TEXT_DOMAIN); ?>">
                            <strong><?php echo esc_html($display_name); ?></strong>
                        </td>
                        <td data-label="<?php esc_attr_e('Beacon Form ID', self::TEXT_DOMAIN); ?>">
                            <input type="text"
                                name="wbcd_forms[<?php echo esc_attr($form_index); ?>][currencies][<?php echo esc_attr($code); ?>]"
                                value="<?php echo esc_attr($form_id); ?>"
                                class="regular-text"
                                placeholder="<?php esc_attr_e('Beacon form ID', self::TEXT_DOMAIN); ?>" />
                        </td>
                        <td data-label="<?php esc_attr_e('Action', self::TEXT_DOMAIN); ?>">
                            <button type="button"
                                class="button wbcd-remove-currency"
                                data-form="<?php echo esc_attr($form_index); ?>"
                                data-currency="<?php echo esc_attr($code); ?>">
                                <?php esc_html_e('Remove', self::TEXT_DOMAIN); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="description">
            <?php esc_html_e('Select a default currency by clicking the radio button. This currency will be used when geo-detection fails or detects an unsupported currency.', self::TEXT_DOMAIN); ?>
        </p>
    <?php
    }

    /**
     * Render add currency section for a form
     * 
     * @param int $form_index Form index
     * @param array $form_currencies Current form currencies
     * @param array $currencies_data All available currencies data
     */
    public static function render_add_currency_section($form_index, $form_currencies, $currencies_data)
    {
        // Get currencies already used in THIS form only
        $current_form_currencies = array_keys($form_currencies);
    ?>
        <button type="button" class="button wbcd-show-add-currency" data-form-index="<?php echo esc_attr($form_index); ?>">
            <?php esc_html_e('Add more currencies', self::TEXT_DOMAIN); ?>
        </button>

        <div class="wbcd-add-currency" data-form-index="<?php echo esc_attr($form_index); ?>">
            <label for="wbcd_new_currency_<?php echo esc_attr($form_index); ?>">
                <strong><?php esc_html_e('Add Currency:', self::TEXT_DOMAIN); ?></strong>
            </label><br>

            <select id="wbcd_new_currency_<?php echo esc_attr($form_index); ?>"
                class="wbcd-currency-select"
                data-form-index="<?php echo esc_attr($form_index); ?>">
                <option value=""><?php esc_html_e('-- Select a currency --', self::TEXT_DOMAIN); ?></option>
                <?php foreach ($currencies_data as $code => $info) :
                    // Only show currencies not already used in THIS specific form
                    if (!in_array($code, $current_form_currencies)) :
                        $display = sprintf('%s - %s (%s)', $code, $info['name'], $info['symbol']);
                ?>
                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                <?php
                    endif;
                endforeach; ?>
            </select>

            <input type="text"
                id="wbcd_new_currency_id_<?php echo esc_attr($form_index); ?>"
                class="wbcd-currency-id"
                placeholder="<?php esc_attr_e('Beacon form ID', self::TEXT_DOMAIN); ?>" />

            <button type="button"
                class="button wbcd-add-currency-btn"
                data-form-index="<?php echo esc_attr($form_index); ?>">
                <?php esc_html_e('Add Currency', self::TEXT_DOMAIN); ?>
            </button>
        </div>
    <?php
    }

    /**
     * Render a single form item
     * 
     * @param int $form_index Form index
     * @param array $form Form data
     * @param int $total_forms Total number of forms
     * @param array $currencies_data All available currencies data
     */
    public static function render_form_item($form_index, $form, $total_forms, $currencies_data)
    {
        $form_name = esc_attr(isset($form['name']) ? $form['name'] : '');
        $form_currencies = isset($form['currencies']) ? $form['currencies'] : [];
        $default_currency = isset($form['default_currency']) ? $form['default_currency'] : '';
    ?>
        <div class="wbcd-form-item">
            <h3><?php echo esc_html(sprintf(__('Form #%d', self::TEXT_DOMAIN), $form_index + 1)); ?></h3>

            <!-- Form name -->
            <p>
                <label for="wbcd_form_name_<?php echo esc_attr($form_index); ?>">
                    <strong><?php esc_html_e('Form Name:', self::TEXT_DOMAIN); ?></strong>
                </label><br>
                <input type="text"
                    id="wbcd_form_name_<?php echo esc_attr($form_index); ?>"
                    name="wbcd_forms[<?php echo esc_attr($form_index); ?>][name]"
                    value="<?php echo $form_name; ?>"
                    class="regular-text"
                    required
                    placeholder="<?php esc_attr_e('e.g., General Donations', self::TEXT_DOMAIN); ?>" />
            </p>

            <!-- Currencies section -->
            <div class="wbcd-currencies-section">
                <h4><?php esc_html_e('Supported Currencies:', self::TEXT_DOMAIN); ?></h4>

                <?php if (!empty($form_currencies)) : ?>
                    <?php self::render_currency_table($form_index, $form_currencies, $default_currency, $currencies_data); ?>
                <?php else : ?>
                    <p><em><?php esc_html_e('No currencies added yet.', self::TEXT_DOMAIN); ?></em></p>
                <?php endif; ?>

                <?php self::render_add_currency_section($form_index, $form_currencies, $currencies_data); ?>
            </div>

            <!-- Remove form button -->
            <?php if ($total_forms > 1) : ?>
                <p class="wbcd-remove-form-wrapper">
                    <button type="button"
                        class="button button-link-delete wbcd-remove-form"
                        data-form-index="<?php echo esc_attr($form_index); ?>">
                        <?php esc_html_e('Remove This Form', self::TEXT_DOMAIN); ?>
                    </button>
                </p>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Render the donation forms field
     * 
     * @param array $forms Array of forms
     * @param array $currencies_data All available currencies data
     */
    public static function render_forms_field($forms, $currencies_data)
    {
    ?>
        <div id="wbcd-forms-container">
            <?php foreach ($forms as $form_index => $form) : ?>
                <?php self::render_form_item($form_index, $form, count($forms), $currencies_data); ?>
            <?php endforeach; ?>
        </div>

        <p>
            <button type="button" id="wbcd-add-form" class="button">
                <?php esc_html_e('+ Add Another Form', self::TEXT_DOMAIN); ?>
            </button>
        </p>

        <p class="description">
            <?php esc_html_e('Create donation forms and assign Beacon CRM form IDs for each currency. Each form can have multiple currencies and a default currency (used as fallback). Each currency can only appear once per form.', self::TEXT_DOMAIN); ?>
        </p>
<?php
    }
}
