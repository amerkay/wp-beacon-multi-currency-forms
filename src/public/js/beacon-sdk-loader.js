/**
 * Beacon SDK Loader
 * Injects the Beacon CRM JavaScript SDK once per page.
 * This enables proper UTM tracking attribution across domains.
 */
(function(d, id) {
  if (d.getElementById(id)) {
    return;
  }
  var js = d.createElement('script');
  js.id = id;
  js.async = true;
  js.src = 'https://static.beaconproducts.co.uk/js-sdk/production/beaconcrm.min.js';
  d.getElementsByTagName('head')[0].appendChild(js);
}(document, 'beacon-js-sdk'));
