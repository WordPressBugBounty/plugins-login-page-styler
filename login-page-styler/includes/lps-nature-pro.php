<?php
/** Nature Pro: an independent visual preset, preserving WordPress login behavior. */
if (!defined('ABSPATH')) exit;
function lps_sanitize_nature_blur($value) { return min(40, max(0, absint($value))); }
/** Per-template blur values prevent frosted presets from changing other designs.
 * Existing saved blur is retained for the currently active design when upgrading,
 * except Split-Screen: older releases could apply Nature Pro blur there by accident.
 */
function lps_form_blur_map() {
    $saved = get_option('lps_form_blur_by_preset', array());
    if (!is_array($saved)) $saved = array();
    $allowed = array('default', 'modern-minimal', 'nature-pro', 'noir-editorial', 'split-studio', 'aurora-glass');
    $values = array();
    foreach ($allowed as $preset) {
        if (array_key_exists($preset, $saved)) {
            $values[$preset] = lps_sanitize_nature_blur($saved[$preset]);
        }
    }
    // Nature Pro and Aurora Glass intentionally start with a frosted effect.
    if (!array_key_exists('aurora-glass', $values)) $values['aurora-glass'] = 24;
    // Retain Nature Pro's legacy blur setting.
    if (!array_key_exists('nature-pro', $values)) {
        $legacy = get_option('lps_nature_form_blur', false);
        $values['nature-pro'] = $legacy !== false && get_option('lps_design_preset', 'default') === 'nature-pro'
            ? lps_sanitize_nature_blur($legacy) : 24;
    }
    return $values;
}
function lps_form_blur_for_preset($preset) {
    $values = lps_form_blur_map();
    return isset($values[$preset]) ? $values[$preset] : 0;
}
/** Settings API calls this when the shared slider is submitted. */
function lps_save_form_blur_for_preset($value) {
    $blur = lps_sanitize_nature_blur($value);
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Settings API sanitize callback runs during options.php processing after WordPress verifies its options nonce.
    $preset = isset($_POST['lps_design_preset']) ? sanitize_key(wp_unslash($_POST['lps_design_preset'])) : get_option('lps_design_preset', 'default');
    if (!in_array($preset, array('default','modern-minimal','nature-pro','noir-editorial','split-studio','aurora-glass'), true)) $preset = 'default';
    $values = lps_form_blur_map();
    $values[$preset] = $blur;
    update_option('lps_form_blur_by_preset', $values, false);
    return $blur;
}

function lps_nature_body_class($classes) {
    if (!lps_design_should_render()) return $classes;
    if (get_option('lps_design_preset', 'default') !== 'nature-pro') return $classes;
    if (is_array($classes)) { $classes[] = 'lps-nature-pro'; return $classes; }
    return trim($classes . ' lps-nature-pro');
}
add_filter('login_body_class', 'lps_nature_body_class');
function lps_nature_enqueue() {
    if (!lps_design_should_render()) return;
    if (get_option('lps_design_preset', 'default') === 'nature-pro') {
        wp_enqueue_style('lps-nature-pro', plugins_url('../css/lps-nature-pro.css', __FILE__), array('login'), '9.9.34.9');
    }
}
add_action('login_enqueue_scripts', 'lps_nature_enqueue', 15);
function lps_nature_preview_style() {
    wp_enqueue_style('lps-nature-preview', plugins_url('../css/lps-nature-pro.css', __FILE__), array(), '9.9.34.9');
}
add_action('admin_enqueue_scripts', 'lps_nature_preview_style', 101);
/** Existing saved color, image and gradient options override preset defaults. */
function lps_nature_saved_background() {
    if (!lps_design_should_render()) return;
    if (get_option('lps_design_preset', 'default') !== 'nature-pro') return;
    $type = lps_gradient_sanitize_type(get_option('lps_background_type', 'auto'));
    $color = sanitize_hex_color(get_option('lps_login_background_color', ''));
    $image = esc_url_raw(get_option('lps_body_bg_img', ''));
    $css = '';
    if ($type === 'gradient') $css = 'background-image:' . lps_gradient_css() . '!important;';
    elseif ($type === 'solid') $css = 'background-image:none!important;';
    elseif ($image && ($type === 'image' || $type === 'auto')) $css = 'background-image:url("' . esc_url($image) . '")!important;';
    elseif ($color) $css = 'background-image:none!important;';
    if ($color) $css .= 'background-color:' . esc_attr($color) . '!important;';
    $size = get_option('lps_login_bg_size', 'cover');
    $position = get_option('lps_login_bg_position', 'center center');
    $repeat = get_option('lps_login_bg_repeat', 'no-repeat');
    if (in_array($size, array('cover','contain','auto'), true)) $css .= 'background-size:' . esc_attr($size) . '!important;';
    if (in_array($position, array('center center','center top','center bottom','left center','right center'), true)) $css .= 'background-position:' . esc_attr($position) . '!important;';
    if (in_array($repeat, array('no-repeat','repeat','repeat-x','repeat-y'), true)) $css .= 'background-repeat:' . esc_attr($repeat) . '!important;';
    if ($css) lps_print_inline_style('lps-nature-saved', 'body.login.lps-nature-pro{' . $css . '}');
}
add_action('login_head', 'lps_nature_saved_background', PHP_INT_MAX);
/** Shared form backdrop blur: applies to default login and every template.
 * Nature Pro keeps its historic 24px default; all other presets default to 0px.
 * Transparency is required for the frosted effect to be visible.
 */
function lps_shared_saved_form_blur() {
    if (!lps_design_should_render()) return;
    $blur = lps_form_blur_for_preset(get_option('lps_design_preset', 'default'));
    if ($blur === 0) return;
    // Split-Screen's otherwise opaque white card would conceal the backdrop effect.
    // Only supply a translucent default when the user has not set a form color/image.
    $split_css = '';
    if (get_option('lps_design_preset', 'default') === 'split-studio'
        && !get_option('lps_login_form_color') && !get_option('lps_login_form_bg')) {
        $split_css = 'body.login.lps-split-studio #loginform,body.login.lps-split-studio #registerform,body.login.lps-split-studio #lostpasswordform{background:rgba(255,255,255,.78)!important;}';
    }
    lps_print_inline_style('lps-shared-form-blur', 'body.login #loginform,body.login #registerform,body.login #lostpasswordform{-webkit-backdrop-filter:blur(' . absint(lps_sanitize_nature_blur($blur)) . 'px) saturate(135%)!important;backdrop-filter:blur(' . absint(lps_sanitize_nature_blur($blur)) . 'px) saturate(135%)!important;}' . $split_css);
}
add_action('login_head', 'lps_shared_saved_form_blur', PHP_INT_MAX);

/** Move the existing WordPress logo into the Nature Pro glass form.
 * The same DOM operation is used by the Customizer preview. No logo is cloned.
 */
function lps_nature_logo_in_form() {
    if (!lps_design_should_render()) return;
    if (get_option('lps_design_preset', 'default') !== 'nature-pro') return;
    ?>
    <script id="lps-nature-logo-placement">
    (function () {
      function moveNatureLogo() {
        var wrapper = document.getElementById('login');
        if (!wrapper) return;
        var heading = wrapper.querySelector('h1');
        var form = wrapper.querySelector('#loginform, #registerform, #lostpasswordform');
        if (heading && form && heading.parentNode !== form) form.insertBefore(heading, form.firstChild);
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', moveNatureLogo);
      else moveNatureLogo();
    }());
    </script>
    <?php
}
add_action('login_footer', 'lps_nature_logo_in_form', 99);
