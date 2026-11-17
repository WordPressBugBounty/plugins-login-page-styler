<?php
/**
 * Free Customizer feature map: These settings are ONLY interactive admin previews.
 * Their WordPress option registrations are removed, and any previously saved Pro
 * values are ignored in Free so a downgrade cannot keep Pro effects on the login page.
 * Do not add frontend renderers for these settings in this package.
 */
if (!defined('ABSPATH')) exit;
function lps_free_customizer_pro_options() { return array('lps_gradient_style' => 'linear', 'lps_gradient_angle' => 135, 'lps_background_animation' => 'none', 'lps_gfonttext_logo' => '', 'lps_login_form_bg' => '', 'lps_login_form_color_opacity' => '', 'lps_nature_form_blur' => '', 'lps_login_animation' => '', 'lps_login_form_border_style' => '', 'lps_login_form_border_size' => '', 'lps_login_form_input_color_opacity' => '', 'lps_login_form_input_border_style' => '', 'lps_login_form_input_border_size' => '', 'lps_box_shadow_vertical' => '', 'lps_box_shadow_horizontal' => '', 'lps_box_shadow_blur' => '', 'lps_box_shadow_color' => '', 'lps_box_shadow_opacity' => '', 'lps_box_shadow_spread' => '', 'lps_gfontlab' => '', 'lps_gfont_inputtext' => '', 'lps_gfontlink' => '', 'lps_gfontmsg' => '', 'lps_gfontbtn' => '', 'lps_login_button_color_hover' => '', 'lps_login_button_text_color_hover' => '', 'lps_login_button_border_color' => '', 'lps_login_button_border_color_hover' => '', 'lps_login_nav_hover_color' => '', 'lps_login_nav_bgcolor' => '', 'lps_login_nav_bgcolor_hover' => '', 'lps_gfont_copyright' => '', 'lps_notice_bgcolor_opacity' => '', 'lps_body_bg_slideshow' => '', 'lps_slideshow_animation_style' => '', 'lps_slideshow_time' => '', 'lps_user_custom_css' => ''); }
function lps_free_customizer_mask_premium_options() {
    foreach (lps_free_customizer_pro_options() as $setting => $fallback) {
        add_filter('pre_option_'.$setting, static function($pre) use ($fallback) { return $fallback; }, 100);
    }
}
add_action('plugins_loaded','lps_free_customizer_mask_premium_options', 99);
function lps_free_customizer_admin_assets($hook) {
    if ($hook !== 'toplevel_page_lps_option') return;
    $base=dirname(__DIR__);
    wp_enqueue_style('lps-free-customizer-tiers',plugins_url('../css/lps-free-customizer-tiers.css',__FILE__),array(),filemtime($base.'/css/lps-free-customizer-tiers.css'));
    wp_enqueue_script('lps-free-customizer-tiers',plugins_url('../js/lps-free-customizer-tiers.js',__FILE__),array(),filemtime($base.'/js/lps-free-customizer-tiers.js'),true);
    wp_enqueue_script('lps-background-animation-preview',plugins_url('../js/lps-background-animation-preview.js',__FILE__),array(),filemtime($base.'/js/lps-background-animation-preview.js'),true);
}
add_action('admin_enqueue_scripts','lps_free_customizer_admin_assets',120);
