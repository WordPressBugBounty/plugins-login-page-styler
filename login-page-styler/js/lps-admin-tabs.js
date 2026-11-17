/**
 * Preserve the selected settings tab when WordPress options.php redirects after Save.
 * This does not touch any plugin option and only runs on the main LPS admin screen.
 */
(function () {
  'use strict';
  var STORAGE_KEY = 'lps-admin-active-tab-v1';
  var tabIds = ['tab0', 'tab1', 'tab3', 'tab4', 'tab5', 'tab6', 'tab7', 'tab8'];

  function isValid(id) { return tabIds.indexOf(id) !== -1; }
  function selectedTab() {
    var checked = document.querySelector('.lps-modern-shell > input.tabin:checked');
    return checked && isValid(checked.id) ? checked.id : 'tab0';
  }
  function remember(id) {
    if (!isValid(id)) return;
    try { window.sessionStorage.setItem(STORAGE_KEY, id); } catch (ignore) {}
  }
  function storedTab() {
    var hash = window.location.hash.replace(/^#lps-/, '');
    if (isValid(hash)) return hash;
    try {
      var saved = window.sessionStorage.getItem(STORAGE_KEY);
      if (isValid(saved)) return saved;
    } catch (ignore) {}
    return null;
  }
  function init() {
    var shell = document.querySelector('.lps-modern-shell');
    if (!shell || !document.getElementById('tab0')) return;
    var restored = storedTab();
    if (restored) {
      var radio = document.getElementById(restored);
      if (radio && !radio.disabled) radio.checked = true;
    }
    tabIds.forEach(function (id) {
      var input = document.getElementById(id);
      if (!input) return;
      input.addEventListener('change', function () {
        if (!input.checked) return;
        remember(id);
        // Deep-link to a tab; the saved value covers redirects that drop the hash.
        if (window.history && window.history.replaceState) {
          window.history.replaceState(null, '', window.location.pathname + window.location.search + '#lps-' + id);
        }
      });
    });
    // Capture the chosen tab before any settings form is submitted.
    shell.querySelectorAll('form').forEach(function (form) {
      form.addEventListener('submit', function () { remember(selectedTab()); });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else { init(); }
}());
