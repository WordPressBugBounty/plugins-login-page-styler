<?php
/** Modern Minimal pilot: styling only, never alters login behavior. */
if (!defined('ABSPATH')) exit;
// Restrict stored values to supported CSS choices.
function lps_gradient_sanitize_type($value) { return in_array($value, array('auto','solid','gradient','image'), true) ? $value : 'auto'; }
function lps_gradient_sanitize_style($value) { return in_array($value, array('linear','radial'), true) ? $value : 'linear'; }
function lps_gradient_sanitize_angle($value) { return max(0, min(360, absint($value))); }
function lps_gradient_css() {
    $first = sanitize_hex_color(get_option('lps_gradient_color_one', '#e7e8ff')) ?: '#e7e8ff';
    $second = sanitize_hex_color(get_option('lps_gradient_color_two', '#d9f4f0')) ?: '#d9f4f0';
    if (get_option('lps_gradient_style', 'linear') === 'radial') return 'radial-gradient(circle, ' . $first . ', ' . $second . ')';
    return 'linear-gradient(' . lps_gradient_sanitize_angle(get_option('lps_gradient_angle', 135)) . 'deg, ' . $first . ', ' . $second . ')';
}
function lps_modern_minimal_body_class($classes) {
    if (!lps_design_should_render()) return $classes;
    if (get_option('lps_design_preset', 'default') !== 'modern-minimal') return $classes;
    if (is_array($classes)) { $classes[] = 'lps-modern-minimal'; return $classes; }
    return trim($classes . ' lps-modern-minimal');
}
add_filter('login_body_class', 'lps_modern_minimal_body_class');
function lps_modern_minimal_styles() {
    if (!lps_design_should_render()) return;
    if (get_option('lps_design_preset', 'default') !== 'modern-minimal') return;
    wp_enqueue_style('lps-modern-minimal', plugins_url('../css/lps-modern-minimal.css', __FILE__), array('login'), '9.9.34.2');
}
add_action('login_enqueue_scripts', 'lps_modern_minimal_styles', 15);
function lps_modern_minimal_admin() {
    wp_enqueue_style('lps-modern-minimal-preview-css', plugins_url('../css/lps-modern-minimal.css', __FILE__), array(), '9.9.34.2');
    wp_enqueue_script('lps-color-live-sync', plugins_url('../js/lps-color-live-sync.js', __FILE__), array(), '9.9.34.9', true);
    wp_enqueue_script('lps-gradient-controls', plugins_url('../js/lps-gradient-controls.js', __FILE__), array(), '9.9.34.9', true);
    wp_enqueue_script('lps-modern-minimal-preview', plugins_url('../js/lps-modern-minimal-preview.js', __FILE__), array(), '9.9.35.15', true);
}
add_action('admin_enqueue_scripts', 'lps_modern_minimal_admin', 101);

/**
 * Respect saved user choices. Template defaults are only used when no custom
 * value is present. Output after the older login CSS to avoid specificity races.
 */
function lps_modern_minimal_saved_overrides() {
    if (!lps_design_should_render()) return;
    if (get_option('lps_design_preset', 'default') !== 'modern-minimal') return;
    $css = '';
    $logo = get_option('lps_login_logo', '');
    $width = absint(get_option('lps_login_logo_width', 0));
    $height = absint(get_option('lps_login_logo_height', 0));
    if ($logo) $css .= 'body.login.lps-modern-minimal #login h1 a{background-image:url("' . esc_url($logo) . '")!important;}';
    if ($width || $height) {
        $css .= 'body.login.lps-modern-minimal #login h1 a{';
        if ($width) $css .= 'width:' . $width . 'px!important;';
        if ($height) $css .= 'height:' . $height . 'px!important;';
        $css .= 'background-size:contain!important;}';
    }
    $background = trim((string) get_option('lps_login_background_color', ''));
    $image = esc_url_raw(get_option('lps_body_bg_img', ''), array('http', 'https'));
    $valid_color = (bool) preg_match('/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/', $background);
    $background_type = lps_gradient_sanitize_type(get_option('lps_background_type','auto'));
    $css .= 'body.login.lps-modern-minimal{background-color:' . ($valid_color ? esc_attr($background) : '#f5f7fc') . '!important;';
    if ($background_type === 'gradient') {
        $css .= 'background-image:' . lps_gradient_css() . '!important;';
    } elseif ($background_type === 'solid') {
        $css .= 'background-image:none!important;';
    } elseif ($background_type === 'image' && $image) {
        $css .= 'background-image:url("' . esc_url($image) . '")!important;';
    } elseif ($image && $background_type === 'auto') {
        $css .= 'background-image:url("' . esc_url($image) . '")!important;';
    } elseif ($valid_color) {
        $css .= 'background-image:none!important;';
    } else {
        $css .= 'background-image:radial-gradient(ellipse at 12% 12%,#e7e8ff 0%,transparent 44%),radial-gradient(ellipse at 90% 85%,#d9f4f0 0%,transparent 45%)!important;';
    }
    $size = get_option('lps_login_bg_size', 'cover');
    $position = get_option('lps_login_bg_position', 'center center');
    $repeat = get_option('lps_login_bg_repeat', 'no-repeat');
    if (!in_array($size, array('cover', 'contain', 'auto'), true)) $size = 'cover';
    if (!in_array($position, array('center center', 'center top', 'center bottom', 'left center', 'right center'), true)) $position = 'center center';
    if (!in_array($repeat, array('repeat', 'no-repeat', 'repeat-x', 'repeat-y'), true)) $repeat = 'no-repeat';
    $css .= 'background-size:' . esc_attr($size) . '!important;background-position:' . esc_attr($position) . '!important;background-repeat:' . esc_attr($repeat) . '!important;}';
    if ($css) lps_print_inline_style('lps-modern-minimal-saved-overrides', $css);
}
add_action('login_head', 'lps_modern_minimal_saved_overrides', PHP_INT_MAX);
