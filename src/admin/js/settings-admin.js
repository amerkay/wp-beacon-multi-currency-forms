/**
 * Beacon CRM Donate - Admin Settings JavaScript
 * Handles dynamic form management on the plugin settings page
 */

(function($) {
    'use strict';

    // Selectors (defined once for reusability)
    const SELECTORS = {
        formItem: '.wbcd-form-item',
        formContainer: '#wbcd-forms-container',
        addFormBtn: '#wbcd-add-form',
        removeFormBtn: '.wbcd-remove-form',
        addCurrencyBtn: '.wbcd-add-currency-btn',
        removeCurrencyBtn: '.wbcd-remove-currency',
        showAddCurrencyBtn: '.wbcd-show-add-currency',
        addCurrencySection: '.wbcd-add-currency',
        currencySelect: '.wbcd-currency-select',
        currencyIdInput: '.wbcd-currency-id',
        currencyTable: 'table tbody',
        currenciesSection: '.wbcd-currencies-section',
        targetPageSelect: 'select[name^="wbcd_forms"][name$="[target_page_id]"]',
        validationError: '.wbcd-validation-error',
        hasErrorClass: 'wbcd-has-error'
    };

    // CSS Classes
    const CSS_CLASSES = {
        widefat: 'widefat',
        currenciesTable: 'wbcd-currencies-table',
        colDefault: 'wbcd-col-default',
        colAction: 'wbcd-col-action',
        removeFormWrapper: 'wbcd-remove-form-wrapper'
    };

    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Form counter for creating new forms
        var formCounter = wbcdAdminSettings.formCount;

        /**
         * Handle toggle for account name instructions
         */
        $('.wbcd-toggle-instructions').on('click', function(e) {
            e.preventDefault();
            var $instructions = $('#wbcd-account-name-instructions');
            $instructions.slideToggle(300);
        });

        /**
         * Get i18n string
         */
        function i18n(key) {
            return wbcdAdminSettings.i18n[key] || '';
        }

        /**
         * Build currency table HTML
         */
        function buildCurrencyTableHtml() {
            return '<table class="' + CSS_CLASSES.widefat + ' ' + CSS_CLASSES.currenciesTable + '">' +
                '<thead><tr>' +
                '<th class="' + CSS_CLASSES.colDefault + '">' + i18n('default') + '</th>' +
                '<th>' + i18n('currency') + '</th>' +
                '<th>' + i18n('beaconFormId') + '</th>' +
                '<th class="' + CSS_CLASSES.colAction + '">' + i18n('action') + '</th>' +
                '</tr></thead>' +
                '<tbody></tbody>' +
                '</table>' +
                '<p class="description">' + i18n('defaultCurrencyDesc') + '</p>';
        }

        /**
         * Clear validation errors for a specific form item
         */
        function clearFormErrors($formItem) {
            // Always remove error messages
            $formItem.find(SELECTORS.validationError).remove();
            
            // Check if all errors are actually fixed
            var hasTargetPage = $formItem.find(SELECTORS.targetPageSelect).val() !== '0';
            var hasCurrencies = $formItem.find(SELECTORS.currenciesSection + ' ' + SELECTORS.currencyTable + ' tr').length > 0;
            
            // Always remove error state if both requirements are met
            if (hasTargetPage && hasCurrencies) {
                $formItem.removeClass(SELECTORS.hasErrorClass);
            }
        }

        /**
         * Validate a single form item and show errors
         */
        function validateFormItem($formItem, formNumber) {
            var isValid = true;
            var errors = [];
            
            // Clear previous errors for this form
            $formItem.find(SELECTORS.validationError).remove();
            $formItem.removeClass(SELECTORS.hasErrorClass);

            // Check if target page is selected
            var targetPageId = $formItem.find(SELECTORS.targetPageSelect).val();
            if (!targetPageId || targetPageId === '0') {
                isValid = false;
                errors.push('Form #' + formNumber + ': ' + i18n('targetPageRequired'));
                $formItem.addClass(SELECTORS.hasErrorClass);
                $formItem.find(SELECTORS.targetPageSelect)
                    .closest('p')
                    .append('<span class="wbcd-validation-error">' + i18n('targetPageRequired') + '</span>');
            }

            // Check if at least one currency is added
            var currencyCount = $formItem.find(SELECTORS.currenciesSection + ' ' + SELECTORS.currencyTable + ' tr').length;
            if (currencyCount === 0) {
                isValid = false;
                errors.push('Form #' + formNumber + ': ' + i18n('currenciesRequired'));
                $formItem.addClass(SELECTORS.hasErrorClass);
                $formItem.find(SELECTORS.currenciesSection + ' h4')
                    .after('<span class="wbcd-validation-error">' + i18n('currenciesRequired') + '</span>');
            }

            return {isValid: isValid, errors: errors};
        }

        /**
         * Validate all forms before submission
         */
        function validateAllForms() {
            var isValid = true;
            var allErrors = [];

            $(SELECTORS.formItem).each(function(index) {
                var $formItem = $(this);
                var formNumber = index + 1;
                
                var result = validateFormItem($formItem, formNumber);
                if (!result.isValid) {
                    isValid = false;
                    allErrors = allErrors.concat(result.errors);
                }
            });

            if (!isValid) {
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $('.' + SELECTORS.hasErrorClass).first().offset().top - 50
                }, 500);

                // Show summary alert
                alert(i18n('validationFailed') + '\n\n' + allErrors.join('\n'));
            }

            return isValid;
        }

        /**
         * Form submission handler
         */
        $('form').on('submit', function(e) {
            if (!validateAllForms()) {
                e.preventDefault();
                return false;
            }
        });

        /**
         * Target page selection change handler - clear errors when page is selected
         */
        $(document).on('change', SELECTORS.targetPageSelect, function() {
            var $formItem = $(this).closest(SELECTORS.formItem);
            clearFormErrors($formItem);
        });

        /**
         * Show add currency form when "Add more currencies" button is clicked
         */
        $(document).on('click', SELECTORS.showAddCurrencyBtn, function() {
            var formIndex = $(this).data('form-index');
            var $addSection = $(this).siblings(SELECTORS.addCurrencySection);
            
            // Toggle visibility
            $addSection.toggleClass('visible');
            
            // Update button text
            if ($addSection.hasClass('visible')) {
                $(this).text(i18n('hideCurrencyForm') || 'Hide');
            } else {
                $(this).text(i18n('addMoreCurrencies') || 'Add more currencies');
            }
        });

        /**
         * Validate Form ID
         * Must be alphanumeric only, 6-12 characters
         */
        function validateFormId(formId) {
            // Remove whitespace
            formId = formId.trim();
            
            // Check if empty
            if (!formId) {
                return {
                    valid: false,
                    message: i18n('enterFormId')
                };
            }
            
            // Check length (6-12 characters)
            if (formId.length < 6 || formId.length > 12) {
                return {
                    valid: false,
                    message: i18n('formIdLengthError')
                };
            }
            
            // Check alphanumeric only
            if (!/^[a-zA-Z0-9]+$/.test(formId)) {
                return {
                    valid: false,
                    message: i18n('formIdAlphanumericError')
                };
            }
            
            return {
                valid: true,
                message: ''
            };
        }

        /**
         * Add currency to form
         * This now creates a proper table row with ALL columns including the Default radio button
         */
        function addCurrencyToForm($button) {
            var formIndex = $button.data('form-index');
            var $select = $('#wbcd_new_currency_' + formIndex);
            var $idInput = $('#wbcd_new_currency_id_' + formIndex);
            var currency = $select.val();
            var formId = $idInput.val().trim();

            if (!currency) {
                alert(i18n('selectCurrency'));
                $select.focus();
                return;
            }

            // Validate form ID
            var validation = validateFormId(formId);
            if (!validation.valid) {
                alert(validation.message);
                $idInput.focus();
                return;
            }

            // Get the FULL currency text for display (e.g., "EUR - Euro (€)")
            var currencyFullText = $select.find('option:selected').text();
            
            var $table = $button.closest(SELECTORS.currenciesSection).find(SELECTORS.currencyTable);

            // Create table if it doesn't exist
            if ($table.length === 0) {
                $button.closest(SELECTORS.currenciesSection).find('p:has(em)').remove();
                $button.closest(SELECTORS.currenciesSection).prepend(buildCurrencyTableHtml());
                $table = $button.closest(SELECTORS.currenciesSection).find(SELECTORS.currencyTable);
            }

            // Check if this is the first currency being added
            var isFirstCurrency = $table.find('tr').length === 0;

            // Create the new table row with ALL columns including Default radio button
            // Display the FULL currency info in the Currency column
            var row = '<tr>' +
                '<td data-label="' + i18n('default') + '">' +
                '<input type="radio" name="wbcd_forms[' + formIndex + '][default_currency]" value="' + currency + '" ' + 
                (isFirstCurrency ? 'checked' : '') + ' title="' + i18n('setAsDefault') + '" />' +
                '</td>' +
                '<td data-label="' + i18n('currency') + '"><strong>' + currencyFullText + '</strong></td>' +
                '<td data-label="' + i18n('beaconFormId') + '"><input type="text" name="wbcd_forms[' + formIndex + '][currencies][' + currency + ']" value="' + formId + '" class="regular-text" placeholder="' + i18n('beaconFormIdPlaceholder') + '" /></td>' +
                '<td data-label="' + i18n('action') + '"><button type="button" class="button wbcd-remove-currency">' + i18n('remove') + '</button></td>' +
                '</tr>';

            $table.append(row);

            // Remove from select dropdown and clear inputs
            $select.find('option[value="' + currency + '"]').remove();
            $select.val('');
            $idInput.val('');
            
            // Clear validation errors since we now have at least one currency
            clearFormErrors($button.closest(SELECTORS.formItem));
        }

        /**
         * Button click handler for adding currency
         */
        $(document).on('click', SELECTORS.addCurrencyBtn, function() {
            addCurrencyToForm($(this));
        });

        /**
         * Enter key handler on currency ID input
         * Prevents form submission and triggers add currency action
         */
        $(document).on('keypress', SELECTORS.currencyIdInput, function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                var formIndex = $(this).attr('id').match(/\d+/)[0];
                var $button = $(SELECTORS.addCurrencyBtn + '[data-form-index="' + formIndex + '"]');
                addCurrencyToForm($button);
                return false;
            }
        });

        /**
         * Remove currency from form
         */
        $(document).on('click', SELECTORS.removeCurrencyBtn, function() {
            if (confirm(i18n('confirmRemoveCurrency'))) {
                var $row = $(this).closest('tr');
                var $table = $row.closest('table');
                var $formItem = $(this).closest(SELECTORS.formItem);
                
                // Get the currency code from the input name attribute before removing
                var $currencyInput = $row.find('input[name*="[currencies]["]');
                var inputName = $currencyInput.attr('name');
                var currencyCode = inputName.match(/\[currencies\]\[([A-Z]{3})\]/)[1];
                
                // Get the full currency text from the row
                var currencyFullText = $row.find('td:nth-child(2) strong').text();
                
                // Get the form index
                var formIndex = inputName.match(/wbcd_forms\[(\d+)\]/)[1];
                
                // Remove the row
                $row.remove();
                
                // Add the currency back to the dropdown for this form
                var $select = $('#wbcd_new_currency_' + formIndex);
                
                // Find the correct position to insert (maintain alphabetical order)
                var $options = $select.find('option');
                var inserted = false;
                
                $options.each(function() {
                    var optionValue = $(this).val();
                    if (optionValue && optionValue > currencyCode) {
                        $('<option value="' + currencyCode + '">' + currencyFullText + '</option>').insertBefore($(this));
                        inserted = true;
                        return false; // break the loop
                    }
                });
                
                // If not inserted (should go at the end), append it
                if (!inserted) {
                    $select.append('<option value="' + currencyCode + '">' + currencyFullText + '</option>');
                }
                
                // If table is now empty, show "no currencies" message
                if ($table.find('tbody tr').length === 0) {
                    $table.remove();
                    var $currenciesSection = $formItem.find(SELECTORS.currenciesSection);
                    $currenciesSection.find('.description').remove();
                    $currenciesSection.prepend('<p><em>' + i18n('noCurrencies') + '</em></p>');
                    
                    // Re-validate this form since currencies are now empty
                    var formIndexNum = $(SELECTORS.formItem).index($formItem);
                    validateFormItem($formItem, formIndexNum + 1);
                } else {
                    // Still have currencies, clear any currency-related errors
                    clearFormErrors($formItem);
                }
            }
        });

        /**
         * Remove entire form
         */
        $(document).on('click', SELECTORS.removeFormBtn, function() {
            if (confirm(i18n('confirmRemoveForm'))) {
                $(this).closest(SELECTORS.formItem).remove();
            }
        });

        /**
         * Build page dropdown options HTML
         */
        function buildPageDropdownOptions() {
            var html = '<option value="0">' + i18n('selectPage') + '</option>';
            if (wbcdAdminSettings.pages && wbcdAdminSettings.pages.length > 0) {
                for (var i = 0; i < wbcdAdminSettings.pages.length; i++) {
                    var page = wbcdAdminSettings.pages[i];
                    html += '<option value="' + page.id + '">' + page.title + '</option>';
                }
            }
            return html;
        }

        /**
         * Build new form HTML
         */
        function buildNewFormHtml(newIndex) {
            return '<div class="' + SELECTORS.formItem.substring(1) + '">' +
                '<h3>' + i18n('form') + ' #' + (newIndex + 1) + '</h3>' +
                '<p>' +
                '<label for="wbcd_form_name_' + newIndex + '"><strong>' + i18n('formName') + '</strong></label><br>' +
                '<input type="text" id="wbcd_form_name_' + newIndex + '" name="wbcd_forms[' + newIndex + '][name]" value="" class="regular-text" required placeholder="' + i18n('formNamePlaceholder') + '" />' +
                '</p>' +
                '<p>' +
                '<label for="wbcd_form_target_page_' + newIndex + '"><strong>' + i18n('donationFormPage') + '</strong></label><br>' +
                '<select name="wbcd_forms[' + newIndex + '][target_page_id]" id="wbcd_form_target_page_' + newIndex + '">' +
                buildPageDropdownOptions() +
                '</select>' +
                '<br><span class="description">' + i18n('targetPageDesc') + '</span>' +
                '</p>' +
                '<div class="' + SELECTORS.currenciesSection.substring(1) + '">' +
                '<h4>' + i18n('supportedCurrencies') + '</h4>' +
                '<p><em>' + i18n('noCurrencies') + '</em></p>' +
                '<button type="button" class="button ' + SELECTORS.showAddCurrencyBtn.substring(1) + '" data-form-index="' + newIndex + '">' + (i18n('addMoreCurrencies') || 'Add more currencies') + '</button>' +
                '<div class="' + SELECTORS.addCurrencySection.substring(1) + '" data-form-index="' + newIndex + '">' +
                '<label for="wbcd_new_currency_' + newIndex + '"><strong>' + i18n('addCurrency') + '</strong></label><br>' +
                '<select id="wbcd_new_currency_' + newIndex + '" class="' + SELECTORS.currencySelect.substring(1) + '" data-form-index="' + newIndex + '">' +
                '<option value="">' + i18n('selectCurrencyOption') + '</option>' +
                '</select> ' +
                '<input type="text" id="wbcd_new_currency_id_' + newIndex + '" class="' + SELECTORS.currencyIdInput.substring(1) + '" placeholder="' + i18n('beaconFormIdPlaceholder') + '" /> ' +
                '<button type="button" class="button ' + SELECTORS.addCurrencyBtn.substring(1) + '" data-form-index="' + newIndex + '">' + i18n('addCurrencyBtn') + '</button>' +
                '</div>' +
                '</div>' +
                '<p class="' + CSS_CLASSES.removeFormWrapper + '">' +
                '<button type="button" class="button button-link-delete ' + SELECTORS.removeFormBtn.substring(1) + '" data-form-index="' + newIndex + '">' + i18n('removeForm') + '</button>' +
                '</p>' +
                '</div>';
        }

        /**
         * Add new form
         */
        $(SELECTORS.addFormBtn).on('click', function() {
            var newIndex = formCounter++;
            
            var formHtml = buildNewFormHtml(newIndex);
            $(SELECTORS.formContainer).append(formHtml);

            // Populate currency options for new form
            var $newSelect = $('#wbcd_new_currency_' + newIndex);
            
            if (wbcdAdminSettings.currencies) {
                // Use the currencies data passed from PHP
                $.each(wbcdAdminSettings.currencies, function(code, info) {
                    $newSelect.append('<option value="' + code + '">' + code + ' - ' + info.name + ' (' + info.symbol + ')</option>');
                });
            }
        });

    }); // End document.ready

})(jQuery);
