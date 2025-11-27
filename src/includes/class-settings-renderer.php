<?php

namespace BMCF;

if (!defined('ABSPATH'))
    exit;

/**
 * Settings Renderer Class
 * 
 * Handles all HTML rendering for the settings page.
 * Separates presentation logic from business logic.
 */
class Settings_Renderer
{
    // Text domain
    const TEXT_DOMAIN = 'beacon-multi-currency-forms';

    /**
     * Render the beacon account field
     * 
     * @param string $value Current value
     */
    public static function render_beacon_account_field($value)
    {
        ?>
                <input type="text" id="<?php echo esc_attr(Settings::OPTION_BEACON_ACCOUNT); ?>"
                    name="<?php echo esc_attr(Settings::OPTION_BEACON_ACCOUNT); ?>" value="<?php echo esc_attr($value); ?>"
                    class="regular-text" required pattern="[a-z0-9_\-]+"
                    title="<?php esc_attr_e('Must be lowercase with only letters, numbers, hyphens, and underscores (no spaces)', 'beacon-multi-currency-forms'); ?>" />

                <p class="description">
                    <?php esc_html_e('Enter your BeaconCRM account name (e.g., "yourorg").', 'beacon-multi-currency-forms'); ?>
                    <a href="#" class="bmcf-toggle-instructions" id="bmcf-account-name-toggle">
                        <strong><?php esc_html_e('How to find your account name and form IDs?', 'beacon-multi-currency-forms'); ?></strong>
                    </a>
                </p>

                <ol class="description bmcf-instructions-list bmcf-collapsible" id="bmcf-account-name-instructions">
                    <li><?php esc_html_e('Navigate to any of your forms on BeaconCRM\'s interface.', 'beacon-multi-currency-forms'); ?></li>
                    <li><?php esc_html_e('Click it, then click "Embed".', 'beacon-multi-currency-forms'); ?></li>
                    <li><?php echo wp_kses_post(__('The form code should look like <code>&lt;div class="beacon-form" data-account="yourorg" data-form="f0rm1d"&gt;&lt;/div&gt;</code>. In this example, the account name is <code>yourorg</code> and the form ID (to fill below) is <code>f0rm1d</code>.', 'beacon-multi-currency-forms')); ?>
                    </li>
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
                <table class="bmcf-settings-table">
                    <thead>
                        <tr>
                            <th class="bmcf-col-default"><?php esc_html_e('Default', 'beacon-multi-currency-forms'); ?></th>
                            <th><?php esc_html_e('Currency', 'beacon-multi-currency-forms'); ?></th>
                            <th><?php esc_html_e('Beacon Form ID', 'beacon-multi-currency-forms'); ?></th>
                            <th class="bmcf-col-action"><?php esc_html_e('Action', 'beacon-multi-currency-forms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($form_currencies as $code => $form_id):
                            $currency_info = isset($currencies_data[$code]) ? $currencies_data[$code] : null;
                            $display_name = $currency_info
                                ? sprintf('%s (%s) %s', $code, $currency_info['name'], $currency_info['symbol'])
                                : $code;
                            $is_default = ($default_currency === $code);
                            ?>
                                <tr>
                                    <td data-label="<?php esc_attr_e('Default', 'beacon-multi-currency-forms'); ?>">
                                        <input type="radio" name="bmcf_forms[<?php echo esc_attr($form_index); ?>][default_currency]"
                                            value="<?php echo esc_attr($code); ?>" <?php checked($is_default, true); ?>
                                            title="<?php esc_attr_e('Set as default currency', 'beacon-multi-currency-forms'); ?>" />
                                    </td>
                                    <td data-label="<?php esc_attr_e('Currency', 'beacon-multi-currency-forms'); ?>">
                                        <strong><?php echo esc_html($display_name); ?></strong>
                                    </td>
                                    <td data-label="<?php esc_attr_e('Beacon Form ID', 'beacon-multi-currency-forms'); ?>">
                                        <input type="text"
                                            name="bmcf_forms[<?php echo esc_attr($form_index); ?>][currencies][<?php echo esc_attr($code); ?>]"
                                            value="<?php echo esc_attr($form_id); ?>" class="regular-text"
                                            placeholder="<?php esc_attr_e('Beacon form ID', 'beacon-multi-currency-forms'); ?>" />
                                    </td>
                                    <td data-label="<?php esc_attr_e('Action', 'beacon-multi-currency-forms'); ?>">
                                        <button type="button" class="button bmcf-remove-currency"
                                            data-form="<?php echo esc_attr($form_index); ?>" data-currency="<?php echo esc_attr($code); ?>">
                                            <?php esc_html_e('Remove', 'beacon-multi-currency-forms'); ?>
                                        </button>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="description">
                    <?php esc_html_e('Select a default currency by clicking the radio button. This currency will be used when geo-detection fails or detects an unsupported currency.', 'beacon-multi-currency-forms'); ?>
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
                <button type="button" class="button bmcf-show-add-currency" data-form-index="<?php echo esc_attr($form_index); ?>">
                    <?php esc_html_e('Add more currencies', 'beacon-multi-currency-forms'); ?>
                </button>

                <div class="bmcf-add-currency" data-form-index="<?php echo esc_attr($form_index); ?>">
                    <label for="bmcf_new_currency_<?php echo esc_attr($form_index); ?>">
                        <strong><?php esc_html_e('Add Currency:', 'beacon-multi-currency-forms'); ?></strong>
                    </label><br>

                    <select id="bmcf_new_currency_<?php echo esc_attr($form_index); ?>" class="bmcf-currency-select"
                        data-form-index="<?php echo esc_attr($form_index); ?>">
                        <option value=""><?php esc_html_e('-- Select a currency --', 'beacon-multi-currency-forms'); ?></option>
                        <?php foreach ($currencies_data as $code => $info):
                            // Only show currencies not already used in THIS specific form
                            if (!in_array($code, $current_form_currencies)):
                                $display = sprintf('%s - %s (%s)', $code, $info['name'], $info['symbol']);
                                ?>
                                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($display); ?></option>
                                        <?php
                            endif;
                        endforeach; ?>
                    </select>

                    <input type="text" id="bmcf_new_currency_id_<?php echo esc_attr($form_index); ?>" class="bmcf-currency-id"
                        placeholder="<?php esc_attr_e('Beacon form ID', 'beacon-multi-currency-forms'); ?>" />

                    <button type="button" class="button bmcf-add-currency-btn" data-form-index="<?php echo esc_attr($form_index); ?>">
                        <?php esc_html_e('Add Currency', 'beacon-multi-currency-forms'); ?>
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
                <div class="bmcf-form-item">
                    <?php /* translators: %d: Form number */ ?>
                    <h3><?php echo esc_html(sprintf(__('Form #%d', 'beacon-multi-currency-forms'), $form_index + 1)); ?></h3>

                    <!-- Form name -->
                    <p>
                        <label for="bmcf_form_name_<?php echo esc_attr($form_index); ?>">
                            <strong><?php esc_html_e('Form Name:', 'beacon-multi-currency-forms'); ?></strong>
                        </label><br>
                        <input type="text" id="bmcf_form_name_<?php echo esc_attr($form_index); ?>"
                            name="bmcf_forms[<?php echo esc_attr($form_index); ?>][name]" value="<?php echo esc_attr($form_name); ?>"
                            class="regular-text" required
                            placeholder="<?php esc_attr_e('e.g., General Donations', 'beacon-multi-currency-forms'); ?>" />
                    </p>

                    <!-- Currencies section -->
                    <div class="bmcf-currencies-section">
                        <h4><?php esc_html_e('Supported Currencies:', 'beacon-multi-currency-forms'); ?></h4>

                        <?php if (!empty($form_currencies)): ?>
                                <?php self::render_currency_table($form_index, $form_currencies, $default_currency, $currencies_data); ?>
                        <?php else: ?>
                                <p><em><?php esc_html_e('No currencies added yet.', 'beacon-multi-currency-forms'); ?></em></p>
                        <?php endif; ?>

                        <?php self::render_add_currency_section($form_index, $form_currencies, $currencies_data); ?>
                    </div>

                    <!-- Remove form button -->
                    <?php if ($total_forms > 1): ?>
                            <p class="bmcf-remove-form-wrapper">
                                <button type="button" class="button button-link-delete bmcf-remove-form"
                                    data-form-index="<?php echo esc_attr($form_index); ?>">
                                    <?php esc_html_e('Remove This Form', 'beacon-multi-currency-forms'); ?>
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
                <div id="bmcf-forms-container">
                    <?php foreach ($forms as $form_index => $form): ?>
                            <?php self::render_form_item($form_index, $form, count($forms), $currencies_data); ?>
                    <?php endforeach; ?>
                </div>

                <p>
                    <button type="button" id="bmcf-add-form" class="button">
                        <?php esc_html_e('+ Add Another Form', 'beacon-multi-currency-forms'); ?>
                    </button>
                </p>

                        <p class="description">
                    <?php esc_html_e('Create donation forms and assign Beacon CRM form IDs for each currency. Each form can have multiple currencies and a default currency (used as fallback). Each currency can only appear once per form.', 'beacon-multi-currency-forms'); ?>
                </p>
        <?php
    }

    /**
     * Render the load Beacon globally field
     * 
     * @param bool $value Current value
     */
    public static function render_load_beacon_globally_field($value)
    {
        ?>
                <label>
                    <input type="checkbox"
                        id="<?php echo esc_attr(Settings::OPTION_LOAD_BEACON_GLOBALLY); ?>"
                        name="<?php echo esc_attr(Settings::OPTION_LOAD_BEACON_GLOBALLY); ?>"
                        value="1"
                        <?php checked($value, true); ?> />
                    <?php esc_html_e('Load Beacon JavaScript on every page to enable utm_* tracking', 'beacon-multi-currency-forms'); ?>
                </label>

                <p class="description">
                    <?php
                    printf(
                        wp_kses(
                            /* translators: %s: URL to documentation */
                            __('When enabled, the Beacon SDK is loaded on all pages to enable proper cross-domain attribution tracking. This is required for accurate source attribution because Beacon forms run on a separate domain. <a href="%s" target="_blank" rel="noopener">Learn more about tracking forms with Google Analytics</a>.', 'beacon-multi-currency-forms'),
                            ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                        ),
                        'https://guide.beaconcrm.org/en/articles/5720151-tracking-forms-with-google-analytics#h_13617763bc'
                    );
                    ?>
                </p>
        <?php
    }

    /**
     * Render the UTM tracking field
     * 
     * @param bool $value Current value
     * @param array $utm_params Current UTM parameter mappings
     */
    public static function render_utm_tracking_field($value, $utm_params)
    {
        ?>
                <label>
                    <input type="checkbox"
                        id="<?php echo esc_attr(Settings::OPTION_TRACK_UTM); ?>"
                        name="<?php echo esc_attr(Settings::OPTION_TRACK_UTM); ?>"
                        value="1"
                        <?php checked($value, true); ?> />
                    <?php esc_html_e('Enable tracking and passing UTM parameters to donation forms', 'beacon-multi-currency-forms'); ?>
                </label>

                <p class="description">
                    <?php esc_html_e('When enabled, UTM parameters (utm_source, utm_medium, utm_campaign) are automatically tracked across all pages and stored in a cookie for 180 days. These parameters are then passed to donation forms via data attributes.', 'beacon-multi-currency-forms'); ?>
                </p>

                <div id="bmcf-utm-params-section" <?php echo $value ? '' : 'style="display:none;"'; ?>>
                    <h4><?php esc_html_e('Parameter Configuration', 'beacon-multi-currency-forms'); ?></h4>
                    <p class="description">
                        <?php esc_html_e('Choose the URL Data parameters as configured under "URL Data" in your forms. All parameter names must start with "bcn_".', 'beacon-multi-currency-forms'); ?>
                    </p>

                    <table class="bmcf-settings-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('UTM Parameter', 'beacon-multi-currency-forms'); ?></th>
                                <th><?php esc_html_e('Payment Parameter', 'beacon-multi-currency-forms'); ?></th>
                                <th><?php esc_html_e('Subscription Parameter', 'beacon-multi-currency-forms'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $utm_fields = Settings::get_utm_field_names();

                            foreach ($utm_fields as $key):
                                $label = $key;
                                $payment_value = isset($utm_params[$key]['payment']) ? $utm_params[$key]['payment'] : '';
                                $subscription_value = isset($utm_params[$key]['subscription']) ? $utm_params[$key]['subscription'] : '';
                                ?>
                                    <tr>
                                        <td data-label="<?php esc_attr_e('UTM Parameter', 'beacon-multi-currency-forms'); ?>">
                                            <strong><?php echo esc_html($label); ?></strong>
                                        </td>
                                        <td data-label="<?php esc_attr_e('Payment Parameter', 'beacon-multi-currency-forms'); ?>">
                                            <input type="text"
                                                name="<?php echo esc_attr(Settings::OPTION_UTM_PARAMS); ?>[<?php echo esc_attr($key); ?>][payment]"
                                                value="<?php echo esc_attr($payment_value); ?>" class="regular-text"
                                                placeholder="<?php echo esc_attr('bcn_pay_c_' . $key); ?>" />
                                        </td>
                                        <td data-label="<?php esc_attr_e('Subscription Parameter', 'beacon-multi-currency-forms'); ?>">
                                            <input type="text"
                                                name="<?php echo esc_attr(Settings::OPTION_UTM_PARAMS); ?>[<?php echo esc_attr($key); ?>][subscription]"
                                                value="<?php echo esc_attr($subscription_value); ?>" class="regular-text"
                                                placeholder="<?php echo esc_attr('bcn_sub_c_' . $key); ?>" />
                                        </td>
                                    </tr>
                                    <?php
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
    }
}
