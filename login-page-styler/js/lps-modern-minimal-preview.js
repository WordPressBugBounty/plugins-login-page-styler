/* Change the selected template in the existing iframe without replacing its preview engine. */
(function () {
  'use strict';
  function init() {
    var select = document.getElementById('lps_design_preset');
    var frame = document.getElementById('lps-iframe');
    if (!select || !frame) return;
    function getValue(id) {
      var field = document.getElementById(id);
      return field ? String(field.value || '').trim() : '';
    }
    function update() {
      try {
        var doc = frame.contentDocument;
        if (!doc || !doc.body || !doc.head) return;
        doc.body.classList.toggle('lps-modern-minimal', select.value === 'modern-minimal');
        doc.body.classList.toggle('lps-nature-pro', select.value === 'nature-pro');
        doc.body.classList.toggle('lps-noir-editorial', select.value === 'noir-editorial');
        doc.body.classList.toggle('lps-split-studio', select.value === 'split-studio');
        doc.body.classList.toggle('lps-aurora-glass', select.value === 'aurora-glass');
        // Mirror the saved-page conditional class while editing unsaved form colors.
        // An empty form surface lets Aurora's outer card be the only glass panel.
        var customFormColor = getValue('lps_login_form_color');
        var customFormImage = getValue('lps_login_form_bg');
        doc.body.classList.toggle('lps-aurora-custom-form-background',
          select.value === 'aurora-glass' &&
          (/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(customFormColor) || /^https?:\/\//i.test(customFormImage)));

        // The same split panel is emitted by PHP on the real login page.
        // In the iframe, create it once and toggle it when the template changes.
        var splitPanel = doc.getElementById('lps-split-panel');
        if (!splitPanel && select.value === 'split-studio') {
          splitPanel = doc.createElement('aside');
          splitPanel.id = 'lps-split-panel';
          splitPanel.className = 'lps-split-panel';
          splitPanel.setAttribute('aria-label','Welcome');
          splitPanel.innerHTML = '<div class="lps-split-art" aria-hidden="true"><span class="lps-split-orbit"></span><span class="lps-split-glass"></span><span class="lps-split-spark"></span></div><div class="lps-split-brand"><span class="lps-split-monogram">✦</span><span>STUDIO / ACCESS</span></div><div class="lps-split-story"><span class="lps-split-eyebrow">A BETTER WAY TO BEGIN</span><h2>Your next chapter starts here.</h2><p>A thoughtfully designed space to make every sign-in feel exceptional.</p><span class="lps-split-story-line"></span></div><div class="lps-split-footnote">DESIGNED FOR WHAT’S NEXT <span>↗</span></div>';
          doc.body.insertBefore(splitPanel,doc.body.firstChild);
        }
        if (splitPanel) splitPanel.hidden = select.value !== 'split-studio';
        // Nature Pro and Aurora move the real logo, never clone it.
        // Aurora's layout helper is shared with the published WordPress login.
        var loginRoot = doc.getElementById('login');
        if (window.lpsAuroraLayout) {
          window.lpsAuroraLayout(doc, select.value === 'aurora-glass');
        }
        if (loginRoot) {
          var logoHeading = loginRoot.querySelector('h1');
          var loginForm = loginRoot.querySelector('#loginform, #registerform, #lostpasswordform');
          if (logoHeading && loginForm && select.value !== 'aurora-glass') {
            if (select.value === 'nature-pro' && logoHeading.parentNode !== loginForm) {
              loginForm.insertBefore(logoHeading, loginForm.firstChild);
            } else if (select.value !== 'nature-pro' && logoHeading.parentNode === loginForm) {
              loginRoot.insertBefore(logoHeading, loginForm);
            }
          }
        }
        // Position changes are handled by the existing Customizer mapping.
        // Never dispatch a change event from update(): it recursively calls update.
        var natureLink = document.getElementById('lps-nature-preview-css');
        if (natureLink && !doc.getElementById('lps-nature-preview-style')) { var nl = doc.createElement('link'); nl.id = 'lps-nature-preview-style'; nl.rel = 'stylesheet'; nl.href = natureLink.href; doc.head.appendChild(nl); }
        var splitLink = document.getElementById('lps-split-studio-preview-css');
        if (splitLink && !doc.getElementById('lps-split-preview-style')) { var sl = doc.createElement('link'); sl.id = 'lps-split-preview-style'; sl.rel = 'stylesheet'; sl.href = splitLink.href; doc.head.appendChild(sl); }
        var auroraLink = document.getElementById('lps-aurora-glass-preview-css');
        if (auroraLink && !doc.getElementById('lps-aurora-preview-style')) { var al = doc.createElement('link'); al.id = 'lps-aurora-preview-style'; al.rel = 'stylesheet'; al.href = auroraLink.href; doc.head.appendChild(al); }
        var noirLink = document.getElementById('lps-noir-editorial-preview-css');
        if (noirLink && !doc.getElementById('lps-noir-preview-style')) { var el = doc.createElement('link'); el.id = 'lps-noir-preview-style'; el.rel = 'stylesheet'; el.href = noirLink.href; doc.head.appendChild(el); }
        var style = doc.getElementById('lps-modern-minimal-preview-style');
        if (!style) {
          style = doc.createElement('link');
          style.id = 'lps-modern-minimal-preview-style';
          style.rel = 'stylesheet';
          style.href = (window.lpsModernMinimalCssUrl || '');
          // Reuse the admin stylesheet URL so the preview and published page use identical CSS.
          var adminLink = document.getElementById('lps-modern-minimal-preview-css-css');
          if (adminLink) style.href = adminLink.href;
          if (style.href) doc.head.appendChild(style);
          var natureLink = document.getElementById('lps-nature-preview-css');
          if (natureLink && !doc.getElementById('lps-nature-preview-style')) {
            var natureStyle = doc.createElement('link'); natureStyle.id = 'lps-nature-preview-style'; natureStyle.rel = 'stylesheet'; natureStyle.href = natureLink.href; doc.head.appendChild(natureStyle);
          }
        }
        // Apply unsaved settings with the same selectors as the published page.
        var css = '';
        if (select.value === 'modern-minimal' || select.value === 'nature-pro' || select.value === 'noir-editorial' || select.value === 'split-studio' || select.value === 'aurora-glass') {
          var logo = getValue('lps_login_logo');
          var width = parseInt(getValue('lps_login_logo_width'), 10) || 0;
          var height = parseInt(getValue('lps_login_logo_height'), 10) || 0;
          if (/^https?:\/\//i.test(logo)) css += 'body.login.lps-modern-minimal #login h1 a{background-image:url("' + logo.replace(/["\\]/g, '') + '")!important;}';
          if (width || height) css += 'body.login.lps-modern-minimal #login h1 a{' + (width ? 'width:' + width + 'px!important;' : '') + (height ? 'height:' + height + 'px!important;' : '') + 'background-size:contain!important;}';
          var color = getValue('lps_login_background_color');
          var image = getValue('lps_body_bg_img');
          var size = getValue('lps_login_bg_size');
          var position = getValue('lps_login_bg_position');
          var repeat = getValue('lps_login_bg_repeat');
          var validColor = /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(color);
          var validImage = /^https?:\/\//i.test(image);
          var bgType = getValue('lps_background_type') || 'auto';
          var gradOne = getValue('lps_gradient_color_one');
          var gradTwo = getValue('lps_gradient_color_two');
          var gradStyle = getValue('lps_gradient_style');
          var gradAngle = Math.min(360, Math.max(0, parseInt(getValue('lps_gradient_angle'),10) || 0));
          var safeOne = /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(gradOne) ? gradOne : '#e7e8ff';
          var safeTwo = /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(gradTwo) ? gradTwo : '#d9f4f0';
          var bodyCss = 'background-color:' + (validColor ? color : (select.value === 'nature-pro' ? '#123e35' : (select.value === 'noir-editorial' ? '#0b1020' : (select.value === 'split-studio' ? '#f8faff' : (select.value === 'aurora-glass' ? '#101a4c' : '#f5f7fc'))))) + '!important;';
          if (bgType === 'gradient') {
            bodyCss += 'background-image:' + (gradStyle === 'radial' ? 'radial-gradient(circle, ' + safeOne + ', ' + safeTwo + ')' : 'linear-gradient(' + gradAngle + 'deg, ' + safeOne + ', ' + safeTwo + ')') + '!important;';
          } else if (bgType === 'solid') {
            bodyCss += 'background-image:none!important;';
          } else if (validImage) {
            bodyCss += 'background-image:url("' + image.replace(/["\\]/g, '') + '")!important;';
          } else if (validColor) {
            bodyCss += 'background-image:none!important;';
          } else {
            if (select.value === 'nature-pro' || select.value === 'noir-editorial' || select.value === 'split-studio' || select.value === 'aurora-glass') { /* Keep the template background. */ } else { bodyCss += 'background-image:radial-gradient(ellipse at 12% 12%,#e7e8ff 0%,transparent 44%),radial-gradient(ellipse at 90% 85%,#d9f4f0 0%,transparent 45%)!important;'; }
          }
          if (['cover','contain','auto'].indexOf(size) !== -1) bodyCss += 'background-size:' + size + '!important;';
          if (['center center','center top','center bottom','left center','right center'].indexOf(position) !== -1) bodyCss += 'background-position:' + position + '!important;';
          if (['repeat','no-repeat','repeat-x','repeat-y'].indexOf(repeat) !== -1) bodyCss += 'background-repeat:' + repeat + '!important;';
          css += 'body.login.' + (select.value === 'nature-pro' ? 'lps-nature-pro' : (select.value === 'noir-editorial' ? 'lps-noir-editorial' : (select.value === 'split-studio' ? 'lps-split-studio' : (select.value === 'aurora-glass' ? 'lps-aurora-glass' : 'lps-modern-minimal')))) + '{' + bodyCss + '}';
          if (select.value === 'nature-pro') css = css.replace(/body\.login\.lps-modern-minimal/g, 'body.login.lps-nature-pro');
          if (select.value === 'noir-editorial') css = css.replace(/body\.login\.lps-modern-minimal/g, 'body.login.lps-noir-editorial');
          if (select.value === 'split-studio') css = css.replace(/body\.login\.lps-modern-minimal/g, 'body.login.lps-split-studio');
          if (select.value === 'aurora-glass') css = css.replace(/body\.login\.lps-modern-minimal/g, 'body.login.lps-aurora-glass');
        }
        var overrides = doc.getElementById('lps-modern-minimal-live-overrides');
        if (!overrides) { overrides = doc.createElement('style'); overrides.id = 'lps-modern-minimal-live-overrides'; doc.head.appendChild(overrides); }
        // Shared backdrop blur: all login designs use the same control.
        var blurInput = document.getElementById('lps_nature_form_blur');
        var rawBlur = blurInput ? Number(blurInput.value) : ((select.value === 'nature-pro'||select.value === 'aurora-glass') ? 24 : 0);
        var blur = Number.isFinite(rawBlur) ? Math.max(0,Math.min(40,rawBlur)) : 0;
        var blurValue = 'blur(' + blur + 'px) saturate(135%)';
        css += 'body.login #loginform,body.login #registerform,body.login #lostpasswordform{' +
               '-webkit-backdrop-filter:' + blurValue + '!important;' +
               'backdrop-filter:' + blurValue + '!important;}';
        if (select.value === 'split-studio' && blur > 0) {
          var formColor = document.getElementById('lps_login_form_color');
          var formImage = document.getElementById('lps_login_form_bg');
          if ((!formColor || !formColor.value) && (!formImage || !formImage.value)) {
            css += 'body.login.lps-split-studio #loginform,body.login.lps-split-studio #registerform,body.login.lps-split-studio #lostpasswordform{background:rgba(255,255,255,.78)!important;}';
          }
        }
        overrides.textContent = css;
        // Ensure this preview-only rule follows the Nature Pro stylesheet.
        if (doc.head.lastElementChild !== overrides) doc.head.appendChild(overrides);
        // Do not re-append on each update; this can trigger preview observers.
      } catch (e) { /* The preview can be reloading. */ }
    }
    // WordPress Iris fires its color-change callback before it has finished
    // updating the original input. Read the color on the next frame instead.
    var lastBackgroundColor = null;
    var lastBackgroundImage = null;
    function scheduleUpdate() { window.requestAnimationFrame(update); }
    select.addEventListener('change', scheduleUpdate);
    document.addEventListener('input', scheduleUpdate);
    document.addEventListener('change', scheduleUpdate);
    // WP media controls and color picker may update inputs programmatically.
    if (window.jQuery) {
      window.jQuery(document).on('change.colorpicker irischange', function () {
        window.setTimeout(scheduleUpdate, 0);
      });
      // WP's Clear button can update the hidden color field after its click.
      window.jQuery(document).on('click', '.wp-picker-clear', function () {
        window.setTimeout(scheduleUpdate, 0);
      });
    }
    // Catch changes made by the picker without native input/change events.
    // Compare the value only; never dispatch events or mutate the parent DOM.
    window.setInterval(function () {
      if (document.hidden) return;
      var current = getValue('lps_login_background_color');
      var image = getValue('lps_body_bg_img');
      if (current !== lastBackgroundColor || image !== lastBackgroundImage) {
        lastBackgroundColor = current;
        lastBackgroundImage = image;
        scheduleUpdate();
      }
    }, 200);
    // A refreshed iframe can finish loading before other preview scripts inject
    // their styles. Reapply our unsaved settings after those late changes too.
    // The observer watches only the iframe head and never changes saved settings.
    var previewHeadObserver = null;
    var refreshUpdateQueued = false;
    function queueRefreshUpdate() {
      if (refreshUpdateQueued) return;
      refreshUpdateQueued = true;
      window.requestAnimationFrame(function () {
        refreshUpdateQueued = false;
        update();
      });
    }
    function onPreviewLoad() {
      if (previewHeadObserver) previewHeadObserver.disconnect();
      update();
      var doc;
      try { doc = frame.contentDocument; } catch (e) { return; }
      if (!doc || !doc.head || !window.MutationObserver) return;
      previewHeadObserver = new MutationObserver(function (changes) {
        // Ignore our own preview style updates and reordering to prevent loops.
        var otherStyleChanged = changes.some(function (change) {
          if (change.type !== 'childList') return false;
          return Array.prototype.some.call(change.addedNodes, function (node) {
            return node.nodeType === 1 && node.id !== 'lps-modern-minimal-live-overrides' &&
              (node.tagName === 'STYLE' || node.tagName === 'LINK');
          });
        });
        if (otherStyleChanged) queueRefreshUpdate();
      });
      previewHeadObserver.observe(doc.head, { childList: true });
      // Cover late inline styling and iframe initialization after the load event.
      [0, 100, 350].forEach(function (delay) {
        window.setTimeout(function () {
          if (frame.contentDocument === doc) update();
        }, delay);
      });
    }
    frame.addEventListener('load', onPreviewLoad);
    onPreviewLoad();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
}());
