/* Aurora Studio options: share the existing iframe/DOM helper and WordPress controls. */
(function(){'use strict';function boot(){
var sel=document.getElementById('lps_design_preset'),frame=document.getElementById('lps-iframe'),section=document.getElementById('lps-aurora-settings');
if(!sel||!frame||!section)return;
var defaults={};section.querySelectorAll('[id^=lps_aurora_]').forEach(function(el){if(el.id)defaults[el.id]=el.type==='checkbox'?el.checked:el.value;});
function options(){var data={};Object.keys(defaults).forEach(function(id){var e=document.getElementById(id);if(e)data[id.replace('lps_aurora_','')]=e.type==='checkbox'?(e.checked?'1':'0'):e.value;});return data;}
function sync(){var active=sel.value==='aurora-glass';section.hidden=!active;if(!active)return;var d=frame.contentDocument;if(!d||!d.body||!d.body.classList.contains('lps-aurora-glass'))return;
var o=options(),b=d.body;
var vars={'width':o.brand_width+'%','tint':String(Number(o.glass_tint)/100),'card-radius':o.glass_radius+'px','accent':o.accent,'text':o.text_color,'headline-size':o.headline_size+'px','welcome-size':o.welcome_size+'px','headline-font':'"'+o.headline_font.split(':')[0].replace(/["']/g,'')+'"','welcome-font':'"'+o.welcome_font.split(':')[0].replace(/["']/g,'')+'"'};
Object.keys(vars).forEach(function(k){b.style.setProperty('--lps-aurora-'+k,vars[k]);});
if(window.lpsAuroraLayout)window.lpsAuroraLayout(d,true);
if(window.lpsAuroraConfigure)window.lpsAuroraConfigure(d,o);
var custom=d.getElementById('lps-aurora-live-options');if(!custom){custom=d.createElement('style');custom.id='lps-aurora-live-options';d.head.appendChild(custom);}
var css='';if(o.brand_side==='right')css+='body.login.lps-aurora-glass #login form{direction:rtl}body.login.lps-aurora-glass #login form>.lps-aurora-brand,body.login.lps-aurora-glass #login form>.lps-aurora-fields{direction:ltr}body.login.lps-aurora-glass .lps-aurora-brand{border-right:0;border-left:1px solid rgba(255,255,255,.57);padding:2px 0 2px 30px}';
if(o.divider!=='1')css+='body.login.lps-aurora-glass .lps-aurora-brand{border-color:transparent!important}';
if(o.mobile_brand!=='1')css+='@media(max-width:820px){body.login.lps-aurora-glass .lps-aurora-brand-story,body.login.lps-aurora-glass .lps-aurora-brand-footer{display:none!important}}';
if(o.mobile_first==='form')css+='@media(max-width:820px){body.login.lps-aurora-glass .lps-aurora-fields{order:0}body.login.lps-aurora-glass .lps-aurora-brand{order:1}}';
if(/^https?:\/\//.test(o.decorative_image))css+='body.login.lps-aurora-glass{background-image:url("'+o.decorative_image.replace(/["\\]/g,'')+'")!important}';
custom.textContent=css;
var font=d.getElementById('lps-aurora-fonts');if(!font){font=d.createElement('style');font.id='lps-aurora-fonts';d.head.appendChild(font);}
font.textContent=[o.headline_font,o.welcome_font].map(function(x){return x.split(':')[0].trim();}).filter(function(x){return /^[\w .-]+$/.test(x)}).map(function(x){return '@import url("https://fonts.googleapis.com/css?family='+encodeURIComponent(x)+'&display=swap");';}).join('');
}
sel.addEventListener('change',function(){sync();requestAnimationFrame(sync);});frame.addEventListener('load',function(){setTimeout(sync,160)});
section.addEventListener('input',sync);section.addEventListener('change',sync);
if(window.jQuery){var $=window.jQuery;if(typeof $.fn.fontselect==='function')$('.lps-aurora-google-font').each(function(){$(this).fontselect();$(this).on('change',sync);});
$('.lps-aurora-color').each(function(){var e=$(this);if(typeof e.wpColorPicker==='function')e.wpColorPicker({change:function(evt,ui){e.val(ui.color.toString());sync();},clear:function(){e.val('');sync();}});});
$('.lps-aurora-upload').on('click',function(){if(!window.wp||!wp.media)return;var input=document.getElementById('lps_aurora_decorative_image');var picker=wp.media({title:'Choose Aurora background',button:{text:'Use this image'},multiple:false,library:{type:'image'}});picker.on('select',function(){var a=picker.state().get('selection').first();if(a){input.value=a.get('url');sync();}});picker.open();});
$('.lps-aurora-clear').on('click',function(){var e=document.getElementById('lps_aurora_decorative_image');e.value='';sync();});}
var reset=document.getElementById('lps-aurora-reset');if(reset)reset.addEventListener('click',function(){if(!confirm('Restore only Aurora Glass settings? Other Customizer settings will remain unchanged.'))return;reset.disabled=true;fetch(reset.dataset.url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'lps_reset_aurora_template',nonce:reset.dataset.nonce})}).then(function(r){return r.json()}).then(function(result){if(!result.success)throw new Error('Reset failed');location.reload()}).catch(function(err){reset.disabled=false;alert(err.message)});});
setTimeout(sync,300);
}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();})();