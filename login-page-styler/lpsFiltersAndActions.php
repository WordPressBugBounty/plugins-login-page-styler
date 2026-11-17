<?php

if (! defined('ABSPATH')) {
	exit;
}

// Visual hooks run for enabled frontend designs or the dedicated preview.
if (lps_design_should_render()) {
if (! empty(get_option('lps_login_background_color'))){
add_action('login_head', 'lps_login_background_color');
}

if (! empty(get_option('lps_login_label_color'))){
add_action('login_head', 'lps_login_label_color');
}

if (! empty(get_option('lps_login_nav_color'))){
add_action('login_head', 'lps_login_nav_color');
}

if (! empty(get_option('lps_login_nav_hover_color'))){
add_action('login_head', 'lps_login_nav_hover_color');
}

if (! empty(get_option('lps_login_nav_bgcolor'))){
add_action('login_head', 'lps_login_nav_bgcolor');
}

if (! empty(get_option('lps_login_nav_bgcolor_hover'))){
add_action('login_head', 'lps_login_nav_bgcolor_hover');
}

if (! empty(get_option('lps_login_form_border_radius'))){
add_action('login_head', 'lps_login_form_border_radius');
}

if (get_option('lps_login_nav_link_hide') == 1){
add_action('login_head', 'lps_login_nav_link_hide');
}

if (get_option('lps_login_logo_hide') == 1){
add_action('login_head', 'lps_login_logo_hide');
}

if (! empty(get_option('lps_login_form_position'))){
add_action('login_head', 'lps_login_form_position');
}

if (! empty(get_option('lps_login_form_color'))){
add_action('login_head', 'lps_login_form_color');
}

if (get_option('lps_login_logo_msg_hide') == 1){
add_action('login_head', 'lps_login_logo_msg_hide');
}

if (get_option('lps_login_blog_link_hide') == 1){
add_action('login_head', 'lps_login_blog_link_hide');
}

if (! empty(get_option('lps_login_label_size'))){
add_action('login_head', 'lps_login_label_size');
}

if (! empty(get_option('lps_login_form_input_feild_border_radius'))){
add_action('login_head', 'lps_login_form_input_feild_border_radius');
}

// add_action( 'login_head', 'lps_login_form_color_opacity' );.
if (! empty(get_option('lps_login_custom_css'))){
// Template preset and user CSS are now output by lps_unified_output().
}

if (! empty(get_option('lps_login_button_border_radius'))){
add_action('login_head', 'lps_login_button_border_radius');
}

if (! empty(get_option('lps_login_form_input_feild_border_color'))){
add_action('login_head', 'lps_login_form_input_feild_border_color');
}


if (! empty(get_option('lps_login_remember_label_size'))){
add_action('login_head', 'lps_login_remember_label_size');
}

if (! empty(get_option('lps_body_bg_img'))){
add_action('login_head', 'lps_body_bg_img');
}

if (! empty(get_option('lps_login_logo'))){
add_action('login_head', 'lps_login_logo');
}

// Notice colors are rendered by the unified frontend styling engine.

/*
if (get_option('lps_login_logo_width') > '80') {
	add_action('login_head', 'lps_login_logo_width');
}

if (get_option('lps_login_logo_height') > '80') {
	add_action('login_head', 'lps_login_logo_height');
}
*/

add_action('login_head', 'lps_login_button_color');

add_action('login_head', 'lps_login_button_color_hover');

// Alpha is rendered by lps_unified_surface_css(), including zero.

if (! empty(get_option('lps_login_form_input_text_color'))){
add_action('login_head', 'lps_login_form_input_text_color');
}

if (! empty(get_option('lps_login_form_border_style'))){
add_action('login_head', 'lps_login_form_border_style');
}

if (! empty(get_option('lps_login_form_input_border_style'))){
add_action('login_head', 'lps_login_form_input_border_style');
}

// Form image is rendered by lps_unified_surface_css().

if (! empty(get_option('lps_login_nav_size'))){
add_action('login_head', 'lps_login_nav_size');
}


if (! empty(get_option('lps_login_logo_link'))){
add_filter('login_headerurl', 'lps_login_logo_link');
}

if (! empty(get_option('lps_login_logo_tittle'))){
add_filter('login_headertext', 'lps_login_logo_tittle');
}





// add_filter( 'login_headertitle', 'lps_login_logo_tittle' );.

// Font settings are stored as lps_gfont* options (not lps_font*).
// One renderer handles all seven selectors on the saved login page.
add_action('login_head', 'lps_recovery_render_fonts', 100);

// Apply the same saved dimensions whether the logo is custom or WordPress default.
add_action('login_head', 'lps_recovery_render_logo_dimensions', 100);

if (! empty(get_option('lps_copyright_color'))){
add_action('login_head', 'lps_copyright_color');
}

if (! empty(get_option('lps_login_text_logo'))){
add_action('login_head', 'lps_login_text_logo');
}

if (get_option('lps_auto_rememberme') == '1') {
	add_action('login_form', 'lps_auto_remember_me');
}

if (trim((string)get_option('lps_login_copyright', '')) !== '') {
	add_action('login_footer', 'lps_copyright_notice');
}

// Hook into the login_head action to output the custom styles.
add_action('login_head', 'lps_boxshadow_styles');

// Hook the custom_login_page_animation function to the login_enqueue_scripts action.
add_action('login_enqueue_scripts', 'lps_login_page_animation');


if (get_option('lps_login_button_size') > 68) {
	add_action('login_head', 'lps_login_button_size');
}

// lang switcher.
if (get_option('lps_login_lang_hide') == 1) {
	add_filter('login_display_language_dropdown', '__return_false');
}


if (! empty(get_option('lps_login_form_width'))){
add_action('login_enqueue_scripts', 'lps_login_form_width');
}

} // End visual hooks.

// Independent behavior and administration hooks.
add_filter('plugin_action_links', 'lps_action_links', 10, 2);
// Private site and selected-page rules share one safe redirect handler.
// Free demonstration: frontend Pro hook omitted.

// Individual page protection is handled by lps_protection_check_request().

if (! empty(get_option('lps_redirectafter_users'))){
// Free demonstration: frontend Pro hook omitted.
}

if (! empty(get_option('lps_redirect_users'))){
// Free demonstration: frontend Pro hook omitted.
}

//Hook into the 'auth_cookie_expiration' filter.
if (get_option('lps_login_session_expire') > 0) {
	add_filter('auth_cookie_expiration', 'lps_user_session_timeout', 10, 3);
}

// menu.
if (get_option('lps_loginout_menu') == 1) {
	// Free demonstration: frontend Pro hook omitted.
}

