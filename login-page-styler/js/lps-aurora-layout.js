/* Arrange existing Aurora login DOM without replacing WordPress form controls.
 * Idempotent: runs on both frontend and existing live preview iframe. */
(function (global) {
  'use strict';
  function setAuroraLayout(doc, enabled) {
    if (!doc || !doc.body) return;
    var root = doc.getElementById('login');
    if (!root) return;
    var form = root.querySelector('#loginform, #registerform, #lostpasswordform');
    if (!form) return;
    var brand = form.querySelector('.lps-aurora-brand');
    var fields = form.querySelector('.lps-aurora-fields');
    var logo = brand ? brand.querySelector('h1') : root.querySelector(':scope > h1');
    if (!logo) logo = root.querySelector('h1');
    if (!enabled) {
      if (fields) {
        while (fields.firstChild) form.insertBefore(fields.firstChild, fields);
        fields.remove();
      }
      if (brand) {
        var heading = brand.querySelector('h1');
        if (heading) root.insertBefore(heading, form);
        brand.remove();
      }
      return;
    }
    if (!brand) {
      brand = doc.createElement('div'); brand.className = 'lps-aurora-brand';
      brand.innerHTML = '<div class="lps-aurora-brand-story"><span class="lps-aurora-kicker">STUDIO / ACCESS</span><h2>Beautifully<br>connected.</h2><p>A welcoming space designed for your next sign-in.</p></div><div class="lps-aurora-brand-footer"></div>';
      form.insertBefore(brand,form.firstChild);
    }
    if (logo && logo.parentNode !== brand) brand.insertBefore(logo,brand.firstChild);
    if (!fields) { fields = doc.createElement('div'); fields.className='lps-aurora-fields'; form.appendChild(fields); }
    Array.prototype.slice.call(form.childNodes).forEach(function (node) {
      if (node !== brand && node !== fields) fields.appendChild(node);
    });
    var welcome=fields.querySelector('.lps-aurora-welcome');
    if (!welcome) {
      welcome = doc.createElement('div'); welcome.className='lps-aurora-welcome';
      welcome.innerHTML='<h2>Welcome back</h2><p>Sign in to your account</p>';
      fields.insertBefore(welcome,fields.firstChild);
    }
    var footer=brand.querySelector('.lps-aurora-brand-footer');
    if (footer) footer.textContent = (doc.querySelector('body.login') && doc.title) ? doc.title.split('‹').pop().trim() : '';
  }
  function configure(doc, options) {
    if (!doc || !options) return;
    var form=doc.querySelector('#loginform, #registerform, #lostpasswordform');
    if(!form)return;
    var brand=form.querySelector('.lps-aurora-brand'),fields=form.querySelector('.lps-aurora-fields');
    function put(parent,selector,value){if(!parent)return;var el=parent.querySelector(selector);if(el){el.textContent=value||'';el.hidden=!value;}}
    put(brand,'.lps-aurora-kicker',options.brand_label);
    put(brand,'.lps-aurora-brand-story h2',options.headline);
    put(brand,'.lps-aurora-brand-story p',options.description);
    put(brand,'.lps-aurora-brand-footer',options.footer);
    put(fields,'.lps-aurora-welcome h2',options.welcome);
    put(fields,'.lps-aurora-welcome p',options.welcome_description);
  }
  global.lpsAuroraConfigure=configure;
  global.lpsAuroraLayout = setAuroraLayout;
})(window);
