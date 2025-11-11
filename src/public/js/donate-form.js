(function(){
  // Expect localized WPBCD_FORM_DATA with: beaconAccountName, formsByCurrency, allowedCurrencies, defaultCurrency
  if (typeof WPBCD_FORM_DATA !== 'object') return;

  var BEACON_ACCOUNT_NAME = WPBCD_FORM_DATA.beaconAccountName;
  var formsByCurrency = WPBCD_FORM_DATA.formsByCurrency || {};
  var ALLOWED = WPBCD_FORM_DATA.allowedCurrencies || [];
  var DEFAULT_CURRENCY = WPBCD_FORM_DATA.defaultCurrency || '';

  // Handle required URL parameters from data attribute
  // This must run before anything else to ensure params are in URL
  (function validateRequiredParams(){
    var pageEl = document.getElementById("wpbcd-page");
    if (!pageEl) return;

    var customParamsAttr = pageEl.getAttribute('data-custom-params');
    var defaultFrequency = pageEl.getAttribute('data-default-frequency');
    var defaultAmount = pageEl.getAttribute('data-default-amount');

    var currentUrl = new URL(window.location.href);
    var currentParams = new URLSearchParams(currentUrl.search);
    var missing = [];
    var needsRedirect = false;

    // Check custom params
    if (customParamsAttr) {
      try {
        var requiredParams = JSON.parse(customParamsAttr);
        if (requiredParams && typeof requiredParams === 'object' && !Array.isArray(requiredParams)) {
          // Check each required parameter
          for (var key in requiredParams) {
            if (requiredParams.hasOwnProperty(key)) {
              var currentValue = currentParams.get(key);
              var requiredValue = String(requiredParams[key]);

              // If parameter is missing or doesn't match required value
              if (!currentValue || currentValue !== requiredValue) {
                missing.push(key);
                currentParams.set(key, requiredValue);
                needsRedirect = true;
              }
            }
          }
        }
      } catch (e) {
        console.warn('WPBCD: Error checking required parameters', e);
      }
    }

    // Add default frequency to URL if specified and not already present
    if (defaultFrequency && !currentParams.get('bcn_donation_frequency')) {
      currentParams.set('bcn_donation_frequency', defaultFrequency);
      needsRedirect = true;
    }

    // Add default amount to URL if specified and not already present
    if (defaultAmount && !currentParams.get('bcn_donation_amount')) {
      currentParams.set('bcn_donation_amount', defaultAmount);
      needsRedirect = true;
    }

    // If any parameters need to be added, redirect
    if (needsRedirect) {
      currentUrl.search = currentParams.toString();
      window.location.href = currentUrl.toString();
      return; // Stop execution after redirect
    }
  }());

  // Check if we have any currencies configured
  if (!ALLOWED.length) {
    console.warn('WPBCD: No currencies configured. Please configure donation forms in settings.');
    return;
  }

  // Use configured default currency, or first available as fallback
  var defaultCurrency = DEFAULT_CURRENCY && ALLOWED.indexOf(DEFAULT_CURRENCY) >= 0 
    ? DEFAULT_CURRENCY 
    : ALLOWED[0];

  // Note: Beacon SDK is loaded separately via beacon-sdk-loader.js
  // (either globally when UTM tracking is enabled, or per-page when disabled)

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
    
    // Add stored UTM parameters as data attributes (only if utm_source is NOT in URL)
    var currentParams = new URLSearchParams(window.location.search);
    if (!currentParams.has('utm_source')) {
      var storedUtm = window.WBCD_getStoredUtm && window.WBCD_getStoredUtm();
      if (storedUtm) {
        // Get UTM parameter mappings and field names from localized data
        var utmParams = WPBCD_FORM_DATA.utmParams || {};
        var utmFields = WPBCD_FORM_DATA.utmFieldNames || ['utm_source', 'utm_medium', 'utm_campaign'];
        
        // Loop through each UTM parameter and set data attributes
        for (var i = 0; i < utmFields.length; i++) {
          var field = utmFields[i];
          var storedValue = storedUtm[field];
          var fieldConfig = utmParams[field];
          
          if (storedValue && fieldConfig) {
            if (fieldConfig.payment) {
              div.setAttribute("data-" + fieldConfig.payment, storedValue);
            }
            if (fieldConfig.subscription) {
              div.setAttribute("data-" + fieldConfig.subscription, storedValue);
            }
          }
        }
      }
    }
    
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
