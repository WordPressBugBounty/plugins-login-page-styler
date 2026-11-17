(function(){const demo=document.querySelector('#lps-social-demo');if(!demo)return;const form=demo.closest('form');function value(k){const e=form.querySelector('[name="lps_social_options['+k+']"]'+(k==='preset'?':checked':''));return e?e.value:'';}function update(){const wrap=demo.querySelector('.lps-social-wrap');if(!wrap)return;const preset=value('preset');wrap.className='lps-social-wrap lps-social-preset-'+(preset==='auto'?demo.dataset.auto:preset)+' lps-social-layout-'+value('layout')+' lps-social-size-'+value('size');wrap.classList.add('lps-social-align-'+value('button_align'),'lps-social-divider-'+value('divider_style'),'lps-social-type-'+value('button_type'),'lps-social-shape-'+value('shape'),'lps-social-colors-'+value('color_mode'),'lps-social-shadow-'+value('shadow'),'lps-social-border-'+value('border_mode'));
wrap.style.setProperty('--lps-social-radius',value('radius')+'px');
wrap.style.setProperty('--lps-social-pad-x',value('padding_x')+'px');
wrap.style.setProperty('--lps-social-pad-y',value('padding_y')+'px');
wrap.style.setProperty('--lps-social-icon-size',value('icon_size')+'px');
wrap.style.setProperty('--lps-social-width',value('button_width')+'%');
demo.style.order=value('position')==='above'?'0':'2';wrap.style.setProperty('--lps-social-gap',Math.max(0,Math.min(30,Number(value('spacing'))||0))+'px');let divider=wrap.querySelector('.lps-social-divider');if(!divider){divider=document.createElement('div');divider.className='lps-social-divider';wrap.prepend(divider);}divider.textContent=value('divider');divider.hidden=!value('divider')||value('divider_style')==='hidden';}function previewProviders(){const buttons=demo.querySelector('.lps-social-buttons');if(!buttons)return;const controls=[...form.querySelectorAll('[name^="lps_social_options[providers]["][name$="[enabled]"]')];let shown=0;controls.forEach(input=>{const key=input.name.split('[')[2].slice(0,-1);const element=buttons.querySelector('.lps-social-'+key)?.closest('.lps-social-button');if(!element)return;const enabled=input.checked && form.querySelector('[name="lps_social_options[enabled]"]').checked;element.hidden=!enabled;if(enabled)shown++;const order=form.querySelector('[name="lps_social_options[providers]['+key+'][order]"]');element.style.order=order?Number(order.value)||99:99;});demo.querySelector('.lps-social-wrap').hidden=!shown;}form.addEventListener('input',previewProviders);form.addEventListener('change',previewProviders);previewProviders();form.addEventListener('input',update);form.addEventListener('change',update);update();
/** v9.9.35.11: A preset is a complete starting design, not an extra layer on top
 * of previous display overrides. Only a deliberate template change resets controls.
 * After selecting a preset, each setting can be customized again normally.
 */
const displayDefaults={
 layout:'vertical',position:'below',size:'medium',spacing:10,radius:12,
 padding_x:16,padding_y:12,icon_size:19,button_width:100,
 button_align:'center',button_type:'preset',shape:'preset',
 color_mode:'preset',shadow:'preset',border_mode:'preset',
 divider_style:'plain',divider:'Or continue with'
};
const iconPresets=new Set(['icon-brand','icon-black','icon-outline','icon-square','icon-soft','icon-glass']);
function resetDisplayForTemplate(preset){
 const defaults={...displayDefaults};
 if(iconPresets.has(preset)){
   defaults.layout='horizontal';
   defaults.spacing=10;
   defaults.divider_style='plain';
 }
 if(preset==='compact-brand'){
   defaults.layout='vertical';defaults.size='small';defaults.spacing=8;
 }
 if(preset==='pill-outline'){
   defaults.radius=40;
 }
 Object.entries(defaults).forEach(([key,val])=>{
   const control=form.querySelector('[name="lps_social_options['+key+']"]');
   if(!control)return;
   control.value=String(val);
 });
 // Rebuild the demo immediately; there is no need to save or refresh to see it.
 update();
}
form.querySelectorAll('input[type="radio"][name="lps_social_options[preset]"]').forEach(radio=>{
 radio.addEventListener('change',()=>{
   if(radio.checked)resetDisplayForTemplate(radio.value);
 });
});

const tabs=form.querySelectorAll('[data-panel]');const sections=form.querySelectorAll('[data-section]');function selectPanel(name){tabs.forEach(b=>{b.classList.toggle('is-active',b.dataset.panel===name);b.setAttribute('aria-pressed',String(b.dataset.panel===name));});sections.forEach(s=>s.hidden=s.dataset.section!==name);}tabs.forEach(b=>b.addEventListener('click',()=>selectPanel(b.dataset.panel)));selectPanel('providers');
const theme=document.querySelector('#lps-social-preview-theme');theme.addEventListener('change',()=>document.querySelector('.lps-social-stage').dataset.theme=theme.value);
form.querySelectorAll('.lps-copy-url').forEach(b=>b.addEventListener('click',async()=>{const field=b.previousElementSibling;field.select();try{await navigator.clipboard.writeText(field.value);b.textContent='Copied';}catch(e){b.textContent='Select and copy URL';}}));
})();

// Free edition: never submit the Social Login demo form, even with the Enter key.
document.addEventListener('DOMContentLoaded', function () {
  const demo = document.getElementById('lps-social-demo-form');
  if (demo) demo.addEventListener('submit', function (event) { event.preventDefault(); });
});
