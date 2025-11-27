/**
 * Beacon Multi-Currency Forms - Admin Settings JavaScript
 * Handles dynamic form management on the plugin settings page
 */

(function($) {
    'use strict';

    // Selectors (defined once for reusability)
    const SELECTORS = {
        formItem: '.bmcf-form-item',
        formContainer: '#bmcf-forms-container',
        addFormBtn: '#bmcf-add-form',
        removeFormBtn: '.bmcf-remove-form',
        addCurrencyBtn: '.bmcf-add-currency-btn',
        removeCurrencyBtn: '.bmcf-remove-currency',
        showAddCurrencyBtn: '.bmcf-show-add-currency',
        addCurrencySection: '.bmcf-add-currency',
        currencySelect: '.bmcf-currency-select',
        currencyIdInput: '.bmcf-currency-id',
        currencyTable: 'table tbody',
        currenciesSection: '.bmcf-currencies-section',
        validationError: '.bmcf-validation-error',
        hasErrorClass: 'bmcf-has-error'
    };

    // CSS Classes
    const CSS_CLASSES = {
        settingsTable: 'bmcf-settings-table',
        colDefault: 'bmcf-col-default',
        colAction: 'bmcf-col-action',
        removeFormWrapper: 'bmcf-remove-form-wrapper'
    };

    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Form counter for creating new forms
        var formCounter = bmcfAdminSettings.formCount;

        /**
         * Handle toggle for account name instructions
         */
        $('.bmcf-toggle-instructions').on('click', function(e) {
            e.preventDefault();
            var $instructions = $('#bmcf-account-name-instructions');
            $instructions.slideToggle(300);
        });

        /**
         * Handle toggle for UTM parameters section
         */
        $('#bmcf_track_utm').on('change', function() {
            var $utmParamsSection = $('#bmcf-utm-params-section');
            if ($(this).is(':checked')) {
                $utmParamsSection.slideDown(300);
            } else {
                $utmParamsSection.slideUp(300);
            }
        });

        /**
         * Get i18n string
         */
        function i18n(key) {
            return bmcfAdminSettings.i18n[key] || '';
        }

        /**
         * Build currency table HTML
         */
        function buildCurrencyTableHtml() {
            return `<table class="${CSS_CLASSES.settingsTable}">
                <thead><tr>
                <th class="${CSS_CLASSES.colDefault}">${i18n('default')}</th>
                <th>${i18n('currency')}</th>
                <th>${i18n('beaconFormId')}</th>
                <th class="${CSS_CLASSES.colAction}">${i18n('action')}</th>
                </tr></thead>
                <tbody></tbody>
                </table>
                <p class="description">${i18n('defaultCurrencyDesc')}</p>`;
        }

        /**
         * Clear validation errors for a specific form item
         */
        function clearFormErrors($formItem) {
            // Always remove error messages
            $formItem.find(SELECTORS.validationError).remove();
            
            // Check if all errors are actually fixed
            var hasCurrencies = $formItem.find(SELECTORS.currenciesSection + ' ' + SELECTORS.currencyTable + ' tr').length > 0;
            
            // Always remove error state if requirement is met
            if (hasCurrencies) {
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

            // Check if at least one currency is added
            var currencyCount = $formItem.find(SELECTORS.currenciesSection + ' ' + SELECTORS.currencyTable + ' tr').length;
            if (currencyCount === 0) {
                isValid = false;
                errors.push('Form #' + formNumber + ': ' + i18n('currenciesRequired'));
                $formItem.addClass(SELECTORS.hasErrorClass);
                $formItem.find(SELECTORS.currenciesSection + ' h4')
                    .after('<span class="bmcf-validation-error">' + i18n('currenciesRequired') + '</span>');
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
         * Uses validation rules from PHP (single source of truth)
         */
        function validateFormId(formId) {
            var validation = bmcfAdminSettings.validation;
            var messages = bmcfAdminSettings.validationMessages;
            
            // Remove whitespace
            formId = formId.trim();
            
            // Check if empty
            if (!formId) {
                return {
                    valid: false,
                    message: messages.enterFormId
                };
            }
            
            // Check length
            if (formId.length < validation.formId.minLength || formId.length > validation.formId.maxLength) {
                return {
                    valid: false,
                    message: messages.formIdLengthError
                };
            }
            
            // Check pattern (alphanumeric only)
            var pattern = new RegExp(validation.formId.pattern);
            if (!pattern.test(formId)) {
                return {
                    valid: false,
                    message: messages.formIdAlphanumericError
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
            var $select = $('#bmcf_new_currency_' + formIndex);
            var $idInput = $('#bmcf_new_currency_id_' + formIndex);
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
            const row = `<tr>
                <td data-label="${i18n('default')}">
                <input type="radio" name="bmcf_forms[${formIndex}][default_currency]" value="${currency}" ${isFirstCurrency ? 'checked' : ''} title="${i18n('setAsDefault')}" />
                </td>
                <td data-label="${i18n('currency')}"><strong>${currencyFullText}</strong></td>
                <td data-label="${i18n('beaconFormId')}"><input type="text" name="bmcf_forms[${formIndex}][currencies][${currency}]" value="${formId}" class="regular-text" placeholder="${i18n('beaconFormIdPlaceholder')}" /></td>
                <td data-label="${i18n('action')}"><button type="button" class="button bmcf-remove-currency">${i18n('remove')}</button></td>
                </tr>`;

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
                var formIndex = inputName.match(/bmcf_forms\[(\d+)\]/)[1];
                
                // Remove the row
                $row.remove();
                
                // Add the currency back to the dropdown for this form
                var $select = $('#bmcf_new_currency_' + formIndex);
                
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
         * Build new form HTML
         */
        function buildNewFormHtml(newIndex) {
            const formItemClass = SELECTORS.formItem.substring(1);
            const showAddCurrencyClass = SELECTORS.showAddCurrencyBtn.substring(1);
            const addCurrencyClass = SELECTORS.addCurrencySection.substring(1);
            const currencySelectClass = SELECTORS.currencySelect.substring(1);
            const currencyIdClass = SELECTORS.currencyIdInput.substring(1);
            const addCurrencyBtnClass = SELECTORS.addCurrencyBtn.substring(1);
            const removeFormClass = SELECTORS.removeFormBtn.substring(1);
            const currenciesClass = SELECTORS.currenciesSection.substring(1);
            
            return `<div class="${formItemClass}">
                <h3>${i18n('form')} #${newIndex + 1}</h3>
                <p>
                <label for="bmcf_form_name_${newIndex}"><strong>${i18n('formName')}</strong></label><br>
                <input type="text" id="bmcf_form_name_${newIndex}" name="bmcf_forms[${newIndex}][name]" value="" class="regular-text" required placeholder="${i18n('formNamePlaceholder')}" />
                </p>
                <div class="${currenciesClass}">
                <h4>${i18n('supportedCurrencies')}</h4>
                <p><em>${i18n('noCurrencies')}</em></p>
                <button type="button" class="button ${showAddCurrencyClass}" data-form-index="${newIndex}">${i18n('addMoreCurrencies') || 'Add more currencies'}</button>
                <div class="${addCurrencyClass}" data-form-index="${newIndex}">
                <label for="bmcf_new_currency_${newIndex}"><strong>${i18n('addCurrency')}</strong></label><br>
                <select id="bmcf_new_currency_${newIndex}" class="${currencySelectClass}" data-form-index="${newIndex}">
                <option value="">${i18n('selectCurrencyOption')}</option>
                </select> 
                <input type="text" id="bmcf_new_currency_id_${newIndex}" class="${currencyIdClass}" placeholder="${i18n('beaconFormIdPlaceholder')}" /> 
                <button type="button" class="button ${addCurrencyBtnClass}" data-form-index="${newIndex}">${i18n('addCurrencyBtn')}</button>
                </div>
                </div>
                <p class="${CSS_CLASSES.removeFormWrapper}">
                <button type="button" class="button button-link-delete ${removeFormClass}" data-form-index="${newIndex}">${i18n('removeForm')}</button>
                </p>
                </div>`;
        }

        /**
         * Add new form
         */
        $(SELECTORS.addFormBtn).on('click', function() {
            var newIndex = formCounter++;
            
            var formHtml = buildNewFormHtml(newIndex);
            $(SELECTORS.formContainer).append(formHtml);

            // Populate currency options for new form
            var $newSelect = $('#bmcf_new_currency_' + newIndex);
            
            if (bmcfAdminSettings.currencies) {
                // Use the currencies data passed from PHP
                $.each(bmcfAdminSettings.currencies, function(code, info) {
                    $newSelect.append('<option value="' + code + '">' + code + ' - ' + info.name + ' (' + info.symbol + ')</option>');
                });
            }
        });

    }); // End document.ready

})(jQuery);
