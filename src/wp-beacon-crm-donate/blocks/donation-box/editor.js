(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

  wp.domReady(function () {
    registerBlockType('wbcd/donation-box', {
      title: 'Beacon Donation Box',
      description: 'CTA box that forwards to your donation form with the chosen options.',
      icon: 'money',
      category: 'widgets',
      edit: function () {
        var blockProps = useBlockProps({
          style: { minHeight: '100px' }
        });
        
        if (ServerSideRender) {
          return el('div', blockProps,
            el(ServerSideRender, { block: 'wbcd/donation-box' })
          );
        }
        
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
          }, '💰 Beacon Donation Box (preview on front end)')
        );
      },
      save: function () { return null; }
    });
  });
})();
