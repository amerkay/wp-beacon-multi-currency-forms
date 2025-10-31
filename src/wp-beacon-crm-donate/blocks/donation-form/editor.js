(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor.useBlockProps;
//   var ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

  wp.domReady(function () {
    registerBlockType('wbcd/donation-form', {
      title: 'Beacon Donation Form',
      description: 'Full-page BeaconCRM form with currency switcher.',
      icon: 'money',
      category: 'widgets',
      edit: function () {
        var blockProps = useBlockProps({
          style: { minHeight: '100px' }
        });
        
        // if (ServerSideRender) {
        //   return el('div', blockProps,
        //     el(ServerSideRender, { block: 'wbcd/donation-form' })
        //   );
        // }
        
        return el('div', blockProps,
          el('div', { 
            style: { 
              padding: '20px', 
              opacity: 0.6, 
              border: '2px dashed #ccc',
              borderRadius: '4px',
              textAlign: 'center',
              background: '#f9f9f9'
            } 
          }, '💰 Beacon Donation Form (preview on front end)')
        );
      },
      save: function () { return null; }
    });
  });
})();
