(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;

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
        }
      },
      edit: function (props) {
        var blockProps = useBlockProps({
          style: { minHeight: '100px' }
        });
        
        var formName = props.attributes.formName;
        
        // Get forms from localized data
        var formOptions = window.wbcdForms || [{ value: '', label: 'Default (First form)' }];
        
        return el('div', {},
          el(InspectorControls, {},
            el(PanelBody, { title: 'Form Settings', initialOpen: true },
              el(SelectControl, {
                label: 'Select Form',
                value: formName,
                options: formOptions,
                onChange: function(value) {
                  props.setAttributes({ formName: value });
                },
                help: 'Choose which donation form to use'
              })
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
            }, '💰 Beacon Donation Box' + (formName ? ' (' + formName + ')' : ' (Default)'))
          )
        );
      },
      save: function () { return null; }
    });
  });
})();
