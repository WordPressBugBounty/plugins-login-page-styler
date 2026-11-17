<?php
/** Noir Editorial preset: visual presentation only, no authentication changes. */
if (!defined('ABSPATH')) exit;
function lps_noir_body_class($classes) {
    if (!lps_design_should_render() || get_option('lps_design_preset','default') !== 'noir-editorial') return $classes;
    if (is_array($classes)) { $classes[]='lps-noir-editorial'; return $classes; }
    return trim($classes . ' lps-noir-editorial');
}
add_filter('login_body_class','lps_noir_body_class');
function lps_noir_styles() {
    if (!lps_design_should_render() || get_option('lps_design_preset','default') !== 'noir-editorial') return;
    wp_enqueue_style('lps-noir-editorial', plugins_url('../css/lps-noir-editorial.css',__FILE__), array('login'), '9.9.35.13');
}
add_action('login_enqueue_scripts','lps_noir_styles',15);
function lps_noir_admin_styles() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen routing; the slug is sanitized before comparison.
    if (!isset($_GET['page']) || !is_string($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
    wp_enqueue_style('lps-noir-editorial-preview',plugins_url('../css/lps-noir-editorial.css',__FILE__),array(),'9.9.35.13');
}
add_action('admin_enqueue_scripts','lps_noir_admin_styles',101);
/** Preserve the common background options without overwriting Noir defaults when unset. */
function lps_noir_saved_background() {
    if (!lps_design_should_render() || get_option('lps_design_preset','default') !== 'noir-editorial') return;
    $type = lps_gradient_sanitize_type(get_option('lps_background_type','auto'));
    $color = sanitize_hex_color(get_option('lps_login_background_color',''));
    $image = esc_url_raw(get_option('lps_body_bg_img',''),array('http','https'));
    $css = '';
    if ($type==='gradient') $css='background-image:'.lps_gradient_css().'!important;';
    elseif ($type==='solid') $css='background-image:none!important;';
    elseif ($image && ($type==='image'||$type==='auto')) $css='background-image:url("'.esc_url($image).'")!important;';
    elseif ($color) $css='background-image:none!important;';
    if ($color) $css.='background-color:'.esc_attr($color).'!important;';
    if ($css!=='') lps_print_inline_style('lps-noir-background', 'body.login.lps-noir-editorial{' . $css . '}');
}
add_action('login_head','lps_noir_saved_background',PHP_INT_MAX);
