/* Split Studio only: unsaved admin controls and iframe rendering. */
(function(){'use strict';function boot(){
 var select=document.getElementById('lps_design_preset'), frame=document.getElementById('lps-iframe');if(!select||!frame)return;
 var section=document.getElementById('lps-split-settings');
 function val(key){var e=document.getElementById('lps_split_'+key);return e?(e.type==='checkbox'?(e.checked?'1':'0'):e.value):'';}
 function setText(parent,selector,value){if(!parent)return;var e=parent.querySelector(selector);if(e){e.textContent=value;e.hidden=!value;}}
 function sync(){if(section)section.hidden=select.value!=='split-studio';try{
 var doc=frame.contentDocument;if(!doc||!doc.body)return;
 var active=select.value==='split-studio',body=doc.body, panel=doc.getElementById('lps-split-panel');
 body.classList.toggle('lps-split-right',active&&val('side')==='right');
 if(!active)return;
 var variables={'width':val('width')+'%','text':val('brand_text_color'),'welcome-color':val('welcome_color'),'gradient':'linear-gradient(150deg,'+val('gradient_one')+','+val('gradient_two')+')','mobile-height':val('mobile_height')+'px','brand-align':val('panel_align'),'overlay':String(Number(val('overlay'))/100),'headline-font':'\"'+val('headline_font').split(':')[0].replace(/[\"']/g,'')+'\"','headline-size':val('headline_size')+'px','headline-weight':val('headline_weight'),'welcome-font':'\"'+val('welcome_font').split(':')[0].replace(/[\"']/g,'')+'\"','welcome-size':val('welcome_size')+'px'};
 Object.keys(variables).forEach(function(k){body.style.setProperty('--lps-split-'+k,variables[k]);});
 // Same Google Fonts dropdown as the main customizer. Load selected families in the iframe.
 var families=[val('headline_font'),val('welcome_font')].map(function(v){return v.split(':')[0].trim();}).filter(function(v){return /^[\w .-]+$/.test(v);});
 var fonts=doc.getElementById('lps-split-preview-fonts');
 if(!fonts){fonts=doc.createElement('style');fonts.id='lps-split-preview-fonts';doc.head.appendChild(fonts);}
 fonts.textContent=Array.from(new Set(families)).map(function(f){return '@import url("https://fonts.googleapis.com/css?family='+encodeURIComponent(f)+'&display=swap");';}).join('');
 // Keep this one shared with the Main Customizer; no duplicate blur slider.
 var blur=document.getElementById('lps_box_shadow_blur');
 if(blur && blur.dataset.lpsShadowEdited==='1'){
   var num=function(id,fallback,min,max){var el=document.getElementById(id);var v=el?Number(el.value):fallback;return Math.max(min,Math.min(max,Number.isFinite(v)?v:fallback));};
   var colorField=document.getElementById('lps_box_shadow_color');
   var hex=colorField&&/^#[0-9a-f]{6}$/i.test(colorField.value)?colorField.value:'#000000';
   var rgb=[1,3,5].map(function(i){return parseInt(hex.substring(i,i+2),16);});
   var opacity=num('lps_box_shadow_opacity',1,0,1);
   var sh=[num('lps_box_shadow_horizontal',0,-100,100),num('lps_box_shadow_vertical',0,-100,100),num('lps_box_shadow_blur',10,0,100),num('lps_box_shadow_spread',0,-100,100)].map(function(n){return n+'px';}).join(' ');
   body.style.setProperty('--lps-split-form-shadow',sh+' rgba('+rgb.join(',')+','+opacity+')');
 }

 if(panel){panel.querySelector('.lps-split-art').hidden=val('art')!=='1';setText(panel,'.lps-split-monogram',val('brand_icon'));setText(panel,'.lps-split-brand span:last-child',val('brand_label'));setText(panel,'.lps-split-eyebrow',val('eyebrow'));setText(panel,'.lps-split-story h2',val('headline'));setText(panel,'.lps-split-story p',val('description'));setText(panel,'.lps-split-footnote',val('footnote'));
 var u=val('background_image');if(u&&/^https?:\/\//i.test(u))panel.style.setProperty('background-image','linear-gradient(rgba(23,23,70,'+variables.overlay+'),rgba(23,23,70,'+variables.overlay+')),url("'+u.replace(/["\\]/g,'')+'")','important');else panel.style.removeProperty('background-image');}
 var login=doc.getElementById('login');if(login){var welcome=login.querySelector('.lps-split-welcome');if(!welcome){welcome=doc.createElement('div');welcome.className='lps-split-welcome';var form=login.querySelector('form');if(form)login.insertBefore(welcome,form);}welcome.replaceChildren();if(val('welcome')){var h=doc.createElement('h2');h.textContent=val('welcome');welcome.appendChild(h);}if(val('welcome_text')){var p=doc.createElement('p');p.textContent=val('welcome_text');welcome.appendChild(p);}welcome.hidden=!welcome.hasChildNodes();}
 var copyright=doc.querySelector('.copyright'),name=document.getElementById('lps_login_copyright');if(copyright&&login){if(name&&!name.value.trim()){copyright.hidden=true;}else{copyright.hidden=false;login.appendChild(copyright);}}
 var extra=doc.getElementById('lps-split-preview-responsive');if(!extra){extra=doc.createElement('style');extra.id='lps-split-preview-responsive';doc.head.appendChild(extra);}
 extra.textContent=(val('art')==='1'?'':'body.login.lps-split-studio .lps-split-art{display:none!important}')+(val('mobile_brand')==='1'?'@media(max-width:760px){body.login.lps-split-studio .lps-split-panel{display:flex!important;position:relative!important;width:100%!important;min-height:var(--lps-split-mobile-height)!important;padding:24px!important;}body.login.lps-split-studio{flex-direction:column!important;}}':'')+(val('mobile_first')==='brand'?'@media(max-width:760px){body.login.lps-split-studio .lps-split-panel{order:0}body.login.lps-split-studio #login{order:1}}':'');
 }catch(err){/* iframe can reload while controls are edited */}}
 document.querySelectorAll('[id^=lps_box_shadow_]').forEach(function(el){el.addEventListener('input',function(){var b=document.getElementById('lps_box_shadow_blur');if(b)b.dataset.lpsShadowEdited='1';sync();});});
 select.addEventListener('change',function(){sync();requestAnimationFrame(sync);});document.addEventListener('input',sync);document.addEventListener('change',sync);frame.addEventListener('load',function(){setTimeout(sync,120);});
 // Use a body-level overlay: fixed elements inside wp-admin wrappers can be
 // clipped by stacking contexts, regardless of their z-index.
 var full=document.getElementById('lps-preview-fullscreen');
 if(full){
   var preview=document.querySelector('.independent-preview'),originalParent=preview&&preview.parentNode;
   var marker=document.createComment('LPS fullscreen return point');
   function closeFullscreen(){if(!preview||!preview.classList.contains('lps-preview-expanded'))return;
      preview.classList.remove('lps-preview-expanded');document.body.classList.remove('lps-preview-open');
      if(marker.parentNode){marker.parentNode.insertBefore(preview,marker);marker.remove();}
      else if(originalParent)originalParent.appendChild(preview);
      full.textContent='Fullscreen';full.setAttribute('aria-pressed','false');}
   full.addEventListener('click',function(){if(!preview)return;
      if(preview.classList.contains('lps-preview-expanded')){closeFullscreen();return;}
      preview.parentNode.insertBefore(marker,preview);document.body.appendChild(preview);
      preview.classList.add('lps-preview-expanded');document.body.classList.add('lps-preview-open');
      full.textContent='Exit fullscreen';full.setAttribute('aria-pressed','true');});
   document.addEventListener('keydown',function(e){if(e.key==='Escape')closeFullscreen();});
 }
 // Initialize the existing Google Fonts picker (also used by the main Customizer).
 if(window.jQuery && typeof window.jQuery.fn.fontselect==='function'){
   window.jQuery('.lps-split-google-font').each(function(){
      var input=window.jQuery(this);input.fontselect();
      input.on('change',function(){sync();});
   });
 }
 // Use WordPress native color picker and Media Library (already enqueued by LPS).
 if(window.jQuery){var $=window.jQuery;
   $('.lps-split-color').each(function(){var el=$(this);if(typeof el.wpColorPicker==='function'){
      el.wpColorPicker({change:function(event,ui){el.val(ui.color.toString());sync();},clear:function(){el.val('');sync();}});
   }});
   $('.lps-split-upload').on('click',function(){if(!window.wp||!wp.media)return;
      var input=document.getElementById(this.getAttribute('data-target'));if(!input)return;
      var picker=wp.media({title:'Choose branding background',button:{text:'Use this image'},multiple:false,library:{type:'image'}});
      picker.on('select',function(){var a=picker.state().get('selection').first();if(a){input.value=a.get('url');input.dispatchEvent(new Event('input',{bubbles:true}));}});picker.open();});
   $('.lps-split-clear').on('click',function(){var input=document.getElementById(this.getAttribute('data-target'));if(input){input.value='';input.dispatchEvent(new Event('input',{bubbles:true}));}});
 }

 var resetSplit=document.getElementById('lps-split-reset');
 if(resetSplit)resetSplit.addEventListener('click',function(){
    if(!window.confirm('Reset Split-Screen Studio settings to their template defaults? Your main Customizer and Social Login settings will stay unchanged.'))return;
    resetSplit.disabled=true;
    var data=new URLSearchParams({action:'lps_reset_split_template',nonce:resetSplit.getAttribute('data-nonce')});
    fetch(resetSplit.getAttribute('data-url'),{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:data.toString()})
      .then(function(r){return r.json();}).then(function(result){if(!result.success)throw new Error(result.data&&result.data.message||'Reset failed');window.location.reload();})
      .catch(function(err){resetSplit.disabled=false;window.alert('Could not reset Split-Screen settings: '+err.message);});
 });
 setTimeout(sync,350);
 }if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();})();
