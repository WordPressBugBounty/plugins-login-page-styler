/* Free vs Pro Customizer - keep original controls and live design preview.
 * Mark Pro settings, omit POST names at submission, and prevent any Pro-only field from
 * being included in the live Settings API submission. No premium frontend code.
 */
(function(){'use strict';
 var pro=["lps_gfonttext_logo", "lps_gradient_style", "lps_gradient_angle", "lps_background_animation", "lps_login_form_bg", "lps_login_form_color_opacity", "lps_nature_form_blur", "lps_login_animation", "lps_login_form_border_style", "lps_login_form_border_size", "lps_login_form_input_color_opacity", "lps_login_form_input_border_style", "lps_login_form_input_border_size", "lps_box_shadow_vertical", "lps_box_shadow_horizontal", "lps_box_shadow_blur", "lps_box_shadow_color", "lps_box_shadow_opacity", "lps_box_shadow_spread", "lps_gfontlab", "lps_gfont_inputtext", "lps_gfontlink", "lps_gfontmsg", "lps_gfontbtn", "lps_login_button_color_hover", "lps_login_button_text_color_hover", "lps_login_button_border_color", "lps_login_button_border_color_hover", "lps_login_nav_hover_color", "lps_login_nav_bgcolor", "lps_login_nav_bgcolor_hover", "lps_gfont_copyright", "lps_notice_bgcolor_opacity", "lps_body_bg_slideshow", "lps_slideshow_animation_style", "lps_slideshow_time", "lps_user_custom_css", "lps_background_animation"];
 var keyset=new Set(pro);
 function init(){
  var form=document.querySelector('#content1 form[action="options.php"]');
  if(!form)return;
  var groups={};
  pro.forEach(function(key){
   var controls=Array.from(form.querySelectorAll('[name="'+key+'"],[name="'+key+'[]"]'));
   if(!controls.length)return;
   controls.forEach(function(input){
    // Keep name until submit: the established live-preview engine looks up fields by form.elements.namedItem(name).
    input.dataset.lpsPremium='true';
    var wrapper=input.closest('tr')||input.closest('p')||input.closest('.lps-background-animation-field');
    if(!wrapper)return;
    if(!wrapper.classList.contains('lps-preview-only-setting')){
     wrapper.classList.add('lps-preview-only-setting');
     var lab=wrapper.querySelector('label');
     if(lab&&!lab.querySelector('.lps-customizer-pro-badge')){
      var badge=document.createElement('span');badge.className='lps-customizer-pro-badge';badge.textContent='Pro preview';lab.appendChild(badge);
     }
    }
   });
  });
  // Some inputs share a form row; the row is intentionally kept visible.
  var accordionLabels=Array.from(form.querySelectorAll('.accordion-label'));
  accordionLabels.forEach(function(label){
   var section=label.closest('.accordion');if(!section)return;
   var title=label.textContent.trim();
   var previewCount=section.querySelectorAll('.lps-preview-only-setting').length;
   if(!previewCount)return;
   if(title==='Google Fonts'||title==='Slider Background'||title==='Custom Css'){
    var badge=document.createElement('span');badge.className='lps-customizer-pro-badge lps-section-pro-badge';badge.textContent='Pro';label.appendChild(badge);
   }
  });
  var customCSS=document.getElementById('lps_user_custom_css');
  if(customCSS){customCSS.dataset.lpsPremium='true';}
  // Do not allow a form submit to accidentally include a premium setting name added by another script.
  form.addEventListener('submit',function(){
   Array.from(form.querySelectorAll('[name]')).forEach(function(input){
    var name=(input.name||'').replace(/\[\]$/,'');
    if(keyset.has(name))input.removeAttribute('name');
   });
  },true);
  // Background Animation preview has no save handler in Free.
  var notice=document.createElement('div');notice.className='lps-customizer-pro-intro';
  notice.innerHTML='<div class="lps-customizer-pro-intro__top"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span><strong>Explore Pro features</strong><span class="lps-customizer-pro-intro__badge">Pro</span></div><p>Try premium controls in the live preview. Only Free settings can be saved and published.</p><a href="'+(form.getAttribute('data-lps-upgrade-url')||'admin.php?page=lps_upgrade_pro')+'">Upgrade to Pro &rarr;</a>';
  var first=form.querySelector('.accordion');if(first)first.parentNode.insertBefore(notice,first);
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
