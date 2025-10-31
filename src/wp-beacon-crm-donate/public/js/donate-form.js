(function(){
  // Expect localized WBCD_FORM_DATA with: account, formsByCurrency, allowedCurrencies
  if (typeof WBCD_FORM_DATA !== 'object') return;

  var ACCOUNT = WBCD_FORM_DATA.account || 'pangeatrust';
  var formsByCurrency = WBCD_FORM_DATA.formsByCurrency || { GBP:'57085719', EUR:'694de004', USD:'17a36966' };
  var ALLOWED = WBCD_FORM_DATA.allowedCurrencies || ['GBP','EUR','USD'];

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
  var selectEl = document.getElementById("pgt-currency");
  var slot = document.getElementById("pgt-beacon-slot");
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
    div.setAttribute("data-account", ACCOUNT);
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
        return (code === "USD" || code === "EUR" || code === "GBP") ? code : "GBP";
      })
      .catch(function(){ return "GBP"; });
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
