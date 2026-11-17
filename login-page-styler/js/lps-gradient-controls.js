/* Gradient settings UI: read values only; never dispatch synthetic change events. */
(function () {
 'use strict';
 function init() {
  var type=document.getElementById('lps_background_type');
  var fields=document.getElementById('lps-gradient-fields');
  var style=document.getElementById('lps_gradient_style');
  var angle=document.getElementById('lps_gradient_angle');
  var angleRow=document.getElementById('lps-gradient-angle-row');
  var angleValue=document.getElementById('lps-gradient-angle-value');
  var reset=document.getElementById('lps-gradient-reset');
  if (!type || !fields) return;
  function refresh() {
   fields.style.display=type.value==='gradient'?'block':'none';
   if(angleRow)angleRow.style.display=style.value==='radial'?'none':'block';
   if(angleValue)angleValue.textContent=angle.value+'°';
  }
  type.addEventListener('change',refresh);
  style.addEventListener('change',refresh);
  angle.addEventListener('input',refresh);
  if(reset)reset.addEventListener('click',function(){
   var defaults={'lps_gradient_color_one':'#e7e8ff','lps_gradient_color_two':'#d9f4f0'};
   Object.keys(defaults).forEach(function(id){var el=document.getElementById(id);if(!el)return;
    if(window.jQuery && window.jQuery(el).data('wpWpColorPicker'))window.jQuery(el).wpColorPicker('color',defaults[id]);
    else {el.value=defaults[id];el.dispatchEvent(new Event('input',{bubbles:true}));}
   });
   style.value='linear';angle.value='135';refresh();
  });
  refresh();
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
}());
