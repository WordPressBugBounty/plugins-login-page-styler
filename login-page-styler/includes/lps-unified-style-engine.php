<?php
/**
 * Login Page Styler - unified styling engine.
 *
 * Cascade order for the real login page AND the iframe preview:
 *  1. Existing template preset (saved as lps_login_custom_css by the gallery).
 *  2. Common settings from the original Customizer.
 *  3. Template-specific effects.
 *  4. User-authored CSS (always last).
 *
 * The same common rule definitions are sent to the live-preview JavaScript.
 * No CSS is written back into the user's Custom CSS editor.
 */
if (!defined('ABSPATH')) exit;

function lps_unified_rules() {
    return array(
        'lps_login_background_color' => array('body.login','background-color','color'),
        'lps_body_bg_img' => array('body.login','background-image','image'),
        // Form background color is emitted with its alpha in lps_unified_surface_css().
        'lps_login_form_border_color' => array('body.login #loginform,body.login #registerform,body.login #lostpasswordform','border-color','color'),
        'lps_login_form_border_radius' => array('body.login #loginform,body.login #registerform,body.login #lostpasswordform','border-radius','px'),
        'lps_login_form_width' => array('body.login #login','width','px'),
        'lps_login_label_color' => array('body.login #login label','color','color'),
        'lps_login_label_size' => array('body.login #login label','font-size','px'),
        // Input background color is emitted with its alpha in lps_unified_surface_css().
        'lps_login_form_input_text_color' => array('body.login #login .input','color','color'),
        'lps_login_form_input_feild_border_color' => array('body.login #login .input','border-color','color'),
        'lps_login_form_input_feild_border_radius' => array('body.login #login .input','border-radius','px'),
        'lps_login_button_color' => array('body.login #login .button-primary','background','color'),
        'lps_login_button_text_color' => array('body.login #login .button-primary','color','color'),
        'lps_login_button_border_color' => array('body.login #login .button-primary','border-color','color'),
        'lps_login_button_border_radius' => array('body.login #login .button-primary','border-radius','px'),
        'lps_login_nav_color' => array('body.login #nav a,body.login #backtoblog a','color','color'),
        'lps_login_nav_size' => array('body.login #nav a,body.login #backtoblog a','font-size','px')
    );
}

function lps_unified_value($value, $kind) {
    $value = trim((string) $value);
    if ($value === '') return '';
    if ($kind === 'color') return sanitize_hex_color($value) ?: '';
    if ($kind === 'px') return is_numeric($value) ? min(1200,max(0,(float)$value)).'px' : '';
    if ($kind === 'image') {
        $url = esc_url_raw($value, array('http','https'));
        return $url ? 'url("'.str_replace(array('"','\\'), '', $url).'")' : '';
    }
    return '';
}

function lps_unified_common_css($values) {
    $css = '';
    foreach (lps_unified_rules() as $name => $definition) {
        if (!isset($values[$name])) continue;
        if ($name === 'lps_login_form_width' && function_exists('lps_aurora_selected') && lps_aurora_selected()) continue;
        list($selector,$property,$kind) = $definition;
        $value = lps_unified_value($values[$name],$kind);
        if ($value === '') continue;
        // A color selection must replace a template gradient or image, not sit behind it.
        $extra = ($name === 'lps_login_background_color' && empty($values['lps_body_bg_img'])) ? 'background-image:none!important;' : '';
        $css .= $selector.'{'.$property.':'.$value.'!important;'.$extra.'}' . "\n";
    }
    return $css;
}



/**
 * Aurora compatibility layer.
 *
 * Aurora owns its TWO-COLUMN LAYOUT and overall form width; the Main Customizer
 * owns explicitly chosen surface, border, label and input appearance.
 * Empty settings do not replace Aurora's carefully chosen defaults.
 * This function feeds the same values into the saved page as the iframe preview.
 */
