<?php
/** Aurora Glass: a visual WordPress login preset. Does not alter authentication. */
if (!defined('ABSPATH')) exit;
function lps_aurora_selected() {
    return lps_design_should_render() && get_option('lps_design_preset', 'default') === 'aurora-glass';
}
add_filter('login_body_class', function ($classes) {
    if (!lps_aurora_selected()) return $classes;
    // Explicit Main Customizer form backgrounds must remain authoritative.
    $has_custom_form_background = sanitize_hex_color(get_option('lps_login_form_color', ''))
        || esc_url_raw(get_option('lps_login_form_bg', ''), array('http', 'https'));
    $extra = $has_custom_form_background ? ' lps-aurora-custom-form-background' : '';
    if (is_array($classes)) {
        $classes[] = 'lps-aurora-glass';
        if ($has_custom_form_background) $classes[] = 'lps-aurora-custom-form-background';
        return $classes;
    }
    return trim($classes . ' lps-aurora-glass' . $extra);
});
add_action('login_enqueue_scripts', function () {
    if (!lps_aurora_selected()) return;
    $file = dirname(__DIR__) . '/css/lps-aurora-glass.css';
    wp_enqueue_style('lps-aurora-glass', plugins_url('../css/lps-aurora-glass.css', __FILE__), array('login'), substr(hash_file('sha256', $file), 0, 12));
}, 15);
/** Shared Aurora layout function for saved login page and Customizer iframe. */
add_action('login_enqueue_scripts', function () {
    if (!lps_aurora_selected()) return;
    $file = dirname(__DIR__) . '/js/lps-aurora-layout.js';
    wp_enqueue_script('lps-aurora-layout', plugins_url('../js/lps-aurora-layout.js', __FILE__), array(), substr(hash_file('sha256', $file), 0, 12), true);
}, 16);
add_action('admin_enqueue_scripts', function () {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen routing; the screen identifier is sanitized, and no state is changed.
    if (!isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
    $file = dirname(__DIR__) . '/js/lps-aurora-layout.js';
    wp_enqueue_script('lps-aurora-layout-admin', plugins_url('../js/lps-aurora-layout.js', __FILE__), array(), substr(hash_file('sha256', $file), 0, 12), true);
}, 100);
add_action('admin_enqueue_scripts', function () {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen routing; the screen identifier is sanitized, and no state is changed.
    if (!isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
    $file = dirname(__DIR__) . '/css/lps-aurora-glass.css';
    wp_enqueue_style('lps-aurora-glass-preview', plugins_url('../css/lps-aurora-glass.css', __FILE__), array(), substr(hash_file('sha256', $file), 0, 12));
}, 101);
/** Only arrange existing WordPress elements; never clone inputs or replace their event handlers.
 * The form is the ONE glass card, as in Nature Pro. A branding column and
 * content wrapper make the two-column layout responsive without altering auth.
 */
add_action('login_footer', function () {
    if (!lps_aurora_selected()) return;
    ?>
    <script id="lps-aurora-layout-script">
    (function () {
      function setup() {
        if (window.lpsAuroraLayout) window.lpsAuroraLayout(document, true);
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
      else setup();
    }());
    </script>
    <?php
}, 90);
/** The standard Customizer background controls remain authoritative when set. */
add_action('login_head', function () {
    if (!lps_aurora_selected()) return;
    $type = lps_gradient_sanitize_type(get_option('lps_background_type', 'auto'));
    $color = sanitize_hex_color(get_option('lps_login_background_color', ''));
    $image = esc_url_raw(get_option('lps_body_bg_img', ''), array('http', 'https'));
    $css = '';
    if ($type === 'gradient') $css = 'background-image:' . lps_gradient_css() . '!important;';
    elseif ($type === 'solid') $css = 'background-image:none!important;';
    elseif ($image && ($type === 'image' || $type === 'auto')) $css = 'background-image:url("' . esc_url($image) . '")!important;';
    elseif ($color) $css = 'background-image:none!important;';
    if ($color) $css .= 'background-color:' . esc_attr($color) . '!important;';
    if ($css) lps_print_inline_style('lps-aurora-saved-background', 'body.login.lps-aurora-glass{' . $css . '}');
}, PHP_INT_MAX);

add_action('login_footer', function () {
    if (!lps_aurora_selected() || trim((string)get_option('lps_login_copyright','')) === '') return;
    echo '<script>(function(){var c=document.querySelector("body.login .copyright"),l=document.getElementById("login");if(c&&l)l.appendChild(c);})();</script>';
}, PHP_INT_MAX);


/** Aurora-only options. The normal Customizer remains authoritative for fields, logo,
 * form backgrounds, buttons, and form blur. */
function lps_aurora_fields() {
    return array(
        'brand_side'=>array('Branding side','select','left',array('left'=>'Left','right'=>'Right')),
        'brand_width'=>array('Brand column width (%)','number','49',array(35,65)),
        'brand_label'=>array('Small branding label','text','STUDIO / ACCESS'),
        'headline'=>array('Branding headline','text','Beautifully connected.'),
        'description'=>array('Branding description','textarea','A welcoming space designed for your next sign-in.'),
        'footer'=>array('Branding footer text','text',''),
        'welcome'=>array('Login welcome heading','text','Welcome back'),
        'welcome_description'=>array('Login welcome description','text','Sign in to your account'),
        'accent'=>array('Accent color','color','#f3499d'),
        'text_color'=>array('Text color','color','#ffffff'),
        'headline_font'=>array('Brand headline Google Font','google_font','Inter'),
        'headline_size'=>array('Brand headline size (px)','number','48',array(24,76)),
        'welcome_font'=>array('Welcome heading Google Font','google_font','Inter'),
        'welcome_size'=>array('Welcome heading size (px)','number','36',array(18,56)),
        'glass_tint'=>array('Glass tint opacity (%)','number','68',array(10,90)),
        'glass_radius'=>array('Glass card radius (px)','number','28',array(0,60)),
        'divider'=>array('Show column divider','checkbox','1'),
        'decorative_image'=>array('Decorative background image','url',''),
        'mobile_brand'=>array('Show branding content on mobile','checkbox','1'),
        'mobile_first'=>array('Mobile order','select','brand',array('brand'=>'Brand first','form'=>'Login first'))
    );
}
function lps_aurora_option($key) {
    $fields=lps_aurora_fields(); if (!isset($fields[$key])) return '';
    $value=get_option('lps_aurora_'.$key,false);
    return $value===false ? $fields[$key][2] : $value;
}
function lps_aurora_sanitize($value,$key) {
    $fields=lps_aurora_fields(); if (!isset($fields[$key])) return '';
    $field=$fields[$key]; $type=$field[1];
    if($type==='checkbox')return $value==='1'?'1':'0';
    if($type==='select')return isset($field[3][$value])?$value:$field[2];
    if($type==='number')return (string)max($field[3][0],min($field[3][1],(int)$value));
    if($type==='color')return sanitize_hex_color($value)?:$field[2];
    if($type==='url')return esc_url_raw($value,array('http','https'));
    if($type==='textarea')return sanitize_textarea_field($value);
    if($type==='google_font')return preg_match('/^[\p{L}\p{N} ._:-]{1,90}$/u',(string)$value)?$value:$field[2];
    return sanitize_text_field($value);
}
add_action('admin_init',function(){
    foreach(lps_aurora_fields() as $key=>$field){
        register_setting('lps-settings-group','lps_aurora_'.$key,array('sanitize_callback'=>function($v)use($key){return lps_aurora_sanitize($v,$key);}));
    }
});
add_action('wp_ajax_lps_reset_aurora_template',function(){
    if(!current_user_can('manage_options'))wp_send_json_error(array('message'=>'Not allowed'),403);
    check_ajax_referer('lps_reset_aurora','nonce');
    foreach(array_keys(lps_aurora_fields()) as $key)delete_option('lps_aurora_'.$key);
    wp_send_json_success();
});
function lps_aurora_settings_css(){
    $side=lps_aurora_option('brand_side'); $width=(int)lps_aurora_option('brand_width');
    $radius=(int)lps_aurora_option('glass_radius'); $opacity=(int)lps_aurora_option('glass_tint')/100;
    $font1=explode(':',lps_aurora_option('headline_font'))[0];
    $font2=explode(':',lps_aurora_option('welcome_font'))[0];
    $css='body.login.lps-aurora-glass{--lps-aurora-width:'.$width.'%;--lps-aurora-card-radius:'.$radius.'px;--lps-aurora-tint:'.$opacity.';--lps-aurora-accent:'.lps_aurora_option('accent').';--lps-aurora-text:'.lps_aurora_option('text_color').';--lps-aurora-headline-size:'.(int)lps_aurora_option('headline_size').'px;--lps-aurora-welcome-size:'.(int)lps_aurora_option('welcome_size').'px;--lps-aurora-headline-font:"'.esc_attr($font1).'";--lps-aurora-welcome-font:"'.esc_attr($font2).'";}';
    if($side==='right')$css.='body.login.lps-aurora-glass #login form{direction:rtl;}body.login.lps-aurora-glass #login form>.lps-aurora-brand,body.login.lps-aurora-glass #login form>.lps-aurora-fields{direction:ltr;}body.login.lps-aurora-glass .lps-aurora-brand{border-right:0;border-left:1px solid rgba(255,255,255,.57);padding:2px 0 2px clamp(16px,3vw,44px);}';
    if(lps_aurora_option('divider')!=='1')$css.='body.login.lps-aurora-glass .lps-aurora-brand{border-color:transparent!important;}';
    if(lps_aurora_option('mobile_brand')!=='1')$css.='@media(max-width:820px){body.login.lps-aurora-glass .lps-aurora-brand-story,body.login.lps-aurora-glass .lps-aurora-brand-footer{display:none!important;}body.login.lps-aurora-glass .lps-aurora-brand{border:0!important;padding:0!important;}}';
    if(lps_aurora_option('mobile_first')==='form')$css.='@media(max-width:820px){body.login.lps-aurora-glass .lps-aurora-fields{order:0}body.login.lps-aurora-glass .lps-aurora-brand{order:1}}';
    $image=lps_aurora_option('decorative_image');
    if($image)$css.='body.login.lps-aurora-glass{background-image:url("'.esc_url($image).'")!important;}';
    return $css;
}
/** Load chosen Google Fonts for the published login; fall back gracefully if unavailable. */
add_action('login_enqueue_scripts',function(){
    if(!lps_aurora_selected())return;
    $families=array();
    foreach(array('headline_font','welcome_font') as $key){
        $family=trim(explode(':',lps_aurora_option($key))[0]);
        if($family!=='' && preg_match('/^[\p{L}\p{N} ._-]{1,90}$/u',$family))$families[]=$family;
    }
    $families=array_unique($families);
    if($families){
        $parts=array_map(function($family){return str_replace('%20','+',rawurlencode($family)).':wght@400;500;600;700';},$families);
        wp_enqueue_style('lps-aurora-custom-fonts','https://fonts.googleapis.com/css2?family='.implode('&family=',$parts).'&display=swap',array(),'1.0.0');
    }
},18);
add_action('login_head',function(){if(!lps_aurora_selected())return;lps_print_inline_style('lps-aurora-settings-css', lps_aurora_settings_css());},PHP_INT_MAX-1);
add_action('login_footer',function(){
    if(!lps_aurora_selected())return;
    $data=array();foreach(lps_aurora_fields() as $key=>$unused)$data[$key]=lps_aurora_option($key);
    echo '<script>if(window.lpsAuroraConfigure){window.lpsAuroraConfigure(document,'.wp_json_encode($data,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).');}</script>';
},100);
add_action('admin_enqueue_scripts',function(){
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen routing; the screen identifier is sanitized, and no state is changed.
    if(!isset($_GET['page'])||sanitize_key(wp_unslash($_GET['page']))!=='lps_option')return;
    $file=dirname(__DIR__).'/js/lps-aurora-settings.js';
    wp_enqueue_script('lps-aurora-settings',plugins_url('../js/lps-aurora-settings.js',__FILE__),array('jquery','lps-modern-minimal-preview'),substr(hash_file('sha256',$file),0,12),true);
    $css=dirname(__DIR__).'/css/lps-aurora-admin.css';
    wp_enqueue_style('lps-aurora-admin',plugins_url('../css/lps-aurora-admin.css',__FILE__),array(),substr(hash_file('sha256',$css),0,12));
},111);
