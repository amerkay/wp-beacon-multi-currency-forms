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

  wp.domReady(function () {
    registerBlockType('wbcd/donation-form', {
      title: 'Beacon Donation Form',
      description: 'Full-page BeaconCRM form with currency switcher.',
      icon: 'money',
      category: 'widgets',
      attributes: {
        formName: {
          type: 'string',
          default: ''
        },
        customParams: {
          type: 'array',
          default: []
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
                help: 'Choose which donation form to display'
              })
            ),
            el(PanelBody, { title: 'Required URL Parameters', initialOpen: false },
              el('p', { style: { fontSize: '12px', color: '#666' } }, 
                'Add required parameters that must be in the URL. If missing, users will be redirected to include them.'
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
                    placeholder: 'e.g., campaign'
                  }),
                  el(TextControl, {
                    label: 'Parameter Value',
                    value: param.value,
                    onChange: function(value) {
                      updateParam(index, 'value', value);
                    },
                    placeholder: 'e.g., spring2025'
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
            }, '💰 Beacon Donation Form' + (attrs.formName ? ' (' + attrs.formName + ')' : ' (Default)'))
          )
        );
      },
      save: function () { return null; }
    });
  });
})();
