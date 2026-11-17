/* Admin-only interactive slideshow preview for BOTH editions.
 * Uses the existing image URL, effect, and timing controls; no new UI.
 * Premium values in Free are never saved or used by the live login page.
 */
(function () {
  'use strict';
  function init() {
    var frame = document.getElementById('lps-iframe');
    var first = document.getElementById('lps_body_bg_slideshow1');
    if (!frame || !first || !window.LPSBackgroundSlideshow) return;
    var inputs = Array.from(document.querySelectorAll('[id^="lps_body_bg_slideshow"]')).filter(function(el) {return el.tagName === 'INPUT';});
    var effect = document.getElementById('lps_slideshow_animation_style');
    var seconds = document.getElementById('lps_slideshow_time');
    var number = document.querySelector('input[name="amountInputSst"]');
    var pending = 0;
    function update() {
      var doc;
      try { doc = frame.contentDocument; } catch (e) { return; }
      if (!doc || !doc.body) return;
      var values = inputs.map(function(input) {return input.value.trim();}).filter(Boolean);
      window.LPSBackgroundSlideshow.mount(doc, {
        images: values,
        effect: effect ? effect.value : 'fade',
        seconds: seconds ? seconds.value : 3
      });
    }
    function schedule() {
      window.clearTimeout(pending);
      pending = window.setTimeout(update, 120);
    }
    inputs.forEach(function(input) { input.addEventListener('input', schedule); input.addEventListener('change', schedule); });
    [effect, seconds, number].forEach(function(input) {
      if (input) {input.addEventListener('input', schedule);input.addEventListener('change', schedule);}
    });
    frame.addEventListener('load', function() {window.setTimeout(update, 180);});
    document.querySelectorAll('.lps-upload-button').forEach(function(btn) { btn.addEventListener('click', function() {window.setTimeout(update, 1000);}); });
    window.setTimeout(update, 500);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
