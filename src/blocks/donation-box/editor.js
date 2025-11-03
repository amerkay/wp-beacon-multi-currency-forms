(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;
  var ComboboxControl = wp.components.ComboboxControl;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var CheckboxControl = wp.components.CheckboxControl;
  var Button = wp.components.Button;
  var Fragment = wp.element.Fragment;

  var REPLACE_NOTICE = 'Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.';

  wp.domReady(function () {
    registerBlockType('wbcd/donation-box', {
      title: 'Beacon Donation Box',
      description: 'donate box that forwards to your donation form with the chosen options.',
      icon: 'money',
      category: 'widgets',
      attributes: {
        formName: {
          type: 'string',
          default: ''
        },
        targetPageId: {
          type: 'number',
          default: 0
        },
        primaryColor: {
          type: 'string',
          default: ''
        },
        brandColor: {
          type: 'string',
          default: ''
        },
        title: {
          type: 'string',
          default: 'Make a donation'
        },
        subtitle: {
          type: 'string',
          default: 'Pick your currency, frequency, and amount'
        },
        noticeText: {
          type: 'string',
          default: "You'll be taken to our secure donation form to complete your gift."
        },
        buttonText: {
          type: 'string',
          default: 'Donate now →'
        },
        customParams: {
          type: 'array',
          default: []
        },
        allowedFrequencies: {
          type: 'array',
          default: ['single', 'monthly', 'annual'] // Must match WBCD\Constants::DEFAULT_FREQUENCIES
        },
        // NOTE: Preset defaults below must match WBCD\Constants preset values
        presetsSingleStr: {
          type: 'string',
          default: '10, 20, 30' // Must match WBCD\Constants::PRESET_SINGLE
        },
        presetsMonthlyStr: {
          type: 'string',
          default: '5, 10, 15' // Must match WBCD\Constants::PRESET_MONTHLY
        },
        presetsAnnualStr: {
          type: 'string',
          default: '50, 100, 200' // Must match WBCD\Constants::PRESET_ANNUAL
        },
        defaultPresets: {
          type: 'object',
          default: {
            single: [10, 20, 30],    // Must match WBCD\Constants::PRESET_SINGLE
            monthly: [5, 10, 15],    // Must match WBCD\Constants::PRESET_MONTHLY
            annual: [50, 100, 200]   // Must match WBCD\Constants::PRESET_ANNUAL
          }
        }
      },
      edit: function (props) {
        var blockProps = useBlockProps({
          style: { minHeight: '100px' }
        });
        
        var attrs = props.attributes;
        var setAttributes = props.setAttributes;
        
        // Get forms from localized data
        var formOptions = window.wbcdForms || [{ value: '', label: 'Default (First form)' }];
        
        // Get pages from localized data and format for ComboboxControl
        var pageOptions = [{ value: '0', label: '— Select Page —' }];
        if (window.wbcdPages && window.wbcdPages.length > 0) {
          window.wbcdPages.forEach(function(page) {
            var displayLabel = page.title + ' (' + page.path + ')';
            pageOptions.push({ value: String(page.id), label: displayLabel });
          });
        }
        
        // Find current page label for ComboboxControl
        var currentPageOption = pageOptions.find(function(opt) {
          return opt.value === String(attrs.targetPageId);
        });
        var currentPageValue = currentPageOption ? currentPageOption.label : '';
        
        // Helpers for custom params
        var addParam = function() {
          var newParams = (attrs.customParams || []).concat([{ key: '', value: '' }]);
          setAttributes({ customParams: newParams });
        };
        
        var removeParam = function(index) {
          var newParams = (attrs.customParams || []).slice();
          newParams.splice(index, 1);
          setAttributes({ customParams: newParams });
        };
        
        var updateParam = function(index, field, value) {
          var newParams = (attrs.customParams || []).slice();
          newParams[index] = Object.assign({}, newParams[index], { [field]: value });
          setAttributes({ customParams: newParams });
        };
        
        return el(Fragment, {},
          el(InspectorControls, {},
            el(PanelBody, { title: 'Form Settings', initialOpen: true },
              el(SelectControl, {
                label: 'Select Form',
                value: attrs.formName,
                options: formOptions,
                onChange: function(value) {
                  setAttributes({ formName: value });
                },
                help: 'Choose which donation form to use'
              }),
              el(ComboboxControl, {
                label: 'Donation Form Page',
                value: currentPageValue,
                options: pageOptions,
                onChange: function(selectedLabel) {
                  // Find the option that matches the selected label
                  var selectedOption = pageOptions.find(function(opt) {
                    return opt.label === selectedLabel;
                  });
                  if (selectedOption) {
                    setAttributes({ targetPageId: parseInt(selectedOption.value, 10) });
                  }
                },
                help: 'Page where donors will be sent to complete the donation (optional). Start typing to search.'
              })
            ),
            el(PanelBody, { title: 'Text Content', initialOpen: false },
              el(TextControl, {
                label: 'Title',
                value: attrs.title,
                onChange: function(value) {
                  setAttributes({ title: value });
                }
              }),
              el(TextControl, {
                label: 'Subtitle',
                value: attrs.subtitle,
                onChange: function(value) {
                  setAttributes({ subtitle: value });
                }
              }),
              el(TextareaControl, {
                label: 'Notice Text',
                value: attrs.noticeText,
                onChange: function(value) {
                  setAttributes({ noticeText: value });
                },
                rows: 3
              }),
              el(TextControl, {
                label: 'Button Text',
                value: attrs.buttonText,
                onChange: function(value) {
                  setAttributes({ buttonText: value });
                },
                placeholder: 'Donate now →',
                help: 'Text shown on the donate button'
              })
            ),
            el(PanelBody, { title: 'Custom URL Parameters', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '12px' } }, 
                'Enter custom parameters in URL format: bcn_c_adopted_animal=12345&key2=value2. This will be added to the URL of the full page form on redirect.'
              ),
              (attrs.customParams || []).map(function(param, index) {
                return el('div', { 
                  key: index, 
                  style: { 
                    marginBottom: '12px', 
                    padding: '12px', 
                    border: '1px solid #ddd', 
                    borderRadius: '4px' 
                  } 
                },
                  el(TextControl, {
                    label: 'Parameter Name',
                    value: param.key,
                    onChange: function(value) {
                      updateParam(index, 'key', value);
                    },
                    placeholder: 'e.g., bcn_c_adopted_animal'
                  }),
                  el(TextControl, {
                    label: 'Parameter Value',
                    value: param.value,
                    onChange: function(value) {
                      updateParam(index, 'value', value);
                    },
                    placeholder: 'e.g., 12345'
                  }),
                  el(Button, {
                    isDestructive: true,
                    isSmall: true,
                    onClick: function() { removeParam(index); }
                  }, 'Remove')
                );
              }),
              el(Button, {
                isPrimary: true,
                onClick: addParam
              }, 'Add Parameter')
            ),
            el(PanelBody, { title: 'Frequencies', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '12px' } }, 
                REPLACE_NOTICE
              ),
              el(CheckboxControl, {
                label: 'Show Single Frequency',
                checked: (attrs.allowedFrequencies || ['single', 'monthly', 'annual']).indexOf('single') >= 0,
                onChange: function(checked) {
                  var current = attrs.allowedFrequencies || ['single', 'monthly', 'annual'];
                  var newFreqs = checked 
                    ? current.concat(['single']).filter(function(v, i, a) { return a.indexOf(v) === i; })
                    : current.filter(function(f) { return f !== 'single'; });
                  setAttributes({ allowedFrequencies: newFreqs.length > 0 ? newFreqs : ['monthly'] });
                }
              }),
              el(CheckboxControl, {
                label: 'Show Monthly Frequency',
                checked: (attrs.allowedFrequencies || ['single', 'monthly', 'annual']).indexOf('monthly') >= 0,
                onChange: function(checked) {
                  var current = attrs.allowedFrequencies || ['single', 'monthly', 'annual'];
                  var newFreqs = checked 
                    ? current.concat(['monthly']).filter(function(v, i, a) { return a.indexOf(v) === i; })
                    : current.filter(function(f) { return f !== 'monthly'; });
                  setAttributes({ allowedFrequencies: newFreqs.length > 0 ? newFreqs : ['single'] });
                }
              }),
              el(CheckboxControl, {
                label: 'Show Annual Frequency',
                checked: (attrs.allowedFrequencies || ['single', 'monthly', 'annual']).indexOf('annual') >= 0,
                onChange: function(checked) {
                  var current = attrs.allowedFrequencies || ['single', 'monthly', 'annual'];
                  var newFreqs = checked 
                    ? current.concat(['annual']).filter(function(v, i, a) { return a.indexOf(v) === i; })
                    : current.filter(function(f) { return f !== 'annual'; });
                  setAttributes({ allowedFrequencies: newFreqs.length > 0 ? newFreqs : ['monthly'] });
                }
              })
            ),
            el(PanelBody, { title: 'Default Preset Amounts', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '12px' } }, 
                REPLACE_NOTICE
              ),
              el(TextControl, {
                label: 'Single Preset Amounts',
                value: attrs.presetsSingleStr || '10, 20, 30',
                onChange: function(value) {
                  setAttributes({ presetsSingleStr: value });
                  var amounts = value.split(',').map(function(v) { 
                    return parseFloat(v.trim()); 
                  }).filter(function(n) { 
                    return !isNaN(n) && n > 0; 
                  });
                  if (amounts.length > 0) {
                    var newPresets = Object.assign({}, attrs.defaultPresets || {});
                    newPresets.single = amounts;
                    setAttributes({ defaultPresets: newPresets });
                  }
                },
                placeholder: '10, 20, 30',
                help: 'Comma-separated amounts (e.g., 10, 20, 30, 40)'
              }),
              el(TextControl, {
                label: 'Monthly Preset Amounts',
                value: attrs.presetsMonthlyStr || '5, 10, 15',
                onChange: function(value) {
                  setAttributes({ presetsMonthlyStr: value });
                  var amounts = value.split(',').map(function(v) { 
                    return parseFloat(v.trim()); 
                  }).filter(function(n) { 
                    return !isNaN(n) && n > 0; 
                  });
                  if (amounts.length > 0) {
                    var newPresets = Object.assign({}, attrs.defaultPresets || {});
                    newPresets.monthly = amounts;
                    setAttributes({ defaultPresets: newPresets });
                  }
                },
                placeholder: '5, 10, 15',
                help: 'Comma-separated amounts (e.g., 5, 10, 15, 20)'
              }),
              el(TextControl, {
                label: 'Annual Preset Amounts',
                value: attrs.presetsAnnualStr || '50, 100, 200',
                onChange: function(value) {
                  setAttributes({ presetsAnnualStr: value });
                  var amounts = value.split(',').map(function(v) { 
                    return parseFloat(v.trim()); 
                  }).filter(function(n) { 
                    return !isNaN(n) && n > 0; 
                  });
                  if (amounts.length > 0) {
                    var newPresets = Object.assign({}, attrs.defaultPresets || {});
                    newPresets.annual = amounts;
                    setAttributes({ defaultPresets: newPresets });
                  }
                },
                placeholder: '50, 100, 200',
                help: 'Comma-separated amounts (e.g., 50, 100, 200, 300)'
              })
            ),
            el(PanelBody, { title: 'Colors', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '12px' } }, 
                REPLACE_NOTICE
              ),
              el('div', { style: { marginBottom: '12px' } },
                el('label', { style: { display: 'block', marginBottom: '4px', fontWeight: '600' } }, 'Primary Color'),
                el('input', {
                  type: 'color',
                  value: attrs.primaryColor || (window.WBCD_CONSTANTS && window.WBCD_CONSTANTS.colors ? window.WBCD_CONSTANTS.colors.primary : '#FF7B1A'),
                  onChange: function(e) {
                    setAttributes({ primaryColor: e.target.value });
                  },
                  style: { width: '100%', height: '40px', cursor: 'pointer' }
                }),
                el(Button, {
                  isSmall: true,
                  isDestructive: true,
                  onClick: function() { setAttributes({ primaryColor: '' }); },
                  style: { marginTop: '4px' }
                }, 'Reset Primary')
              ),
              el('div', { style: { marginBottom: '12px' } },
                el('label', { style: { display: 'block', marginBottom: '4px', fontWeight: '600' } }, 'Brand Color'),
                el('input', {
                  type: 'color',
                  value: attrs.brandColor || (window.WBCD_CONSTANTS && window.WBCD_CONSTANTS.colors ? window.WBCD_CONSTANTS.colors.brand : '#676767'),
                  onChange: function(e) {
                    setAttributes({ brandColor: e.target.value });
                  },
                  style: { width: '100%', height: '40px', cursor: 'pointer' }
                }),
                el(Button, {
                  isSmall: true,
                  isDestructive: true,
                  onClick: function() { setAttributes({ brandColor: '' }); },
                  style: { marginTop: '4px' }
                }, 'Reset Brand')
              )
            )
          ),
          el('div', blockProps,
            el('div', { 
              style: { 
                padding: '20px', 
                opacity: 0.6, 
                border: '2px dashed #ccc',
                borderRadius: '4px',
                textAlign: 'center',
                background: '#f9f9f9'
              } 
            }, '💰 Beacon Donation Box' + (attrs.formName ? ' (' + attrs.formName + ')' : ' (Default)'))
          )
        );
      },
      save: function () { return null; }
    });
  });
})();
