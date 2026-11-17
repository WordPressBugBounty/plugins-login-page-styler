/* Keep every Customizer color picker synchronized with the existing iframe.
 * No synthetic change events: those previously caused a frozen settings page.
 */
(function () {
  'use strict';
  var fields = {
    lps_textlogo_color: ['#login h1 a','color'],
    lps_textlogo_color_hover: ['#login h1 a:hover','color'],
    lps_login_form_color: ['#login form','background-color'],
    lps_login_form_border_color: ['#login form','border-color'],
    lps_login_label_color: ['#login label','color'],
    lps_login_form_input_color: ['#login form .input','background-color'],
    lps_login_form_input_text_color: ['#login form .input','color'],
    lps_login_form_input_feild_border_color: ['#login form .input','border-color'],
    lps_login_button_color: ['#login .button-primary','background-color'],
    lps_login_button_color_hover: ['#login .button-primary:hover','background-color'],
    lps_login_button_text_color: ['#login .button-primary','color'],
    lps_login_button_text_color_hover: ['#login .button-primary:hover','color'],
    lps_login_button_border_color: ['#login .button-primary','border-color'],
    lps_login_button_border_color_hover: ['#login .button-primary:hover','border-color'],
    lps_login_nav_color: ['#nav a, #backtoblog a','color'],
    lps_login_nav_hover_color: ['#nav a:hover, #backtoblog a:hover','color'],
    lps_login_nav_bgcolor: ['#nav a, #backtoblog a','background-color'],
    lps_login_nav_bgcolor_hover: ['#nav a:hover, #backtoblog a:hover','background-color'],
    lps_copyright_color: ['.login .copyright','color'],
    lps_notice_textcolor: ['body.login #login #login_error, body.login #login .message, body.login #login .notice, body.login #login .success','color'],
    lps_notice_bgcolor: ['body.login #login #login_error, body.login #login .message, body.login #login .notice, body.login #login .success','background-color']
  };
  function start() {
    var frame = document.getElementById('lps-iframe');
    if (!frame) return;
    var last = {}, lastDocument = null;
    function refresh() {
      try {
        var doc = frame.contentDocument;
        if (!doc || !doc.head) return;
        var changed = doc !== lastDocument;
        if (changed) { last = {}; lastDocument = doc; }
        // Show a sample only while the notice settings accordion is open.
        var section = document.getElementById('section8');
        var login = doc.getElementById('login');
        var sample = doc.getElementById('lps-preview-notice');
        if (section && section.checked && login && !sample) {
          sample = doc.createElement('p');
          sample.id = 'lps-preview-notice';
          sample.className = 'message';
          sample.textContent = 'Preview: This is a sample login notice.';
          var form = login.querySelector('form');
          if (form) form.parentNode.insertBefore(sample, form);
        } else if (sample && (!section || !section.checked)) {
          sample.remove();
        }
        // Unsaved values replace the saved notice CSS, including when cleared.
        var savedNotice = doc.getElementById('lps-notice-custom-colors');
        if (savedNotice) { savedNotice.remove(); changed = true; }
        var opacityInput = document.getElementById('lps_notice_bgcolor_opacity');
        var opacity = opacityInput && opacityInput.value !== '' ? Number(opacityInput.value) : 1;
        opacity = Number.isFinite(opacity) ? Math.max(0, Math.min(1, opacity)) : 1;
        if (last.noticeOpacity !== opacity) { last.noticeOpacity = opacity; changed = true; }
        var rules = [];
        Object.keys(fields).forEach(function (id) {
          var input = document.getElementById(id);
          if (!input) return;
          var value = (input.value || '').trim();
          if (last[id] !== value) { changed = true; last[id] = value; }
          // The old mapper writes inline styles. Remove those on Clear so
          // the selected template's original design can become visible.
          if (value === '') {
            var selector = fields[id][0];
            if (selector.indexOf(':hover') === -1) {
              doc.querySelectorAll(selector).forEach(function (element) {
                element.style.removeProperty(fields[id][1]);
              });
            }
          }
          if (/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(value)) {
            if (id === 'lps_notice_bgcolor') {
              var hex = value.slice(1);
              if (hex.length === 3) hex = hex.split('').map(function (c) { return c + c; }).join('');
              value = 'rgba(' + [parseInt(hex.slice(0,2),16), parseInt(hex.slice(2,4),16), parseInt(hex.slice(4,6),16), opacity].join(',') + ')';
            }
            rules.push(fields[id][0] + '{' + fields[id][1] + ':' + value + ' !important;}');
          }
        });
        if (!changed) return;
        var style = doc.getElementById('lps-color-live-sync');
        if (!style) {
          style = doc.createElement('style');
          style.id = 'lps-color-live-sync';
          doc.head.appendChild(style);
        }
        style.textContent = rules.join('\n');
      } catch (error) { /* iframe may be reloading */ }
    }
    // Picker inputs can be updated programmatically without input events.
    // Poll values only; never dispatch events or modify parent controls.
    window.setInterval(refresh, 180);
    frame.addEventListener('load', refresh);
    document.addEventListener('input', refresh);
    document.addEventListener('change', refresh);
    refresh();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
}());
