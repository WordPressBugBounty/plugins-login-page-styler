/** Live preview for the main Customizer. Uses the PHP engine's selector map. */
(function ($) {
  'use strict';
  $(function () {
    var form = document.querySelector('#content1 form[action*="options.php"]') || document.querySelector('form input[name="lps_login_bg_size"]')?.form;
    var frame = document.getElementById('lps-iframe');
    if (!form || !frame || !window.lpsUnifiedPreview) return;
    // Template mode is edited only through its independent template controls.
    if (window.lpsUnifiedPreview.activeTemplate) return;

    var rules = window.lpsUnifiedPreview.rules;
    var lastCSS = null;
    var lastPosition = null;
    var lastDocument = null;
    var pending = false;

    function fieldValue(name) {
      var field = form.elements.namedItem(name);
      if (!field) return '';
      if (field.length && !field.tagName) field = field[0];
      return String(field.value || '').trim();
    }


    function auroraCompatCSS() {
      if (document.getElementById('lps_design_preset')?.value !== 'aurora-glass') return '';
      var form = 'body.login.lps-aurora-glass #login form';
      var input = 'body.login.lps-aurora-glass #login form input.input,body.login.lps-aurora-glass #login form input[type=text],body.login.lps-aurora-glass #login form input[type=email],body.login.lps-aurora-glass #login form input[type=password]';
      var label = 'body.login.lps-aurora-glass #login form label';
      var css = '';
      function add(sel, prop, value) {if(value !== '') css += sel+'{'+prop+':'+value+'!important;}\n';}
      function color(key) {var v=fieldValue(key);return /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(v)?v:'';}
      function px(key) {var v=fieldValue(key);return /^(?:\d+(?:\.\d*)?|\.\d+)$/.test(v)?Math.min(1200,Math.max(0,Number(v)))+'px':'';}
      function alpha(key) {var v=fieldValue(key);return v===''?1:Math.max(0,Math.min(1,Number(v)||0));}
      function rgba(hex,a) {if(hex.length===4)hex='#'+hex.slice(1).split('').map(function(c){return c+c;}).join('');var n=parseInt(hex.slice(1),16);return 'rgba('+((n>>16)&255)+','+((n>>8)&255)+','+(n&255)+','+a+')';}
      var fc=color('lps_login_form_color'), image=fieldValue('lps_login_form_bg');
      if(/^https?:\/\//i.test(image))add(form,'background','url("'+image.replace(/["\\]/g,'')+'") center / cover no-repeat');
      else if(fc)add(form,'background',rgba(fc,alpha('lps_login_form_color_opacity')));
      add(form,'border-color',color('lps_login_form_border_color'));
      add(form,'border-radius',px('lps_login_form_border_radius'));
      var style=fieldValue('lps_login_form_border_style'), styles=['none','solid','dashed','dotted','double'];
      if(styles.includes(style))add(form,'border-style',style);
      var borderSize=px('lps_login_form_border_size');
      if(borderSize!==''&&(parseFloat(borderSize)>0||styles.includes(style)))add(form,'border-width',borderSize);
      add(label,'color',color('lps_login_label_color'));
      add(label,'font-size',px('lps_login_label_size'));
      add('body.login.lps-aurora-glass #login form .forgetmenot label','font-size',px('lps_login_remember_label_size'));
      var ic=color('lps_login_form_input_color');
      if(ic)add(input,'background',rgba(ic,alpha('lps_login_form_input_color_opacity')));
      add(input,'color',color('lps_login_form_input_text_color'));
      add(input,'-webkit-text-fill-color',color('lps_login_form_input_text_color'));
      add(input,'border-color',color('lps_login_form_input_feild_border_color'));
      add(input,'border-radius',px('lps_login_form_input_feild_border_radius'));
      var inputStyle=fieldValue('lps_login_form_input_border_style');
      if(styles.includes(inputStyle))add(input,'border-style',inputStyle);
      var shadowKeys=['lps_box_shadow_horizontal','lps_box_shadow_vertical','lps_box_shadow_blur','lps_box_shadow_spread','lps_box_shadow_color','lps_box_shadow_opacity'];
      if(shadowKeys.some(function(k){return fieldValue(k)!=='';})){
        function shadowNumber(key){var n=Number(fieldValue(key));return Number.isFinite(n)?Math.max(-500,Math.min(500,n)):0;}
        var shadowColor=color('lps_box_shadow_color')||'#000000';
        add(form,'box-shadow',shadowNumber('lps_box_shadow_horizontal')+'px '+shadowNumber('lps_box_shadow_vertical')+'px '+Math.max(0,shadowNumber('lps_box_shadow_blur'))+'px '+Math.max(0,shadowNumber('lps_box_shadow_spread'))+'px '+rgba(shadowColor,alpha('lps_box_shadow_opacity')));
      }
      var inputSize=px('lps_login_form_input_border_size');
      if(inputSize!==''&&(parseFloat(inputSize)>0||styles.includes(inputStyle)))add(input,'border-width',inputSize);
      return css;
    }

    function buildCSS() {
      var result = '';
      Object.keys(rules).forEach(function (name) {
        var rule = rules[name];
        var value = fieldValue(name);
        var type = rule[2];
        if (!value) return;
        if (name === 'lps_login_form_width' && document.getElementById('lps_design_preset')?.value === 'aurora-glass') return;
        if (type === 'color' && !/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(value)) return;
        if (type === 'px') {
          // Match lps_unified_value() on the saved WordPress page:
          // negative/non-numeric values are ignored; valid values cap at 1200px.
          if (!/^(?:\d+(?:\.\d*)?|\.\d+)$/.test(value)) return;
          value = Math.min(1200, Math.max(0, Number(value))) + 'px';
        }
        if (type === 'image') {
          if (!/^https?:\/\//i.test(value)) return;
          value = 'url("' + value.replace(/["\\]/g, '') + '")';
        }
        // Modern Minimal manages its background (including Clear) in one place.
        if (name === 'lps_login_background_color' && ['modern-minimal','nature-pro','noir-editorial','split-studio'].includes(document.getElementById('lps_design_preset')?.value)) return;
        var extra = name === 'lps_login_background_color' && !fieldValue('lps_body_bg_img') ? 'background-image:none!important;' : '';
        result += rule[0] + '{' + rule[1] + ':' + value + '!important;' + extra + '}\n';
      });
      var panel='body.login #loginform,body.login #registerform,body.login #lostpasswordform';
      function alpha(name){var n=parseFloat(fieldValue(name));return isFinite(n)?Math.max(0,Math.min(1,n)):1;}
      function rgba(hex,a){if(!/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(hex))return '';if(hex.length===4)hex='#'+hex.slice(1).split('').map(function(c){return c+c;}).join('');var n=parseInt(hex.slice(1),16);return 'rgba('+((n>>16)&255)+','+((n>>8)&255)+','+(n&255)+','+a+')';}
      var fc=rgba(fieldValue('lps_login_form_color'),alpha('lps_login_form_color_opacity'));
      if(fc)result+=panel+'{background-color:'+fc+'!important;}';
      var ic=rgba(fieldValue('lps_login_form_input_color'),alpha('lps_login_form_input_color_opacity'));
      if(ic)result+='body.login #login .input{background-color:'+ic+'!important;}';
      var img=fieldValue('lps_login_form_bg');
      if(/^https?:\/\//i.test(img))result+=panel+'{background-image:url("'+img.replace(/["\\]/g,'')+'")!important;background-size:cover!important;background-position:center!important;background-repeat:no-repeat!important;}';
      var size=fieldValue('lps_login_bg_size'),pos=fieldValue('lps_login_bg_position'),rep=fieldValue('lps_login_bg_repeat');
      if(['cover','contain','auto'].indexOf(size)>=0)result+='body.login{background-size:'+size+'!important;}';
      if(['center center','center top','center bottom','left center','right center'].indexOf(pos)>=0)result+='body.login{background-position:'+pos+'!important;}';
      if(['no-repeat','repeat','repeat-x','repeat-y'].indexOf(rep)>=0)result+='body.login{background-repeat:'+rep+'!important;}';
      result+='html{background-image:none!important;}body.login{min-height:100vh!important;}';
      result += auroraCompatCSS();
      return result;
    }

    function update() {
      pending = false;
      try {
        var doc = frame.contentDocument;
        if (!doc || !doc.head) return;
        var css = buildCSS();
        // Position is controlled by the original #login padding rules only.
        // Update the same dedicated style element as the frontend controller.
        var placement = fieldValue('lps_login_form_position');
        lastPosition = placement;
        var positionRules = window.lpsUnifiedPreview.positionCSS || {};
        var positionStyle = doc.getElementById('lps-layout-position');
        if (!positionStyle) {
          positionStyle = doc.createElement('style');
          positionStyle.id = 'lps-layout-position';
          doc.head.appendChild(positionStyle);
        }
        var positionCSS = positionRules[placement] || '';
        var preset = document.getElementById('lps_design_preset');
        // A wide two-column Aurora design must be centered at the template-default
        // position. The original nine-position CSS is for the narrow WP login box;
        // never let it leak into Aurora's iframe (the frontend controller already
        // applies this centered layout to Aurora for every position selection).
        if (preset && preset.value === 'aurora-glass') {
          positionCSS = 'body.login.lps-aurora-glass{min-height:100vh!important;display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;padding:26px 16px!important;box-sizing:border-box!important;}' +
            'body.login.lps-aurora-glass #login{width:min(1020px,100%)!important;max-width:100%!important;min-width:0!important;position:relative!important;top:auto!important;right:auto!important;bottom:auto!important;left:auto!important;float:none!important;margin:auto!important;padding:0!important;box-sizing:border-box!important;}';
        }
        if (preset && ['modern-minimal','nature-pro','noir-editorial','split-studio'].includes(preset.value)) {
          var number = parseInt(placement, 10) || 1;
          var vertical = [4,5,6].includes(number) ? 'flex-start' : ([7,8,9].includes(number) ? 'flex-end' : 'center');
          var horizontal = [2,5,8].includes(number) ? 'flex-start' : ([3,6,9].includes(number) ? 'flex-end' : 'center');
          positionCSS = 'body.login.lps-modern-minimal{min-height:100vh!important;display:flex!important;flex-direction:column!important;align-items:'+horizontal+'!important;justify-content:'+vertical+'!important;padding:24px!important;box-sizing:border-box!important;}' +
            'body.login.lps-modern-minimal #login{width:min(420px,100%)!important;max-width:100%!important;min-width:0!important;position:relative!important;top:auto!important;right:auto!important;bottom:auto!important;left:auto!important;float:none!important;margin:0!important;padding:0!important;box-sizing:border-box!important;}' +
            'body.login.lps-modern-minimal #login form{box-sizing:border-box!important;max-width:100%!important;}';
          if (preset.value === 'nature-pro') positionCSS = positionCSS.replace(/lps-modern-minimal/g, 'lps-nature-pro');
          if (preset.value === 'noir-editorial') positionCSS = positionCSS.replace(/lps-modern-minimal/g, 'lps-noir-editorial');
          if (preset.value === 'split-studio') positionCSS = 'body.login.lps-split-studio{display:flex!important;align-items:center!important;justify-content:flex-end!important;padding:32px max(4%,24px) 32px calc(51% + 3%)!important;min-height:100vh!important;box-sizing:border-box!important;}body.login.lps-split-studio #login{width:min(440px,100%)!important;max-width:100%!important;min-width:0!important;position:relative!important;top:auto!important;right:auto!important;bottom:auto!important;left:auto!important;float:none!important;margin:0!important;padding:0!important;box-sizing:border-box!important;}@media(max-width:760px){body.login.lps-split-studio{padding:32px 18px!important;justify-content:center!important;}}';
        }
        // Reset any previous position completely when switching selections.
        if (positionStyle.textContent !== positionCSS) positionStyle.textContent = positionCSS;
        var style = doc.getElementById('lps-unified-live');
        if (!style) {
          style = doc.createElement('style');
          style.id = 'lps-unified-live';
          doc.head.appendChild(style);
        }
        // Keep this stylesheet last so older preview scripts cannot override it.
        if (doc.head.lastElementChild !== style) doc.head.appendChild(style);
        if (doc.head.lastElementChild !== positionStyle) doc.head.appendChild(positionStyle);
        if (lastDocument !== doc || lastCSS !== css || style.textContent !== css) {
          style.textContent = css;
          lastCSS = css;
          lastDocument = doc;
        }
      } catch (error) {
        // Some security plugins prevent accessing the iframe document.
      }
    }

    function scheduleUpdate() {
      if (pending) return;
      pending = true;
      window.requestAnimationFrame(update);
    }

    // Native inputs, WP color picker, range sliders and media library controls.
    $(form).on('input change keyup click', 'input,select,textarea,button', scheduleUpdate);
    $(form).on('change.colorpicker irischange', scheduleUpdate);
    $(frame).on('load', function () {
      lastDocument = null;
      scheduleUpdate();
      window.setTimeout(scheduleUpdate, 100);
      window.setTimeout(scheduleUpdate, 500);
    });

    // Legacy controls sometimes update values programmatically without firing
    // an input event. Check for actual value changes, without reloading the iframe.
    window.setInterval(function () {
      if (document.hidden) return;
      var doc;
      try { doc = frame.contentDocument; } catch (error) { return; }
      if (!doc || !doc.head || (buildCSS() !== lastCSS || fieldValue('lps_login_form_position') !== lastPosition) || !doc.getElementById('lps-unified-live')) {
        scheduleUpdate();
      }
    }, 350);
    scheduleUpdate();
  });
})(jQuery);
