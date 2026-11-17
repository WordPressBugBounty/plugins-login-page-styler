/* Preview only: mirror the corrective background and alpha styling. */
(function($){
  $(function(){
    var form=document.querySelector('#content1 form[action*="options.php"]');
    var frame=document.getElementById('lps-iframe');
    if(!form||!frame)return;
    function value(name){var el=form.elements.namedItem(name);return el?String(el.value||''):'';}
    function rgba(hex,alpha){
      if(!/^#[0-9a-f]{6}$/i.test(hex))return '';
      var n=parseInt(hex.substring(1),16),a=Math.max(0,Math.min(1,parseFloat(alpha)||0));
      return 'rgba('+((n>>16)&255)+','+((n>>8)&255)+','+(n&255)+','+a+')';
    }
    function update(){
      try{
        var doc=frame.contentDocument;if(!doc||!doc.head)return;
        var css='',size=value('lps_login_bg_size'),pos=value('lps_login_bg_position'),rep=value('lps_login_bg_repeat');
        if(['cover','contain','auto'].includes(size))css+='body.login{background-size:'+size+'!important;}';
        if(['center center','center top','center bottom','left center','right center'].includes(pos))css+='body.login{background-position:'+pos+'!important;}';
        if(['repeat','no-repeat','repeat-x','repeat-y'].includes(rep))css+='body.login{background-repeat:'+rep+'!important;}';
        var panel='body.login #loginform,body.login #registerform,body.login #lostpasswordform';
        var active=!!(window.lpsUnifiedPreview&&window.lpsUnifiedPreview.activeTemplate);
        var fc=rgba(value('lps_login_form_color'),value('lps_login_form_color_opacity'));
        if(fc&&!active)css+=panel+'{background:'+fc+'!important;}';
        var image=value('lps_login_form_bg');
        if(!active&&/^https?:\/\//i.test(image))css+=panel+'{background-image:url("'+image.replace(/["\\]/g,'')+'")!important;background-size:cover!important;background-repeat:no-repeat!important;}';
        var ic=rgba(value('lps_login_form_input_color'),value('lps_login_form_input_color_opacity'));
        if(ic&&!active)css+='body.login #login .input{background:'+ic+'!important;}';
        var style=doc.getElementById('lps-corrective-live');if(!style){style=doc.createElement('style');style.id='lps-corrective-live';doc.head.appendChild(style);}style.textContent=css;
      }catch(e){}
    }
    $(form).on('input change keyup', 'input,select',update);
    $(form).on('change.colorpicker irischange',update);
    $(frame).on('load',function(){setTimeout(update,150);});
    update();
  });
})(jQuery);