function lps_aurora_form_compat_css($values) {
    $form = 'body.login.lps-aurora-glass #login form';
    $input = 'body.login.lps-aurora-glass #login form input.input,body.login.lps-aurora-glass #login form input[type=text],body.login.lps-aurora-glass #login form input[type=email],body.login.lps-aurora-glass #login form input[type=password]';
    $label = 'body.login.lps-aurora-glass #login form label';
    $css = '';
    $value = static function($key) use ($values) { return trim((string)($values[$key] ?? '')); };
    $color = static function($key) use ($value) { return sanitize_hex_color($value($key)) ?: ''; };
    $px = static function($key) use ($value) { $v=$value($key); return $v!=='' && is_numeric($v) ? max(0,min(1200,(float)$v)).'px' : ''; };
    $styles = array('none','solid','dashed','dotted','double');
    $add = static function($selector,$property,$setting) use (&$css,$value) {
        if ($setting !== '') $css .= $selector.'{'.$property.':'.$setting.'!important;}' . "\n";
    };
    // Background must override Aurora's full gradient shorthand, not merely
    // background-color (which would remain hidden behind that gradient).
    $formColor=$color('lps_login_form_color');
    $image=esc_url_raw($value('lps_login_form_bg'),array('http','https'));
    $alpha=static function($key) use ($value) { $a=$value($key); return $a==='' ? 1 : max(0,min(1,(float)$a)); };
    $rgba=static function($hex,$opacity) {
        $hex=ltrim($hex,'#');
        if(strlen($hex)===3) $hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return sprintf('rgba(%d,%d,%d,%s)',hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2)),$opacity);
    };
    if ($image) $add($form,'background','url("'.str_replace(array('"','\\'),'',$image).'") center / cover no-repeat');
    elseif ($formColor) $add($form,'background',$rgba($formColor,$alpha('lps_login_form_color_opacity')));

    $add($form,'border-color',$color('lps_login_form_border_color'));
    $add($form,'border-radius',$px('lps_login_form_border_radius'));
    $style=$value('lps_login_form_border_style');
    if (in_array($style,$styles,true)) $add($form,'border-style',$style);
    // A zero slider value is the untouched default; don't erase the template
    // border unless the user has selected a border style.
    $borderSize=$px('lps_login_form_border_size');
    if ($borderSize!=='' && ((float)$borderSize>0 || in_array($style,$styles,true))) $add($form,'border-width',$borderSize);

    $add($label,'color',$color('lps_login_label_color'));
    $add($label,'font-size',$px('lps_login_label_size'));
    $add('body.login.lps-aurora-glass #login form .forgetmenot label','font-size',$px('lps_login_remember_label_size'));

    $inputColor=$color('lps_login_form_input_color');
    if ($inputColor) $add($input,'background',$rgba($inputColor,$alpha('lps_login_form_input_color_opacity')));
    $add($input,'color',$color('lps_login_form_input_text_color'));
    $add($input,'-webkit-text-fill-color',$color('lps_login_form_input_text_color'));
    $add($input,'border-color',$color('lps_login_form_input_feild_border_color'));
    $add($input,'border-radius',$px('lps_login_form_input_feild_border_radius'));
    $inputStyle=$value('lps_login_form_input_border_style');
    if (in_array($inputStyle,$styles,true)) $add($input,'border-style',$inputStyle);
    // Respect a deliberately saved form shadow, but never replace Aurora's
    // luminous default shadow if the owner has not customized this group.
    $shadowKeys=array('lps_box_shadow_horizontal','lps_box_shadow_vertical','lps_box_shadow_blur','lps_box_shadow_spread','lps_box_shadow_color','lps_box_shadow_opacity');
    $shadowSet=false;
    foreach($shadowKeys as $key) if($value($key)!=='') { $shadowSet=true; break; }
    if($shadowSet){
        $number=static function($key,$limit=500)use($value){$v=$value($key);return is_numeric($v)?max(-$limit,min($limit,(float)$v)):0;};
        $shadowColor=$color('lps_box_shadow_color') ?: '#000000';
        $add($form,'box-shadow',$number('lps_box_shadow_horizontal').'px '.$number('lps_box_shadow_vertical').'px '.max(0,$number('lps_box_shadow_blur')).'px '.max(0,$number('lps_box_shadow_spread')).'px '.$rgba($shadowColor,$alpha('lps_box_shadow_opacity')));
    }
    $inputSize=$px('lps_login_form_input_border_size');
    if ($inputSize!=='' && ((float)$inputSize>0 || in_array($inputStyle,$styles,true))) $add($input,'border-width',$inputSize);
    return $css;
}

