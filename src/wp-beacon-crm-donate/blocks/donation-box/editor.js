(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var CheckboxControl = wp.components.CheckboxControl;
  var Button = wp.components.Button;
  var Fragment = wp.element.Fragment;

  wp.domReady(function () {
    registerBlockType('wbcd/donation-box', {
      title: 'Beacon Donation Box',
      description: 'CTA box that forwards to your donation form with the chosen options.',
      icon: 'money',
      category: 'widgets',
      attributes: {
        formName: {
          type: 'string',
          default: ''
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
        customParams: {
          type: 'array',
          default: []
        },
        allowedFrequencies: {
          type: 'array',
          default: ['single', 'monthly', 'annual']
        },
        defaultPresets: {
          type: 'object',
          default: {
            single: [10, 20, 30],
            monthly: [5, 10, 15],
            annual: [50, 100, 200]
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
                'Enter custom parameters in URL format: bcn_c_adopted_animals=elephant-123&key2=value2. This will be added to the URL of the full page form on redirect.'
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
                    placeholder: 'e.g., bcn_c_adopted_animals'
                  }),
                  el(TextControl, {
                    label: 'Parameter Value',
                    value: param.value,
                    onChange: function(value) {
                      updateParam(index, 'value', value);
                    },
                    placeholder: 'e.g., elephant-123'
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
                'Show single donation frequency option. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.'
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
                'Set default donation amounts per frequency. API settings will override these if configured.'
              ),
              el(TextControl, {
                label: 'Single Preset Amounts',
                value: ((attrs.defaultPresets || {}).single || [10, 20, 30]).join(', '),
                onChange: function(value) {
                  var amounts = value.split(',').map(function(v) { 
                    return parseFloat(v.trim()); 
                  }).filter(function(n) { 
                    return !isNaN(n) && n > 0; 
                  });
                  var newPresets = Object.assign({}, attrs.defaultPresets || {});
                  newPresets.single = amounts.length > 0 ? amounts : [10, 20, 30];
                  setAttributes({ defaultPresets: newPresets });
                },
                placeholder: '10, 20, 30',
                help: 'Comma-separated amounts for single donations (e.g., 10, 20, 30). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.'
              }),
              el(TextControl, {
                label: 'Monthly Preset Amounts',
                value: ((attrs.defaultPresets || {}).monthly || [5, 10, 15]).join(', '),
                onChange: function(value) {
                  var amounts = value.split(',').map(function(v) { 
                    return parseFloat(v.trim()); 
                  }).filter(function(n) { 
                    return !isNaN(n) && n > 0; 
                  });
                  var newPresets = Object.assign({}, attrs.defaultPresets || {});
                  newPresets.monthly = amounts.length > 0 ? amounts : [5, 10, 15];
                  setAttributes({ defaultPresets: newPresets });
                },
                placeholder: '5, 10, 15',
                help: 'Comma-separated amounts for monthly donations (e.g., 5, 10, 15). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.'
              }),
              el(TextControl, {
                label: 'Annual Preset Amounts',
                value: ((attrs.defaultPresets || {}).annual || [50, 100, 200]).join(', '),
                onChange: function(value) {
                  var amounts = value.split(',').map(function(v) { 
                    return parseFloat(v.trim()); 
                  }).filter(function(n) { 
                    return !isNaN(n) && n > 0; 
                  });
                  var newPresets = Object.assign({}, attrs.defaultPresets || {});
                  newPresets.annual = amounts.length > 0 ? amounts : [50, 100, 200];
                  setAttributes({ defaultPresets: newPresets });
                },
                placeholder: '50, 100, 200',
                help: 'Comma-separated amounts for annual donations (e.g., 50, 100, 200). Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.'
              })
            ),
            el(PanelBody, { title: 'Colors', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '12px' } }, 
                'Default primary color. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.'
              ),
              el('div', { style: { marginBottom: '12px' } },
                el('label', { style: { display: 'block', marginBottom: '4px', fontWeight: '600' } }, 'Primary Color'),
                el('input', {
                  type: 'color',
                  value: attrs.primaryColor || '#FF7B1A',
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
              el('p', { style: { fontSize: '12px', color: '#666', marginBottom: '4px' } }, 
                'Default brand color. Note: this is a backup option that gets replaced by your settings on the BeaconCRM Form on page load.'
              ),
              el('div', { style: { marginBottom: '12px' } },
                el('label', { style: { display: 'block', marginBottom: '4px', fontWeight: '600' } }, 'Brand Color'),
                el('input', {
                  type: 'color',
                  value: attrs.brandColor || '#354cb1',
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
