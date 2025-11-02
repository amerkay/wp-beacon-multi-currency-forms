(function(){
  // Expect localized WPBCD_CTA_DATA: { beaconAccountName, formsByCurrency:{CODE:{id,symbol},...}, baseURL, defaultCurrency }
  if (typeof WPBCD_CTA_DATA !== 'object') return;

  var formsByCurrency = WPBCD_CTA_DATA.formsByCurrency || {};
  var baseURL = WPBCD_CTA_DATA.baseURL || (location.origin + "/donation-form/");
  var DEFAULT_CURRENCY = WPBCD_CTA_DATA.defaultCurrency || '';

  // Check if we have any currencies configured
  if (!Object.keys(formsByCurrency).length) {
    console.warn('WPBCD: No currencies configured. Please configure donation forms in settings.');
    return;
  }

  var DEFAULT_PRESETS = { single:[10,20,30], monthly:[5,10,15], annual:[50,100,200] };

  // Determine default currency from available ones
  var availableCurrencies = Object.keys(formsByCurrency);
  var defaultCurrency = DEFAULT_CURRENCY && availableCurrencies.indexOf(DEFAULT_CURRENCY) >= 0
    ? DEFAULT_CURRENCY
    : availableCurrencies[0]; // Fallback to first available

  // Geo currency via ajax endpoint (JS API must be enabled in the GeoIP plugin)
  function fetchGeoCurrency(){
    var endpoint = "/wp-admin/admin-ajax.php?action=geoip_detect2_get_info_from_current_ip";
    return fetch(endpoint, { credentials:"same-origin", cache:"no-store" })
      .then(function(res){ if(!res.ok) throw new Error("HTTP "+res.status); return res.json(); })
      .then(function(record){
        var code = String( (record && record.extra && (record.extra.currency_code || record.extra.currencyCode)) || "" ).toUpperCase();
        // Return detected currency only if it's available in our forms
        return formsByCurrency[code] ? code : defaultCurrency;
      })
      .catch(function(){ return defaultCurrency; });
  }

  // State
  var state = {
    currency: defaultCurrency,
    frequency: "monthly",
    allowedFrequencies: [], // Will be set after parsing data attributes
    amountPresets: { single:[], monthly:[], annual:[] }, // Will be set after parsing data attributes
    amountSelected: null
  };

  // Elements
  var wrap = document.getElementById("wpbcd-donate");
  if(!wrap) return;

  var freqBtns = Array.prototype.slice.call(wrap.querySelectorAll(".wpbcd-btn-frequency"));
  var amountBtnsWrap = document.getElementById("wpbcd-amount-buttons");
  var customWrap = document.getElementById("wpbcd-custom-wrap");
  var customToggle = document.getElementById("wpbcd-toggle-custom");
  var customAmountInput = document.getElementById("wpbcd-custom-amount");
  var currencySymbol = document.getElementById("wpbcd-currency-symbol");
  var currencySelect = document.getElementById("wpbcd-currency-select");
  var nextBtn = document.getElementById("wpbcd-next");

  // Parse custom URL parameters from data attribute
  var customParams = {};
  try {
    var customParamsAttr = wrap.getAttribute('data-custom-params');
    if (customParamsAttr) {
      customParams = JSON.parse(customParamsAttr);
    }
  } catch(e) {
    console.warn('WPBCD: Failed to parse custom params', e);
  }

  // Parse allowed frequencies from data attribute
  var configuredFrequencies = ["single", "monthly", "annual"]; // Default
  try {
    var allowedFreqAttr = wrap.getAttribute('data-allowed-frequencies');
    if (allowedFreqAttr) {
      var parsed = JSON.parse(allowedFreqAttr);
      if (Array.isArray(parsed) && parsed.length > 0) {
        configuredFrequencies = parsed;
      }
    }
  } catch(e) {
    console.warn('WPBCD: Failed to parse allowed frequencies', e);
  }

  // Parse default presets from data attribute
  var configuredPresets = { single:[10,20,30], monthly:[5,10,15], annual:[50,100,200] }; // Default
  try {
    var presetsAttr = wrap.getAttribute('data-default-presets');
    if (presetsAttr) {
      var parsed = JSON.parse(presetsAttr);
      if (parsed && typeof parsed === 'object') {
        configuredPresets = {
          single: Array.isArray(parsed.single) && parsed.single.length > 0 ? parsed.single : DEFAULT_PRESETS.single,
          monthly: Array.isArray(parsed.monthly) && parsed.monthly.length > 0 ? parsed.monthly : DEFAULT_PRESETS.monthly,
          annual: Array.isArray(parsed.annual) && parsed.annual.length > 0 ? parsed.annual : DEFAULT_PRESETS.annual
        };
      }
    }
  } catch(e) {
    console.warn('WPBCD: Failed to parse default presets', e);
  }

  // Initialize state with configured values
  state.allowedFrequencies = configuredFrequencies;
  state.amountPresets = {
    single: configuredPresets.single.slice(0),
    monthly: configuredPresets.monthly.slice(0),
    annual: configuredPresets.annual.slice(0)
  };

  // Helper to get valid frequency
  function getValidFrequency(preferredFreq){
    // If preferred frequency is allowed, use it
    if (state.allowedFrequencies.indexOf(preferredFreq) >= 0) return preferredFreq;
    // Otherwise return first allowed frequency
    return state.allowedFrequencies[0] || "monthly";
  }

  // Ensure initial frequency is valid
  state.frequency = getValidFrequency(state.frequency);

  currencySelect.value = state.currency;
  currencySymbol.textContent = formsByCurrency[state.currency].symbol;

  // Helpers
  function setSelected(btns, value, attr){
    btns.forEach(function(b){
      var v = b.getAttribute('data-'+attr);
      b.setAttribute("aria-selected", v === value ? "true" : "false");
    });
  }
  function renderFrequencyUI(){ 
    setSelected(freqBtns, state.frequency, "frequency");
    // Show/hide buttons based on allowed frequencies
    freqBtns.forEach(function(btn){
      var freq = btn.getAttribute("data-frequency");
      var isAllowed = state.allowedFrequencies.indexOf(freq) >= 0;
      btn.style.display = isAllowed ? "" : "none";
      if (!isAllowed) btn.setAttribute("aria-hidden", "true");
      else btn.removeAttribute("aria-hidden");
    });
  }

  function renderAmountPresets(){
    amountBtnsWrap.innerHTML = "";
    var group = (state.frequency === "single")
      ? state.amountPresets.single
      : (state.frequency === "annual") ? state.amountPresets.annual : state.amountPresets.monthly;

    var safeGroup = (Object.prototype.toString.call(group)==='[object Array]' && group.length) ? group : DEFAULT_PRESETS[state.frequency];

    safeGroup.forEach(function(v){
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "wpbcd-tab wpbcd-btn-amount";
      btn.textContent = formsByCurrency[state.currency].symbol + String(Number(v));
      btn.setAttribute("aria-pressed","false");
      btn.setAttribute("data-amount", String(v));
      btn.addEventListener("click", function(){
        Array.prototype.slice.call(amountBtnsWrap.querySelectorAll(".wpbcd-btn-amount")).forEach(function(el){ el.setAttribute("aria-pressed","false"); });
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
  
  function applyBrandColor(color){
    if (!color) return;
    // Don't override if a color is already set inline (e.g., from Elementor settings)
    var existingColor = wrap && wrap.style.getPropertyValue('--wpbcd-brand');
    if (existingColor && existingColor.trim()) return;
    
    // Update the CSS variable - color-mix in CSS handles the darker shade automatically
    if (wrap) {
      wrap.style.setProperty('--wpbcd-brand', color);
    }
  }

  function fetchPresets(currency){
    var formId = formsByCurrency[currency].id;
    var beaconAccountName = WPBCD_CTA_DATA.beaconAccountName;
    var url = "https://portal.beaconproducts.co.uk/v1/account/" + beaconAccountName + "/form/" + formId + "?fp=x";
    return fetch(url, { credentials:"omit", cache:"no-store" })
      .then(function(res){ if(!res.ok) throw new Error("HTTP "+res.status); return res.json(); })
      .then(function(data){
        var sections = (data && data.sections) || [];
        var donationSection = null;
        for (var i=0;i<sections.length;i++){
          if (sections[i] && sections[i].key === "donation_amount"){ donationSection = sections[i]; break; }
        }
        
        // Parse allowed frequencies
        var allowedFreqs = (donationSection && donationSection.allowed_frequencies) || [];
        var normalizedFreqs = [];
        for (var j=0; j<allowedFreqs.length; j++){
          var freq = String(allowedFreqs[j]).toLowerCase();
          if (freq === "monthly" || freq === "annual" || freq === "single") {
            normalizedFreqs.push(freq);
          }
        }
        // Fallback to all if none specified
        if (!normalizedFreqs.length) normalizedFreqs = ["single", "monthly", "annual"];
        
        // Extract brand color if available
        var brandColor = data && data.color ? String(data.color) : null;
        
        return {
          single:  parsePresetArray(donationSection && donationSection.preset_single_amounts),
          monthly: parsePresetArray(donationSection && donationSection.preset_monthly_amounts),
          annual:  parsePresetArray(donationSection && donationSection.preset_annual_amounts),
          allowedFrequencies: normalizedFreqs,
          brandColor: brandColor
        };
      })
      .catch(function(){ 
        return { 
          single:DEFAULT_PRESETS.single, 
          monthly:DEFAULT_PRESETS.monthly, 
          annual:DEFAULT_PRESETS.annual,
          allowedFrequencies: ["single", "monthly", "annual"],
          brandColor: null
        }; 
      });
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
      state.allowedFrequencies = p.allowedFrequencies || ["single", "monthly", "annual"];
      // Ensure current frequency is valid, otherwise switch to first allowed
      state.frequency = getValidFrequency(state.frequency);
      // Apply brand color if available
      if (p.brandColor) applyBrandColor(p.brandColor);
      renderFrequencyUI();
      renderAmountPresets(); // update grid with live values
    });
  }

  function onFrequencyChange(newFreq){
    // Only allow changing to frequencies that are allowed
    if (state.allowedFrequencies.indexOf(newFreq) < 0) return;
    
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
    
    // Append custom URL parameters
    if (customParams && typeof customParams === 'object') {
      for (var key in customParams) {
        if (customParams.hasOwnProperty(key) && customParams[key]) {
          params.set(key, String(customParams[key]));
        }
      }
    }
    
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
    Array.prototype.slice.call(amountBtnsWrap.querySelectorAll(".wpbcd-btn-amount")).forEach(function(el){ el.setAttribute("aria-pressed","false"); });
    state.amountSelected = null;
    updateNextButton();
  });

  customAmountInput.addEventListener("keydown", function(e){
    if (e.key === "Enter" || e.keyCode === 13) {
      e.preventDefault();
      // Only submit if form is valid (button is enabled)
      if (!nextBtn.disabled) {
        window.location.href = buildNextURL();
      }
    }
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
          state.allowedFrequencies = p.allowedFrequencies || ["single", "monthly", "annual"];
          // Ensure initial frequency is valid
          state.frequency = getValidFrequency(state.frequency);
          // Apply brand color if available
          if (p.brandColor) applyBrandColor(p.brandColor);
          renderFrequencyUI();
          renderAmountPresets();
        });
      }
    });
  }());
}());
