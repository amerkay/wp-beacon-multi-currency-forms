(function(){
  // Expect localized WPBCD_FORM_DATA with: beaconAccountName, formsByCurrency, allowedCurrencies, defaultCurrency
  if (typeof WPBCD_FORM_DATA !== 'object') return;

  var BEACON_ACCOUNT_NAME = WPBCD_FORM_DATA.beaconAccountName;
  var formsByCurrency = WPBCD_FORM_DATA.formsByCurrency || {};
  var ALLOWED = WPBCD_FORM_DATA.allowedCurrencies || [];
  var DEFAULT_CURRENCY = WPBCD_FORM_DATA.defaultCurrency || '';

  // Check if we have any currencies configured
  if (!ALLOWED.length) {
    console.warn('WPBCD: No currencies configured. Please configure donation forms in settings.');
    return;
  }

  // Use configured default currency, or first available as fallback
  var defaultCurrency = DEFAULT_CURRENCY && ALLOWED.indexOf(DEFAULT_CURRENCY) >= 0 
    ? DEFAULT_CURRENCY 
    : ALLOWED[0];

  // Inject Beacon SDK once
  (function(d,i){
    if(!d.getElementById(i)){
      var s=d.createElement("script");
      s.id=i;s.async=true;
      s.src="https://static.beaconproducts.co.uk/js-sdk/production/beaconcrm.min.js";
      d.head.appendChild(s);
    }
  }(document,"beacon-js-sdk"));

  // Elements
  var selectEl = document.getElementById("wpbcd-currency");
  var slot = document.getElementById("wpbcd-beacon-slot");
  if(!selectEl || !slot) return;

  // Helpers
  function getURLCurrency(){
    var params = new URLSearchParams(location.search);
    var cur = (params.get("currency") || "").toUpperCase();
    return ALLOWED.indexOf(cur) >= 0 ? cur : null;
  }

  function setURLCurrency(cur){
    try {
      var url = new URL(location.href);
      url.searchParams.set("currency", cur);
      history.replaceState(null,"",url.toString());
    } catch(e){}
  }

  function renderBeaconForm(cur){
    slot.innerHTML = "";
    var div = document.createElement("div");
    div.className = "beacon-form";
    div.setAttribute("data-account", BEACON_ACCOUNT_NAME);
    div.setAttribute("data-form", formsByCurrency[cur]);
    slot.appendChild(div);
    if(window.BeaconCRM && typeof window.BeaconCRM.render === "function"){
      window.BeaconCRM.render();
    }
  }

  // Geo-IP currency via ajax endpoint (requires GeoIP Detect plugin "JS API")
  function fetchGeoCurrency(){
    var endpoint = "/wp-admin/admin-ajax.php?action=geoip_detect2_get_info_from_current_ip";
    return fetch(endpoint, { credentials:"same-origin", cache:"no-store" })
      .then(function(res){ if(!res.ok) throw new Error("HTTP "+res.status); return res.json(); })
      .then(function(record){
        var raw = (record && record.extra && (record.extra.currencyCode || record.extra.currency_code)) || "";
        var code = String(raw).toUpperCase();
        // Return detected currency only if it's in our allowed list
        return (ALLOWED.indexOf(code) >= 0) ? code : defaultCurrency;
      })
      .catch(function(){ return defaultCurrency; });
  }

  // Init
  (function init(){
    var urlCurrency = getURLCurrency();
    if (urlCurrency){
      selectEl.value = urlCurrency;
      renderBeaconForm(urlCurrency);
      return;
    }
    fetchGeoCurrency().then(function(cur){
      selectEl.value = cur;
      renderBeaconForm(cur);
    });
  }());

  // Events
  selectEl.addEventListener("change", function(){
    var cur = String(selectEl.value || "").toUpperCase();
    if(ALLOWED.indexOf(cur) < 0) return;
    setURLCurrency(cur);
    renderBeaconForm(cur);
  });
}());
