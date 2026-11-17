/* Background Animation admin preview (Free: visual demo only; Pro: real saved setting).
 * Move a dedicated inherited-background layer instead of body.login. The latter
 * has !important positions supplied by the existing live Customizer. */
(function(){'use strict';
 var css="/* A moving image layer avoids conflicts with Customizer !important background-position.\n * The image/gradient is inherited from body.login, so uploaded changes remain visible. */\nbody.login.lps-bg-motion{isolation:isolate;}\nbody.login.lps-bg-motion::before{content:\"\";position:fixed;inset:-5%;pointer-events:none;z-index:-1;background-image:inherit;background-color:inherit;background-size:inherit;background-position:inherit;background-repeat:inherit;will-change:transform,filter;}\nbody.login.lps-bg-motion #login{z-index:1;}\n@keyframes lpsBackgroundDrift{0%,100%{transform:translate3d(-1.5%,0,0) scale(1.06)}50%{transform:translate3d(1.5%,-1.5%,0) scale(1.09)}}\n@keyframes lpsBackgroundPulse{0%,100%{transform:scale(1.02);filter:brightness(1)}50%{transform:scale(1.11);filter:brightness(1.08)}}\nbody.login.lps-bg-drift::before{animation:lpsBackgroundDrift 14s ease-in-out infinite;}\nbody.login.lps-bg-pulse::before{animation:lpsBackgroundPulse 8s ease-in-out infinite;}\n@media(prefers-reduced-motion:reduce){body.login.lps-bg-motion::before{animation:none!important;transform:none!important;filter:none!important;}}\n";
 function start(){
   var select=document.getElementById('lps_background_animation');
   var iframe=document.getElementById('lps-iframe');
   if(!select||!iframe)return;
   function apply(){
     try{
       var doc=iframe.contentDocument;
       if(!doc||!doc.head||!doc.body)return;
       var style=doc.getElementById('lps-background-animation-live');
       if(!style){style=doc.createElement('style');style.id='lps-background-animation-live';doc.head.appendChild(style);}
       style.textContent=css;
       var body=doc.body;
       body.classList.remove('lps-bg-motion','lps-bg-drift','lps-bg-pulse');
       if(select.value==='drift'||select.value==='pulse'){
         body.classList.add('lps-bg-motion','lps-bg-'+select.value);
       }
     }catch(e){ /* Preview iframe may be reloading. */ }
   }
   select.addEventListener('change',apply);
   iframe.addEventListener('load',apply);
   apply();
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
})();