/** One surface renderer for both login-page output and live preview. */
function lps_unified_surface_css($values) {
    $css = '';
    $alpha = static function($key) use ($values) {
        if (!isset($values[$key]) || $values[$key] === '') return 1;
        return max(0, min(1, (float) $values[$key]));
    };
    $rgba = static function($hex, $opacity) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return sprintf('rgba(%d,%d,%d,%s)', hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2)), $opacity);
    };
    $form = 'body.login #loginform,body.login #registerform,body.login #lostpasswordform';
    $fc = sanitize_hex_color($values['lps_login_form_color'] ?? '');
    if ($fc) $css .= $form.'{background-color:'.$rgba($fc,$alpha('lps_login_form_color_opacity')).'!important;}';
    $image = esc_url_raw($values['lps_login_form_bg'] ?? '',array('http','https'));
    if ($image) $css .= $form.'{background-image:url("'.esc_attr($image).'")!important;background-size:cover!important;background-position:center!important;background-repeat:no-repeat!important;}';
    $ic = sanitize_hex_color($values['lps_login_form_input_color'] ?? '');
    if ($ic) $css .= 'body.login #login .input{background-color:'.$rgba($ic,$alpha('lps_login_form_input_color_opacity')).'!important;}';
    $size = $values['lps_login_bg_size'] ?? 'cover';
    $position = $values['lps_login_bg_position'] ?? 'center center';
    $repeat = $values['lps_login_bg_repeat'] ?? 'no-repeat';
    if (!in_array($size,array('cover','contain','auto'),true)) $size='cover';
    if (!in_array($position,array('center center','center top','center bottom','left center','right center'),true)) $position='center center';
    if (!in_array($repeat,array('no-repeat','repeat','repeat-x','repeat-y'),true)) $repeat='no-repeat';
    $css .= 'html{background-image:none!important;}body.login{min-height:100vh;background-size:'.$size.'!important;background-position:'.$position.'!important;background-repeat:'.$repeat.'!important;}';
    return $css;
}

function lps_unified_output() {
    if (!lps_design_should_render()) return;
    $values = array();
    foreach (array_merge(array_keys(lps_unified_rules()),array('lps_login_form_color','lps_login_form_color_opacity','lps_login_form_bg','lps_login_form_input_color','lps_login_form_input_color_opacity','lps_login_bg_size','lps_login_bg_position','lps_login_bg_repeat','lps_login_form_border_style','lps_login_form_border_size','lps_login_form_input_border_style','lps_login_form_input_border_size','lps_login_remember_label_size','lps_box_shadow_horizontal','lps_box_shadow_vertical','lps_box_shadow_blur','lps_box_shadow_spread','lps_box_shadow_color','lps_box_shadow_opacity')) as $name) {
        $values[$name] = get_option($name, '');
    }
    lps_print_inline_style('lps-unified-common', lps_unified_common_css($values) . lps_unified_surface_css($values) . lps_aurora_form_compat_css($values));
    // Use the same frontend path as the other saved visual settings.
    lps_notice_color();
    $user_css = get_option('lps_user_custom_css','');
    if (is_string($user_css) && trim($user_css) !== '') {
        lps_print_inline_style('lps-unified-user-css', wp_strip_all_tags($user_css));
    }
}
// The late hook wins over the older styling functions and preset rules.
add_action('login_head','lps_unified_output', PHP_INT_MAX);

function lps_unified_admin_preview() {
    // Only enqueue the iframe preview engine on the Login Customizer screen.
    // Other admin screens do not contain its form or iframe.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing for preview JS; no state change.
    if (!isset($_GET['page']) || !is_string($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
    wp_enqueue_script('lps-unified-live', plugins_url('../js/lps-unified-live.js', __FILE__), array('jquery'), '9.9.34.9', true);
    wp_localize_script('lps-unified-live','lpsUnifiedPreview',array('rules'=>lps_unified_rules(), 'activeTemplate'=>'', 'positionCSS'=>function_exists('lps_layout_position_css') ? array_combine(range(1,9),array_map('lps_layout_position_css',range(1,9))) : array()));
}
add_action('admin_enqueue_scripts','lps_unified_admin_preview', 99);

