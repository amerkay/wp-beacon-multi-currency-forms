(function(){
  // Expect localized WBCD_CTA_DATA: { account, formsByCurrency:{GBP:{id,symbol},...}, baseURL }
  if (typeof WBCD_CTA_DATA !== 'object') return;

  var formsByCurrency = WBCD_CTA_DATA.formsByCurrency || {
    GBP:{id:"57085719",symbol:"£"},
    EUR:{id:"694de004",symbol:"€"},
    USD:{id:"17a36966",symbol:"$"}
  };
  var baseURL = WBCD_CTA_DATA.baseURL || (location.origin + "/donation-form/");

  var DEFAULT_PRESETS = { single:[10,20,30], monthly:[5,10,15], annual:[50,100,200] };

  // Geo currency via ajax endpoint (JS API must be enabled in the GeoIP plugin)
  function fetchGeoCurrency(){
    var endpoint = "/wp-admin/admin-ajax.php?action=geoip_detect2_get_info_from_current_ip";
    return fetch(endpoint, { credentials:"same-origin", cache:"no-store" })
      .then(function(res){ if(!res.ok) throw new Error("HTTP "+res.status); return res.json(); })
      .then(function(record){
        var code = String( (record && record.extra && (record.extra.currency_code || record.extra.currencyCode)) || "" ).toUpperCase();
        return (code === "USD" || code === "EUR" || code === "GBP") ? code : "GBP";
      })
      .catch(function(){ return "GBP"; });
  }

  // State
  var state = {
    currency: "GBP",
    frequency: "monthly",
    amountPresets: { single:DEFAULT_PRESETS.single.slice(0), monthly:DEFAULT_PRESETS.monthly.slice(0), annual:DEFAULT_PRESETS.annual.slice(0) },
    amountSelected: null
  };

  // Elements
  var wrap = document.getElementById("pangea-donate");
  if(!wrap) return;

  var freqBtns = Array.prototype.slice.call(wrap.querySelectorAll(".pgt-btn-frequency"));
  var amountBtnsWrap = document.getElementById("pgt-amount-buttons");
  var customWrap = document.getElementById("pgt-custom-wrap");
  var customToggle = document.getElementById("pgt-toggle-custom");
  var customAmountInput = document.getElementById("pgt-custom-amount");
  var currencySymbol = document.getElementById("pgt-currency-symbol");
  var currencySelect = document.getElementById("pgt-currency-select");
  var nextBtn = document.getElementById("pgt-next");

  currencySelect.value = state.currency;
  currencySymbol.textContent = formsByCurrency[state.currency].symbol;

  // Helpers
  function setSelected(btns, value, attr){
    btns.forEach(function(b){
      var v = b.getAttribute('data-'+attr);
      b.setAttribute("aria-selected", v === value ? "true" : "false");
    });
  }
  function renderFrequencyUI(){ setSelected(freqBtns, state.frequency, "frequency"); }

  function renderAmountPresets(){
    amountBtnsWrap.innerHTML = "";
    var group = (state.frequency === "single")
      ? state.amountPresets.single
      : (state.frequency === "annual") ? state.amountPresets.annual : state.amountPresets.monthly;

    var safeGroup = (Object.prototype.toString.call(group)==='[object Array]' && group.length) ? group : DEFAULT_PRESETS[state.frequency];

    safeGroup.forEach(function(v){
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "pgt-tab pgt-btn-amount";
      btn.textContent = formsByCurrency[state.currency].symbol + String(Number(v));
      btn.setAttribute("aria-pressed","false");
      btn.setAttribute("data-amount", String(v));
      btn.addEventListener("click", function(){
        Array.prototype.slice.call(amountBtnsWrap.querySelectorAll(".pgt-btn-amount")).forEach(function(el){ el.setAttribute("aria-pressed","false"); });
        btn.setAttribute("aria-pressed","true");
        state.amountSelected = Number(v);
        customAmountInput.value = "";
        updateNextButton();
      });
      amountBtnsWrap.appendChild(btn);
    });
    updateNextButton();
  }

  function parsePresetArray(arr){
    if (!arr || Object.prototype.toString.call(arr)!=='[object Array]') return [];
    var out = [];
    for (var i=0;i<arr.length;i++){
      var o = arr[i];
      var n = Number(o && o.value);
      if(!isNaN(n)) out.push(n);
    }
    return out;
  }

  function fetchPresets(currency){
    var formId = formsByCurrency[currency].id;
    var url = "https://portal.beaconproducts.co.uk/v1/account/pangeatrust/form/" + formId + "?fp=x";
    return fetch(url, { credentials:"omit", cache:"no-store" })
      .then(function(res){ if(!res.ok) throw new Error("HTTP "+res.status); return res.json(); })
      .then(function(data){
        var sections = (data && data.sections) || [];
        var donationSection = null;
        for (var i=0;i<sections.length;i++){
          if (sections[i] && sections[i].key === "donation_amount"){ donationSection = sections[i]; break; }
        }
        return {
          single:  parsePresetArray(donationSection && donationSection.preset_single_amounts),
          monthly: parsePresetArray(donationSection && donationSection.preset_monthly_amounts),
          annual:  parsePresetArray(donationSection && donationSection.preset_annual_amounts)
        };
      })
      .catch(function(){ return { single:DEFAULT_PRESETS.single, monthly:DEFAULT_PRESETS.monthly, annual:DEFAULT_PRESETS.annual }; });
  }

  function onCurrencyChange(newCur){
    if(!formsByCurrency[newCur]) return;
    state.currency = newCur;
    currencySelect.value = newCur;
    currencySymbol.textContent = formsByCurrency[state.currency].symbol;
    state.amountSelected = null;
    customAmountInput.value = "";
    renderAmountPresets(); // default first
    fetchPresets(state.currency).then(function(p){
      state.amountPresets = p;
      renderAmountPresets(); // update grid with live values
    });
  }

  function onFrequencyChange(newFreq){
    state.frequency = newFreq;
    state.amountSelected = null;
    customAmountInput.value = "";
    renderFrequencyUI();
    renderAmountPresets();
  }

  function getChosenAmount(){
    var custom = parseFloat(customAmountInput.value);
    if(!isNaN(custom) && custom > 0) return Math.round(custom * 100) / 100;
    if(typeof state.amountSelected === "number") return state.amountSelected;
    return null;
  }

  function updateNextButton(){
    var amt = getChosenAmount();
    nextBtn.disabled = !amt;
  }

  function buildNextURL(){
    var params = new URLSearchParams();
    params.set("currency", state.currency);
    params.set("bcn_donation_frequency", state.frequency);
    var amt = getChosenAmount();
    if(amt) params.set("bcn_donation_amount", String(amt));
    return baseURL + (baseURL.indexOf('?')>-1 ? '&' : '?') + params.toString();
  }

  currencySelect.addEventListener("change", function(){ onCurrencyChange(currencySelect.value); });
  freqBtns.forEach(function(btn){ btn.addEventListener("click", function(){ onFrequencyChange(btn.getAttribute("data-frequency")); }); });

  function setCustomVisible(show){
    if(show){
      customWrap.hidden = false;
      customWrap.style.display = "flex";
      customToggle.textContent = "Hide custom amount";
      customToggle.setAttribute("aria-expanded","true");
      customAmountInput.focus();
    } else {
      customWrap.hidden = true;
      customWrap.style.display = "none";
      customToggle.textContent = "Custom amount";
      customToggle.setAttribute("aria-expanded","false");
    }
  }
  function isCustomVisible(){ return !customWrap.hidden && customWrap.style.display !== "none"; }

  customToggle.addEventListener("click", function(){ setCustomVisible(!isCustomVisible()); });

  customAmountInput.addEventListener("input", function(){
    Array.prototype.slice.call(amountBtnsWrap.querySelectorAll(".pgt-btn-amount")).forEach(function(el){ el.setAttribute("aria-pressed","false"); });
    state.amountSelected = null;
    updateNextButton();
  });

  nextBtn.addEventListener("click", function(){
    window.location.href = buildNextURL();
  });

  // Boot
  (function init(){
    setCustomVisible(false);
    renderFrequencyUI();
    renderAmountPresets();

    fetchGeoCurrency().then(function(detected){
      if(detected && detected !== state.currency){
        onCurrencyChange(detected);
      } else {
        fetchPresets(state.currency).then(function(p){
          state.amountPresets = p;
          renderAmountPresets();
        });
      }
    });
  }());
}());
