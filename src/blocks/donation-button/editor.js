(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;
  var TextControl = wp.components.TextControl;
  var Button = wp.components.Button;
  var Fragment = wp.element.Fragment;
  var ServerSideRender = wp.serverSideRender;

  wp.domReady(function () {
    registerBlockType('wbcd/donation-button', {
      title: 'Beacon Donation Button',
      description: 'A simple button that links to your donation form.',
      icon: 'button',
      category: 'widgets',
      attributes: {
        formName: {
          type: 'string',
          default: ''
        },
        color: {
          type: 'string',
          default: ''
        },
        text: {
          type: 'string',
          default: 'Donate'
        },
        size: {
          type: 'string',
          default: 'md'
        },
        amount: {
          type: 'string',
          default: ''
        },
        frequency: {
          type: 'string',
          default: ''
        },
        currency: {
          type: 'string',
          default: ''
        },
        customParams: {
          type: 'array',
          default: []
        }
      },
      edit: function (props) {
        var blockProps = useBlockProps();
        var attrs = props.attributes;
        var setAttributes = props.setAttributes;
        
        // Get forms from localized data
        var formOptions = window.wbcdForms || [{ value: '', label: 'Default (First form)' }];
        var formsData = window.wbcdFormsData || {};
        
        // Get available currencies for selected form
        var availableCurrencies = [];
        if (formsData[attrs.formName] && formsData[attrs.formName].currencies) {
          availableCurrencies = formsData[attrs.formName].currencies;
        }
        
        // Build currency options
        var currencyOptions = [{ value: '', label: 'None (use default)' }];
        availableCurrencies.forEach(function(code) {
          currencyOptions.push({ value: code, label: code });
        });
        
        // Helpers for custom params
        var addParam = function() {
          var newParams = attrs.customParams.slice();
          newParams.push({ key: '', value: '' });
          setAttributes({ customParams: newParams });
        };
        
        var removeParam = function(index) {
          var newParams = attrs.customParams.slice();
          newParams.splice(index, 1);
          setAttributes({ customParams: newParams });
        };
        
        var updateParam = function(index, field, value) {
          var newParams = attrs.customParams.slice();
          newParams[index][field] = value;
          setAttributes({ customParams: newParams });
        };
        
        return el(
          Fragment,
          {},
          el(
            InspectorControls,
            {},
            el(
              PanelBody,
              { title: 'Form Settings', initialOpen: true },
              el(SelectControl, {
                label: 'Select Form',
                value: attrs.formName,
                options: formOptions,
                onChange: function (val) { 
                  setAttributes({ formName: val, currency: '' }); // Reset currency when form changes
                },
                help: 'Choose which donation form to link to'
              })
            ),
            el(
              PanelBody,
              { title: 'Donation Settings', initialOpen: true },
              el(TextControl, {
                label: 'Amount',
                type: 'number',
                value: attrs.amount,
                onChange: function (val) { setAttributes({ amount: val }); },
                help: 'Pre-set donation amount (leave empty for user choice)',
                min: '0',
                step: '0.01'
              }),
              el(SelectControl, {
                label: 'Frequency',
                value: attrs.frequency,
                options: [
                  { value: '', label: 'None (use default)' },
                  { value: 'single', label: 'Single' },
                  { value: 'monthly', label: 'Monthly' },
                  { value: 'annual', label: 'Annual' }
                ],
                onChange: function (val) { setAttributes({ frequency: val }); },
                help: 'Pre-set donation frequency'
              }),
              el(SelectControl, {
                label: 'Currency',
                value: attrs.currency,
                options: currencyOptions,
                onChange: function (val) { setAttributes({ currency: val }); },
                help: 'Pre-set currency (changes based on selected form)'
              })
            ),
            el(
              PanelBody,
              { title: 'Button Settings', initialOpen: true },
              el(TextControl, {
                label: 'Button Text',
                value: attrs.text,
                onChange: function (val) { setAttributes({ text: val }); }
              }),
              el(SelectControl, {
                label: 'Button Size',
                value: attrs.size,
                options: [
                  { value: 'md', label: 'Medium' },
                  { value: 'lg', label: 'Large' },
                  { value: 'xl', label: 'Extra Large' }
                ],
                onChange: function (val) { setAttributes({ size: val }); }
              }),
              el(TextControl, {
                label: 'Button Color',
                value: attrs.color,
                onChange: function (val) { setAttributes({ color: val }); },
                help: 'Hex color code (e.g., #FF7B1A) or leave empty for default'
              })
            ),
            el(
              PanelBody,
              { title: 'Custom URL Parameters', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '10px' } },
                'Add custom parameters to append to the donation form URL.'
              ),
              attrs.customParams.map(function (param, idx) {
                return el(
                  'div',
                  { key: idx, style: { marginBottom: '12px', padding: '12px', background: '#f0f0f1', borderRadius: '4px' } },
                  el(TextControl, {
                    label: 'Parameter Key',
                    value: param.key || '',
                    onChange: function (val) { updateParam(idx, 'key', val); },
                    placeholder: 'e.g., bcn_c_adopted_animal'
                  }),
                  el(TextControl, {
                    label: 'Parameter Value',
                    value: param.value || '',
                    onChange: function (val) { updateParam(idx, 'value', val); },
                    placeholder: 'e.g., 12345'
                  }),
                  el(Button, {
                    isDestructive: true,
                    isSmall: true,
                    onClick: function () { removeParam(idx); }
                  }, 'Remove')
                );
              }),
              el(Button, {
                isPrimary: true,
                onClick: addParam
              }, '+ Add Parameter')
            )
          ),
          el(
            'div',
            blockProps,
            el(ServerSideRender, {
              block: 'wbcd/donation-button',
              attributes: attrs
            })
          )
        );
      },
      save: function () {
        return null;
      }
    });
  });
})();
