/**
 * UTM Parameter Tracker
 * Tracks UTM parameters across all pages and stores them in a cookie for 180 days.
 * Handles atomic replacement: all UTM values are replaced when utm_source is present.
 */
(function() {
  'use strict';

  var COOKIE_NAME = 'wbcd_utm_params';
  var COOKIE_DAYS = 180;
  var UTM_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign'];

  /**
   * Get a cookie value by name
   */
  function getCookie(name) {
    var value = '; ' + document.cookie;
    var parts = value.split('; ' + name + '=');
    if (parts.length === 2) {
      return parts.pop().split(';').shift();
    }
    return null;
  }

  /**
   * Set a cookie with expiration
   */
  function setCookie(name, value, days) {
    var expires = '';
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
  }

  /**
   * Parse URL parameters
   */
  function getUrlParams() {
    var params = {};
    var searchParams = new URLSearchParams(window.location.search);
    
    UTM_PARAMS.forEach(function(param) {
      var value = searchParams.get(param);
      if (value) {
        params[param] = value;
      }
    });
    
    return params;
  }

  /**
   * Main tracking logic
   */
  function trackUtmParams() {
    var urlParams = getUrlParams();
    
    // Only proceed if utm_source is present (atomic replacement requirement)
    if (!urlParams.utm_source) {
      return;
    }

    // Create UTM object with all three params (use empty string if not present)
    var utmData = {
      utm_source: urlParams.utm_source || '',
      utm_medium: urlParams.utm_medium || '',
      utm_campaign: urlParams.utm_campaign || ''
    };

    // Store in cookie
    try {
      setCookie(COOKIE_NAME, JSON.stringify(utmData), COOKIE_DAYS);
    } catch (e) {
      console.warn('WBCD: Failed to store UTM parameters', e);
    }
  }

  /**
   * Get stored UTM parameters from cookie
   * This function is used by other scripts (like donate-form.js)
   */
  window.WBCD_getStoredUtm = function() {
    try {
      var cookieValue = getCookie(COOKIE_NAME);
      if (cookieValue) {
        return JSON.parse(decodeURIComponent(cookieValue));
      }
    } catch (e) {
      console.warn('WBCD: Failed to parse UTM cookie', e);
    }
    return null;
  };

  // Run tracking on page load
  trackUtmParams();

}());
