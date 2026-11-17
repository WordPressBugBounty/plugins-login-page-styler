<?php
/** Split-Screen Studio: decorative presentation only, no authentication changes. */
if (!defined('ABSPATH')) exit;
function lps_split_selected() {
    return lps_design_should_render() && get_option('lps_design_preset','default') === 'split-studio';
}
add_filter('login_body_class', function($classes) {
    if (!lps_split_selected()) return $classes;
    if (is_array($classes)) { $classes[] = 'lps-split-studio'; if(lps_split_option('side')==='right') $classes[]='lps-split-right'; return $classes; }
    return trim($classes . ' lps-split-studio');
});
add_action('login_enqueue_scripts', function () {
    if (!lps_split_selected()) return;
    $file = dirname(__DIR__) . '/css/lps-split-studio.css';
    wp_enqueue_style('lps-split-studio', plugins_url('../css/lps-split-studio.css',__FILE__), array('login'), substr(hash_file('sha256',$file),0,12));
}, 15);
add_action('admin_enqueue_scripts', function () {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen routing for stylesheet/script enqueue.
    if (!isset($_GET['page']) || !is_string($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
    $file = dirname(__DIR__) . '/css/lps-split-studio.css';
    wp_enqueue_style('lps-split-studio-preview', plugins_url('../css/lps-split-studio.css',__FILE__), array(), substr(hash_file('sha256',$file),0,12));
},101);
/** Split-Screen settings are separate from global styling and never touch OAuth settings. */
function lps_split_fields() {
 return array(
 'side'=>array('Panel side','select','left',array('left'=>'Left','right'=>'Right')),
 'width'=>array('Branding width (%)','number','51',array(30,70)),
 'panel_align'=>array('Branding content alignment','select','left',array('left'=>'Left','center'=>'Center','right'=>'Right')),
 'gradient_one'=>array('Gradient start','color','#3c3ab4'),
 'gradient_two'=>array('Gradient end','color','#ae78d5'),
 'background_image'=>array('Branding background image URL','url',''),
 'overlay'=>array('Image overlay opacity (%)','number','20',array(0,90)),
 'art'=>array('Show decorative artwork','checkbox','1'),
 'brand_icon'=>array('Branding mark (text or symbol)','text','✦'),
 'brand_label'=>array('Branding label','text','STUDIO / ACCESS'),
 'eyebrow'=>array('Small heading','text','A BETTER WAY TO BEGIN'),
 'headline'=>array('Headline','text','Your next chapter starts here.'),
 'description'=>array('Description','textarea','A thoughtfully designed space to make every sign-in feel exceptional.'),
 'footnote'=>array('Branding footer','text','DESIGNED FOR WHAT’S NEXT'),
 'brand_text_color'=>array('Branding text color','color','#ffffff'),
 'welcome'=>array('Welcome heading','text','Welcome back'),
 'welcome_text'=>array('Welcome description','text','Sign in to your account'),
 'welcome_color'=>array('Welcome text color','color','#17213a'),
 'headline_font'=>array('Brand headline Google Font','google_font','Playfair Display'),
 'headline_size'=>array('Brand headline size (px)','number','64',array(28,90)),
 'headline_weight'=>array('Brand headline weight','select','500',array('400'=>'Regular','500'=>'Medium','600'=>'Semibold','700'=>'Bold')),
 'welcome_font'=>array('Welcome heading Google Font','google_font','Open Sans'),
 'welcome_size'=>array('Welcome heading size (px)','number','27',array(18,48)),
 'mobile_brand'=>array('Show branding panel on mobile','checkbox','0'),
 'mobile_first'=>array('Mobile panel order','select','form',array('form'=>'Form first','brand'=>'Branding first')),
 'mobile_height'=>array('Mobile branding height (px)','number','180',array(100,400))
 );
}
function lps_split_option($key) {
 $fields=lps_split_fields(); if (!isset($fields[$key])) return '';
 $value=get_option('lps_split_'.$key,false);
 return $value === false ? $fields[$key][2] : $value;
}
function lps_split_sanitize($value,$key) {
 $fields=lps_split_fields(); if (!isset($fields[$key])) return '';
 $field=$fields[$key]; $type=$field[1];
 if ($type==='checkbox') return $value==='1' ? '1':'0';
 if ($type==='google_font') {
  $font=trim((string)$value);
  return preg_match('/^[\p{L}\p{N} ._:-]{1,90}$/u',$font) ? $font : $field[2];
 }
 if ($type==='select') return isset($field[3][$value]) ? $value : $field[2];
 if ($type==='number') return (string)max($field[3][0],min($field[3][1],(int)$value));
 if ($type==='color') return sanitize_hex_color($value) ?: $field[2];
 if ($type==='url') return esc_url_raw($value);
 if ($type==='textarea') return sanitize_textarea_field($value);
 return sanitize_text_field($value);
}
add_action('admin_init',function() {
 foreach(lps_split_fields() as $key=>$field) {
  register_setting('lps-settings-group','lps_split_'.$key,array('sanitize_callback'=>function($v)use($key){return lps_split_sanitize($v,$key);}));
 }
});
/** Reset only Split-Screen Studio's saved fields. Never clear global design settings. */
add_action('wp_ajax_lps_reset_split_template', function () {
    if (!current_user_can('manage_options')) wp_send_json_error(array('message'=>'Permission denied'),403);
    check_ajax_referer('lps_reset_split','nonce');
    foreach (array_keys(lps_split_fields()) as $key) delete_option('lps_split_'.$key);
    wp_send_json_success(array('message'=>'Split-Screen defaults restored'));
});
function lps_split_css() {
 $side=lps_split_option('side'); $width=(int)lps_split_option('width');
 $p1=lps_split_option('gradient_one'); $p2=lps_split_option('gradient_two');
 $color=lps_split_option('brand_text_color'); $welcome=lps_split_option('welcome_color');
 $headline_font=preg_replace('/:[^:]*$/','',lps_split_option('headline_font'));
 $welcome_font=preg_replace('/:[^:]*$/','',lps_split_option('welcome_font'));
 // Only load selected Google Font families; no imported font is required for system fallbacks.
 $imports='';
 foreach(array_unique(array($headline_font,$welcome_font)) as $family){
  if($family!=='' && !in_array($family,array('Georgia','Arial','Verdana','Trebuchet MS','Times New Roman','Palatino Linotype'),true)){
   $imports.='@import url("https://fonts.googleapis.com/css?family='.rawurlencode($family).'&display=swap");';
  }
 }
 $css=$imports.'body.login.lps-split-studio{--lps-split-width:'.$width.'%;--lps-split-text:'.$color.';--lps-split-welcome-color:'.$welcome.';--lps-split-gradient:linear-gradient(150deg,'.$p1.','.$p2.');--lps-split-mobile-height:'.(int)lps_split_option('mobile_height').'px;--lps-split-brand-align:'.lps_split_option('panel_align').';--lps-split-overlay:'.((int)lps_split_option('overlay')/100).';--lps-split-headline-font:\''.esc_attr($headline_font).'\';--lps-split-headline-size:'.(int)lps_split_option('headline_size').'px;--lps-split-headline-weight:'.(int)lps_split_option('headline_weight').';--lps-split-welcome-font:\''.esc_attr($welcome_font).'\';--lps-split-welcome-size:'.(int)lps_split_option('welcome_size').'px;}';
 // Split-Screen must honor the shared Form Box Shadow controls.
 // Only set a shadow override if the user has actually configured one.
 $shadow_keys=array('lps_box_shadow_horizontal','lps_box_shadow_vertical','lps_box_shadow_blur','lps_box_shadow_spread','lps_box_shadow_color','lps_box_shadow_opacity');
 $shadow_custom=false;
 foreach($shadow_keys as $shadow_key){if(get_option($shadow_key,false)!==false){$shadow_custom=true;break;}}
 if($shadow_custom){
   $x=max(-100,min(100,(int)get_option('lps_box_shadow_horizontal',0)));
   $y=max(-100,min(100,(int)get_option('lps_box_shadow_vertical',0)));
   $blur=max(0,min(100,(int)get_option('lps_box_shadow_blur',10)));
   $spread=max(-100,min(100,(int)get_option('lps_box_shadow_spread',0)));
   $hex=sanitize_hex_color(trim((string)get_option('lps_box_shadow_color','#000000'))) ?: '#000000';
   $opacity=max(0,min(1,(float)get_option('lps_box_shadow_opacity',1)));
   $color=sprintf('rgba(%d,%d,%d,%s)',hexdec(substr($hex,1,2)),hexdec(substr($hex,3,2)),hexdec(substr($hex,5,2)),$opacity);
   $css.='body.login.lps-split-studio{--lps-split-form-shadow:'.$x.'px '.$y.'px '.$blur.'px '.$spread.'px '.$color.';}';
 }
 if ($side==='right') $css.='body.login.lps-split-studio{--lps-split-side:right;}';
 if (lps_split_option('art')!=='1') $css.='body.login.lps-split-studio .lps-split-art{display:none!important;}';
 if (lps_split_option('mobile_brand')==='1') $css.='@media(max-width:760px){body.login.lps-split-studio .lps-split-panel{display:flex!important;position:relative!important;width:100%!important;min-height:var(--lps-split-mobile-height)!important;padding:24px!important;}body.login.lps-split-studio{display:flex!important;flex-direction:column!important;justify-content:center!important;}body.login.lps-split-studio #login{flex:none!important;}}';
 if (lps_split_option('mobile_first')==='brand') $css.='@media(max-width:760px){body.login.lps-split-studio .lps-split-panel{order:0}body.login.lps-split-studio #login{order:1}}';
 $url=lps_split_option('background_image');
 if ($url) $css.='body.login.lps-split-studio .lps-split-panel{background-image:linear-gradient(rgba(23,23,70,var(--lps-split-overlay)),rgba(23,23,70,var(--lps-split-overlay))),url("'.esc_url($url).'")!important;background-size:cover!important;background-position:center!important;}';
 return $css;
}
add_action('login_head',function(){if(lps_split_selected()) lps_print_inline_style('lps-split-settings-style', lps_split_css());},PHP_INT_MAX);
/** Insert optional welcome content inside WordPress's #login, before the form. */
add_filter('login_message',function($message){
 if (!lps_split_selected()) return $message;
 $heading=lps_split_option('welcome'); $desc=lps_split_option('welcome_text');
 if ($heading==='' && $desc==='') return $message;
 return '<div class="lps-split-welcome">'.($heading!==''?'<h2>'.esc_html($heading).'</h2>':'').($desc!==''?'<p>'.esc_html($desc).'</p>':'').'</div>'.$message;
},5);
/** Move copyright to #login only for this template; do not generate a default. */
add_action('login_footer',function(){
 if (!lps_split_selected() || trim((string)get_option('lps_login_copyright',''))==='') return;
 echo '<script>(function(){var c=document.querySelector("body.login .copyright"),l=document.getElementById("login");if(c&&l)l.appendChild(c);})();</script>';
},PHP_INT_MAX);

/** Decorative panel sits outside #login, leaving native WP login, reset, and error flows untouched. */
add_action('login_header', function() {
    if (!lps_split_selected()) return;
    echo '<aside id="lps-split-panel" class="lps-split-panel" aria-label="Welcome">'
      . '<div class="lps-split-art" aria-hidden="true"><span class="lps-split-orbit"></span><span class="lps-split-glass"></span><span class="lps-split-spark"></span></div>'
      . '<div class="lps-split-brand"><span class="lps-split-monogram">' . esc_html(lps_split_option('brand_icon')) . '</span><span>' . esc_html(lps_split_option('brand_label')) . '</span></div>'
      . '<div class="lps-split-story"><span class="lps-split-eyebrow">' . esc_html(lps_split_option('eyebrow')) . '</span><h2>' . esc_html(lps_split_option('headline')) . '</h2><p>' . esc_html(lps_split_option('description')) . '</p><span class="lps-split-story-line"></span></div>'
      . '<div class="lps-split-footnote">' . esc_html(lps_split_option('footnote')) . ' <span>↗</span></div></aside>';
}, 5);
/** Shared background fields remain available without replacing the preset unless set. */
add_action('login_head', function () {
    if (!lps_split_selected()) return;
    $type = lps_gradient_sanitize_type(get_option('lps_background_type','auto'));
    $color = sanitize_hex_color(get_option('lps_login_background_color',''));
    $image = esc_url_raw(get_option('lps_body_bg_img',''), array('http','https'));
    $css = '';
    if ($type === 'gradient') $css .= 'background-image:' . lps_gradient_css() . '!important;';
    elseif ($type === 'solid') $css .= 'background-image:none!important;';
    elseif ($image && ($type === 'image' || $type === 'auto')) $css .= 'background-image:url("' . esc_url($image) . '")!important;';
    elseif ($color) $css .= 'background-image:none!important;';
    if ($color) $css .= 'background-color:' . esc_attr($color) . '!important;';
    if ($css) lps_print_inline_style('lps-split-saved-background', 'body.login.lps-split-studio{' . $css . '}');
},PHP_INT_MAX);

add_action('admin_enqueue_scripts',function(){
 // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen routing for stylesheet/script enqueue.
 if (!isset($_GET['page']) || !is_string($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
 $file=dirname(__DIR__).'/js/lps-split-studio-settings.js';
 wp_enqueue_script('lps-split-settings',plugins_url('../js/lps-split-studio-settings.js',__FILE__),array('lps-modern-minimal-preview'),substr(hash_file('sha256',$file),0,12),true);
 $css=dirname(__DIR__).'/css/lps-split-settings-admin.css';
 wp_enqueue_style('lps-split-settings-admin',plugins_url('../css/lps-split-settings-admin.css',__FILE__),array(),substr(hash_file('sha256',$css),0,12));
},110);
