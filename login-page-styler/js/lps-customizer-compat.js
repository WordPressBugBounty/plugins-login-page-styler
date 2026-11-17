(function($){'use strict';$(function(){
var $form=$('#content1 form[action*="options.php"]'),$acc=$form.find('.accordion-container').first();
if(!$acc.length)return;
var sections=[
['section1','02 · Logo & identity','Logo image, text and link'],
['section2','03 · Page background','Background colours and images'],
['section3','04 · Login form','Position, form background, size and borders'],
['section3fields','05 · Fields & labels','Input backgrounds, field borders, labels and Remember Me'],
['section3shadow','06 · Form box shadow','Shadow color, opacity, offsets, blur and spread'],
['section5','07 · Login button','Button colours, size and shape'],
['section4','08 · Typography','Fonts for fields, labels and links'],
['section6','09 · Navigation links','Back-to-site and password links'],
['section7','10 · Footer','Copyright and footer styling'],
['section8','11 · Messages','Success and error notices'],
['section9','12 · Background slideshow','Rotating background images'],
['section10','13 · Custom CSS','Optional CSS written by you; your login page styling is applied automatically']];
sections.forEach(function(row){
var $input=$acc.find('input[id="'+row[0]+'"]').first();
if(!$input.length)return;
var $section=$input.closest('.accordion');
$section.insertBefore($acc.children('.lps-settings-actions').first());
// Keep .accordion-content immediately after the label: the original CSS relies on that sibling order.
$section.find('label.accordion-label').first().text(row[1]).attr('title',row[2]);
$section.find('.accordion-content').first().prepend($('<p class="lps-section-hint"></p>').text(row[2]));
});

});})(jQuery);

/* 9.8.1: keep legacy numeric fields and sliders in sync in both directions. */
(function(){document.addEventListener('DOMContentLoaded',function(){
 document.querySelectorAll('#content1 input[type="range"]').forEach(function(slider){
  var form=slider.form;if(!form)return;var number=null;
  if(slider.hasAttribute('oninput')){var match=slider.getAttribute('oninput').match(/amountInput[A-Za-z0-9_]+/);if(match)number=form.elements[match[0]];}
  if(!number){var parent=slider.closest('td, .lps-editor-field');if(parent)number=parent.querySelector('input[type="number"]');}
  if(!number||number===slider)return;
  number.addEventListener('input',function(){var n=Number(number.value);if(!Number.isFinite(n))return;var min=Number(slider.min||0),max=Number(slider.max||100);slider.value=String(Math.min(max,Math.max(min,n)));slider.dispatchEvent(new Event('input',{bubbles:true}));});
  number.addEventListener('change',function(){slider.dispatchEvent(new Event('change',{bubbles:true}));});
 });
});})();


/* Preview toolbar: device widths and explicit refresh, without touching styling controls. */
(function () {
  'use strict';
  function initPreviewToolbar() {
    var frame = document.getElementById('lps-iframe');
    var stage = document.querySelector('#content1 .lps-preview-stage');
    var buttons = document.querySelectorAll('#content1 .lps-device-button');
    var refresh = document.getElementById('lps-preview-refresh');
    if (!frame || !stage || !buttons.length) return;

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var size = button.getAttribute('data-preview-size');
        if (['desktop', 'tablet', 'mobile'].indexOf(size) === -1) return;
        stage.classList.remove('is-tablet', 'is-mobile');
        if (size !== 'desktop') stage.classList.add('is-' + size);
        buttons.forEach(function (item) {
          var active = item === button;
          item.classList.toggle('is-active', active);
          item.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
      });
    });

    if (refresh) {
      refresh.addEventListener('click', function () {
        // Reload the existing iframe URL, retaining the chosen device width.
        if (frame.contentWindow) frame.contentWindow.location.reload();
        else frame.src = frame.src;
      });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPreviewToolbar);
  else initPreviewToolbar();
})();

/* Keep taller Noir Editorial forms fully reachable inside the EXISTING preview.
   Do not alter the published site or add any preview controls. */
(function () {
  'use strict';
  function setupNoirPreviewHeight() {
    var frame = document.getElementById('lps-iframe');
    var stage = document.querySelector('#content1 .lps-preview-stage');
    if (!frame || !stage) return;
    function adjust() {
      var doc;
      try { doc = frame.contentDocument; } catch (e) { return; }
      if (!doc || !doc.body) return;
      var noir = doc.body.classList.contains('lps-noir-editorial');
      stage.classList.toggle('lps-noir-tall-preview', noir);
      if (!noir) {
        frame.style.removeProperty('height');
        frame.style.removeProperty('min-height');
        return;
      }
      // Measure the login content rather than viewport height. The form and
      // footer must remain visible even on shorter desktop previews.
      var login = doc.getElementById('login');
      if (!login) return;
      var rect = login.getBoundingClientRect();
      var natural = Math.ceil(Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight, rect.bottom + 36, rect.height + 110));
      var target = Math.max(820, Math.min(1500, natural + 12));
      if (Math.abs(frame.offsetHeight - target) > 8) {
        frame.style.setProperty('height', target + 'px', 'important');
        frame.style.setProperty('min-height', target + 'px', 'important');
      }
    }
    frame.addEventListener('load', function () {
      adjust();
      window.setTimeout(adjust, 250);
    });
    document.querySelectorAll('#content1 .lps-device-button, #lps-preview-refresh').forEach(function (button) {
      button.addEventListener('click', function () { window.setTimeout(adjust, 180); });
    });
    adjust();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupNoirPreviewHeight);
  else setupNoirPreviewHeight();
})();
