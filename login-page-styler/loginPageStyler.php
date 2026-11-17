<?php

/*
 * Plugin Name: Login Page Styler
 * Plugin URI: https://wordpress.org/plugins/login-page-styler/
 * Description: Customize your WordPress login page and preview advanced Pro design and security features.
 * Author: Zia Imtiaz
 * Version: 8.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author URI:https://pluginnestwp.website/custom-login-page-styler//
 * License: GPLv2
 * Text Domain: login-page-styler
 * Domain Path: /languages
 */



/**
 * Escape an inline CSS text context without converting selectors or quotes to HTML entities.
 * Values must be validated where they are assembled; this is a final defense against
 * prematurely terminating a style element. It intentionally preserves valid CSS.
 */
function lps_inline_css_text($css) {
    if (!is_string($css)) { return ''; }
    // CSS in a <style> element is a raw-text context. Never allow saved CSS to
    // terminate that element and inject arbitrary HTML. Keep ordinary CSS intact.
    $css = str_replace(array("\0", "<!--", "-->"), '', $css);
    $css = preg_replace('~</\s*style~i', '/* blocked style end */', $css);
    // Obsolete executable-CSS constructs are not needed by the Customizer.
    // This deliberately does not strip CSS variables, gradients or image URLs.
    $css = preg_replace('~expression\s*\(~i', 'blocked-expression(', $css);
    $css = preg_replace('~url\s*\(\s*([\"\']?)\s*javascript:~i', 'url($1about:blank', $css);
    return $css;
}


/**
 * Emit CSS safely in a style element. Only one static style element and its ID
 * are allowed: arbitrary HTML from saved CSS must never become page markup.
 * Preserve CSS text rather than applying esc_html() to CSS (which alters URLs).
 * Individual settings are validated by the style builders.
 */
function lps_print_inline_style($style_id, $css) {
    $allowed = array('style' => array('id' => true));
    // wp_kses normalizes raw ampersands; use CSS escapes so URLs keep their query strings.
    $safe_css = str_replace('&', '\\26 ', lps_inline_css_text($css));
    $markup = '<style id="' . esc_attr($style_id) . '">' . $safe_css . '</style>';
    echo wp_kses($markup, $allowed);
}

// Security check to prevent direct access.
if (!defined('ABSPATH')) { exit; }

/**
 * Free edition: premium designs may be selected in the protected admin preview,
 * but they are NEVER rendered on the public login page.
 *
 * Do not rewrite the stored admin preview selection: doing so would make
 * the premium demonstration disappear when the Customizer is refreshed.
 * All frontend rendering code reads get_option('lps_design_preset'), so this
 * filter provides a central fail-safe across template-specific login hooks.
 */
function lps_free_public_design_guard($saved_preset) {
    if ((defined('LPS_DESIGN_PREVIEW') && LPS_DESIGN_PREVIEW === true) || is_admin()) {
        return $saved_preset;
    }
    return in_array($saved_preset, array('default', 'modern-minimal'), true)
        ? $saved_preset : 'default';
}
add_filter('option_lps_design_preset', 'lps_free_public_design_guard', 999);
// Free: protected-page rules exist only as admin demonstrations.
require_once plugin_dir_path(__FILE__) . 'includes/lps-password-policy.php';

/** Only the dedicated preview request may render a disabled design. */
function lps_design_should_render() {
    return (int) get_option('lps_login_on_off', 0) === 1
        || (defined('LPS_DESIGN_PREVIEW') && LPS_DESIGN_PREVIEW === true);
}


/**
 * Cache-bust local plugin CSS/JS using each file's modification time.
 * This covers older enqueue calls without changing third-party or WordPress assets.
 * When an asset is edited in a future release, its URL changes automatically.
 */
function lps_version_local_asset($src) {
    $base_url = plugin_dir_url(__FILE__);
    $clean_url = strtok($src, '?');
    if (!is_string($clean_url) || strpos($clean_url, $base_url) !== 0) {
        return $src;
    }
    $relative = rawurldecode(substr($clean_url, strlen($base_url)));
    $root = realpath(plugin_dir_path(__FILE__));
    $file = realpath(plugin_dir_path(__FILE__) . $relative);
    if (!$root || !$file || strpos($file, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) {
        return $src;
    }
    return add_query_arg('ver', '8.0.0-' . substr(hash_file('sha256', $file), 0, 12), remove_query_arg('ver', $src));
}
add_filter('style_loader_src', 'lps_version_local_asset', 100);
add_filter('script_loader_src', 'lps_version_local_asset', 100);


/**
 * Summary of lps_login_background_color
 * function to change background color of login page
 */
function lps_login_background_color()
{

	if (! empty(get_option('lps_login_background_color'))) {
		echo '<style> body {  background-color: ' . esc_attr(get_option('lps_login_background_color')) . '; } </style>';
	}
}


/**
 * Summary of lps_login_label_color
 * function to change label color of login form
 */
function lps_login_label_color()
{

	echo '<style> .login label{ color: ' . esc_attr(get_option('lps_login_label_color')) . ';  } </style > ';
}


/**
 * Summary of lps_login_form_input_color_opacity
 * function to change  login form input fields opacity with color
 */

/*
Function lps_login_form_input_color_opacity() {

	echo ' < style > . login form . input{background: rgba( ' . esc_attr( get_option( 'lps_login_form_input_color_opacity' ) ) . ' ) ! important;} < / style > ';
}
*/
/**
 * Summary of lps_login_nav_size
 *
 * Nav links font size change
 */
function lps_login_nav_size()
{

	echo '<style> .login #nav,
.login #backtoblog{font-size:' . esc_attr(get_option('lps_login_nav_size')) . 'px ;}</style>';
}

/**
 * Summary of lps_login_nav_color
 * function to change color of login nav link
 */
function lps_login_nav_color()
{

	echo '<style> .login #backtoblog a, 
	.login #nav a{ color : ' . esc_attr(get_option('lps_login_nav_color')) . ';}</style>';
}

/**
 * Summary of lps_login_nav_hover_color
 * function to change color on hover over nav links
 */
function lps_login_nav_hover_color()
{

	echo '<style> .login #backtoblog a:hover, .login #nav a:hover{ color : ' . esc_attr(get_option('lps_login_nav_hover_color')) . ';}</style>';
}

function lps_login_nav_bgcolor()
{

	echo '<style> .login #backtoblog a, 
	.login #nav a{ background : ' . esc_attr(get_option('lps_login_nav_bgcolor')) . '; padding:5px;}</style>';
}

/**
 * Summary of lps_login_nav_bgcolor_hover
 * function to change color on hover over nav links
 */
function lps_login_nav_bgcolor_hover()
{

	echo '<style> .login #backtoblog a:hover, .login #nav a:hover{ background : ' . esc_attr(get_option('lps_login_nav_bgcolor_hover')) . ';}</style>';
}

/**
 * Summary of lps_login_form_border_radius
 * function to change border radius of login form
 */
function lps_login_form_border_radius()
{

	echo '<style> .login form{ border-radius:' . esc_attr(get_option('lps_login_form_border_radius')) . 'px !important;}</style>';
}

/**
 * Summary of lps_login_label_size
 * function to change size of  input label
 */
function lps_login_label_size()
{
	if (get_option('lps_login_label_size') !== '') {
		echo '<style> .login label[for="user_login"], .login label[for="user_pass"], .login label[for="user_email"]  { font-size:' . esc_attr(get_option('lps_login_label_size')) . 'px ;}</style>';
	}
}

/**
 * Summary of lps_login_remember_label_size
 * function to change the size of label for remember me checkbox
 */
function lps_login_remember_label_size()
{

	echo '<style>  .login label[for="rememberme"] {font-size:' . esc_attr(get_option('lps_login_remember_label_size')) . 'px  ;} </style>';
}


/**
 * Summary of lps_login_nav_link_hide
 * Function to hide nav link on login page
 */
function lps_login_nav_link_hide()
{

	if (get_option('lps_login_nav_link_hide') == 1) {

		echo '<style> .login #nav {display:none ;}</style>';
	} else {
		echo '<style> .login #nav {display:block ;}</style>';
	}
}


/**
 * Summary of lps_login_logo_hide
 * Function to hide login logo
 */
function lps_login_logo_hide()
{

	if (get_option('lps_login_logo_hide') === '1') {

		echo '<style> .login h1 a {display:none;}</style>';
	} else {
		echo '<style> .login h1 a {display:block;}</style>';
	}
}

/**
 * Summary of lps_login_form_position
 * Function to change position of login form
 */
function lps_login_form_position()
{
    // Position is handled by the shared layout stylesheet below.
    // Old percentage padding (70%) pushed forms outside the viewport.
}

/**
 * Summary of lps_login_form_color
 * function to add login form color
 */
function lps_login_form_color()
{
	// Get hex color value from options.
	$hex_color = get_option('lps_login_form_color');

	// Get opacity value from options or use default of 1.
	// Get opacity value from options or use default of 1.
	$opacity = get_option('lps_login_form_color_opacity');

	// Convert hex color to RGBA format with opacity.
	$rgba_color = lps_hex_to_rgba($hex_color, $opacity);

	echo '<style> .login form { background: ' . esc_attr($rgba_color) . ' ; margin:0 auto;}</style>';
}


/**
 * Summary of lps_login_form_input_color_opacity
 *
 * Adding input field opacity
 */
function lps_login_form_input_color_opacity()
{
	// Get hex color value from options.
	$hex_color = get_option('lps_login_form_input_color');

	// Get opacity value from options or use default of 1.
	$opacity = get_option('lps_login_form_input_color_opacity');

	// Convert hex color to RGBA format with default opacity of 1.
	$rgba_color = lps_hex_to_rgba($hex_color, $opacity);

	echo '<style> .login form .input { background: ' . esc_attr($rgba_color) . '; }</style>';
}


/**
 * Summary of lps_login_logo_msg_hide
 * function to hide login error msg
 */
function lps_login_logo_msg_hide()
{

	if (get_option('lps_login_logo_msg_hide') === 1) {
		echo '<style> #login_error,.login .message{display:none ;}</style>';
	} else {

		echo '<style> #login_error,.login .message{display:block ;}</style>';
	}
}

/**
 * Summary of lps_login_blog_link_hide
 * function to hide login back to blog link
 */
function lps_login_blog_link_hide()
{

	if (get_option('lps_login_blog_link_hide') == 1) {
		echo '<style> .login #backtoblog{ display:none ;}</style>';
	} else {
		echo '<style> .login #backtoblog{ display:block ;}</style>';
	}
}

/**
 * Summary of lps_login_form_input_feild_border_radius
 * adding border radius for input field
 */
function lps_login_form_input_feild_border_radius()
{

	echo '<style> .login form .input {border-radius:' . esc_attr(get_option('lps_login_form_input_feild_border_radius')) . 'px !important;}</style>';
}


/**
 * Summary of lps_copyright_color
 * adding color for login copyright set by user
 */
function lps_copyright_color()
{

	echo '<style> .login .copyright{color:' . esc_attr(get_option('lps_copyright_color')) . ';}</style>';
}

/**
 * Summary of lps_login_custom_css
 * adding custom css  set by user
 */
function lps_login_custom_css()
{

	echo '<style>' . esc_attr(get_option('lps_login_custom_css')) . '</style>';
}


/**
 * Summary of lps_login_button_border_radius
 * login button border radius set by user
 */
function lps_login_button_border_radius()
{

	echo '<style> .login .button-primary{ border-radius:' . esc_attr(get_option('lps_login_button_border_radius')) . 'px; } </style>';
}


/**
 * Summary of lps_login_form_input_feild_border_color
 * changing border color of input field set by user
 */
function lps_login_form_input_feild_border_color()
{

	echo '<style> .login form .input{border-color:' . esc_attr(get_option('lps_login_form_input_feild_border_color')) . '!important;}</style>';
}

/**
 * Summary of lps_login_logo_link
 * changing link for login logo set by user
 */
function lps_login_logo_link()
{

	return esc_url(get_option('lps_login_logo_link'));
}


/**
 * Summary of lps_login_logo_tittle
 * changing title for login logo
 */
function lps_login_logo_tittle()
{

	return esc_attr(get_option('lps_login_logo_tittle'));
}


/**
 * Summary of lps_body_bg_img
 * adding bg img to login page body
 */
function lps_body_bg_img()
{

	// The unified style engine owns background sizing, position and repeat.
	// Do not emit the old fixed 100% 100% rule: it stretched photographs.
	return;
}


/**
 * Summary of lps_login_logo
 * adding backgroung image to for login logo with height and width
 */
function lps_login_logo()
{
	if (get_option('lps_login_logo') !== '') {
		echo '<style> .login h1 a{margin:0 auto; background-size:' . esc_attr(get_option('lps_login_logo_width')) . 'px ,' . esc_attr(get_option('lps_login_logo_height')) . 'px; background-image:url(' . esc_url(get_option('lps_login_logo')) . ') ;  width:' . esc_attr(get_option('lps_login_logo_width')) . 'px;height:' . esc_attr(get_option('lps_login_logo_height')) . 'px;} </style>';
	}
}

/**
 * Summary of lps_login_logo_width
 * changing logo width
 */
function lps_login_logo_width()
{

	if (get_option('lps_login_logo_width') !== '') {
		echo '<style> .login h1 a{ width:' . esc_attr(get_option('lps_login_logo_width')) . 'px;}</style>';
	}
}

/**
 * Summary of lps_login_logo_height
 * changing login logo height
 */
function lps_login_logo_height()
{
	if (get_option('lps_login_logo_height') !== '') {
		echo '<style> .login h1 a{ height:' . esc_attr(get_option('lps_login_logo_height')) . 'px;}</style>';
	}
}


/**
 * Summary of lps_login_button_color
 * Changing login button color
 */
function lps_login_button_color()
{

	echo '<style> .login .button-primary{background-color:' . esc_attr(get_option('lps_login_button_color')) . ';

    border-color:' . esc_attr(get_option('lps_login_button_border_color')) . '; border:1px solid ' . esc_attr(get_option('lps_login_button_border_color')) . ';

    color:' . esc_attr(get_option('lps_login_button_text_color')) . ';
 

    }</style>';
}


/**
 * Summary of lps_login_form_input_text_color
 * Changing login form input text color 
 */
function lps_login_form_input_text_color()
{

	echo '<style> .login form .input{color:' . esc_attr(get_option('lps_login_form_input_text_color')) . ';

    }</style>';
}




/**
 * Summary of lps_login_button_color_hover
 * Changing login button hover color
 */
function lps_login_button_color_hover()
{

	echo '<style> .login .button-primary:hover {background-color:' . esc_attr(get_option('lps_login_button_color_hover')) . ';

    border-color:' . esc_attr(get_option('lps_login_button_border_color_hover')) . '; border:1px solid ' . esc_attr(get_option('lps_login_button_border_color_hover')) . ';

    color:' . esc_attr(get_option('lps_login_button_text_color_hover')) . ';

    }</style>';
}


/**
 * Summary of lps_login_form_border_style
 * Changing login form border color size and style
 */
function lps_login_form_border_style()
{

	echo '<style> .login form{border-style:' . esc_attr(get_option('lps_login_form_border_style')) . ';
     border-width:' . esc_attr(get_option('lps_login_form_border_size')) . 'px ;
     border-color:' . esc_attr(get_option('lps_login_form_border_color')) . ' ;}</style>';
}


/**
 * Summary of lps_login_form_input_border_style
 *
 * Changing border style of input field
 */
function lps_login_form_input_border_style()
{

	echo '<style> .login form .input{border-style:' . esc_attr(get_option('lps_login_form_input_border_style')) . ';
	 border-width:' . esc_attr(get_option('lps_login_form_input_border_size')) . 'px ;}</style>';
}

/**
 * Summary of lps_login_form_bg
 *
 * Adding Login form background image
 */
function lps_login_form_bg()
{

	echo '<style> .login form {background-image:url(' . esc_attr(get_option('lps_login_form_bg')) . '); background-size:cover; display:block !important;}</style>';
}

/**
 * Summary of form width
 *
 * Adding form width
 */
function lps_login_form_width()
{
	// Get the value from the option, or use default value.
	$login_form_width = get_option('lps_login_form_width');

	// Output the custom styles if the value is greater than 0.
	if ($login_form_width > 320) {
		echo '<style> #login { width: ' . esc_attr($login_form_width) . 'px!important; }

</style>';
	}
}


/**
 * Summary of lps_font_textlogo
 *
 * Adding fonts to login text logo
 */
function lps_font_textlogo()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfonttext_logo')));
	echo '<style> 

@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );

body.login h1 a {
font-family:' . esc_attr(get_option('lps_gfonttext_logo')) . ';


	</style>';
}

/**
 * Summary of lps_font_copyright
 *
 * Adding fonts to copyright
 */
function lps_font_copyright()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfont_copyright')));
	echo '<style> 

@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );

.login .copyright {
font-family:' . esc_attr(get_option('lps_gfont_copyright')) . ';


	</style>';
}


/**
 * Summary of lps_login_form_input_text_color
 *
 * Adding fonts to login form input text
 */
function lps_font_inputtext()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfont_inputtext')));
	echo '<style> 

@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );

.login form .input{
font-family:' . esc_attr(get_option('lps_gfont_inputtext')) . ';


	</style>';
}

/**
 * Summary of lps_font_label
 *
 * Adding fonts to login form labels
 */
function lps_font_label()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfontlab')));
	echo '<style> 

@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );

.login label {
font-family:' . esc_attr(get_option('lps_gfontlab')) . '!important;


	</style>';
}

/**
 * Summary of lps_font_btn
 *
 * Adding fonts to login button
 */
function lps_font_btn()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfontbtn')));

	echo '<style> 
	@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );
.login .button-primary {
	font-family:' . esc_attr(get_option('lps_gfontbtn')) . ';
}
</style>';
}

/**
 * Summary of lps_font_link
 *
 * Adding fonts to login page nav links
 */
function lps_font_link()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfontlink')));

	echo '<style> 
	@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );
	.login #nav,
	.login #backtoblog{
	font-family:' . esc_attr(get_option('lps_gfontlink')) . '!important;
}
</style>';
}

/**
 * Summary of lps_font_msg
 *
 * Adding fonts to login message , error
 */
function lps_font_msg()
{
	$font = str_replace(' ', '+', esc_attr(get_option('lps_gfontmsg')));

	echo '<style> 
	@import url(https://fonts.googleapis.com/css?family=' . esc_attr($font) . ' );
	.login #login_error, 
	.login .message, 
	.login .success{
	font-family:' . esc_attr(get_option('lps_gfontmsg')) . '!important;
}
</style>';
}


/**
 * Summary of lps_redirect_to_login
 *
 * Check if no user logged in and private site option is selected redirect to selected page
 */
function lps_redirect_to_login()
{
	/*
	Commented code
	if ( ! is_user_logged_in() && get_option( 'lps_enable_private_site' ) == 1 ) {
		wp_redirect( home_url( '/wp-login.php/?redirect_to =' . $_SERVER['REQUEST_URI'] ) );
		exit;
	}
	*/

	if (! is_user_logged_in() && get_option('lps_enable_private_site') == 1) {
		$request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
		if (! empty($request_uri)) {
			$redirect_url = home_url('/wp-login.php/?redirect_to=' . rawurlencode($request_uri));
			wp_safe_redirect(esc_url($redirect_url));
			exit;
		}
	}
}

// Array of option keys to check.
$option_keys = array(
	'lps_private_login_url',
	'lps_private_login_url2',
	'lps_private_login_url3',
	'lps_private_login_url4',
	'lps_private_login_url5',
);

// Check if any of the option values is not empty.
$should_redirect = false;
foreach ($option_keys as $option_key) {
	$option_value = get_option($option_key);
	if (! empty($option_value)) {
		$should_redirect = true;
		break; // Exit the loop if any option value is not empty.
	}
}


/**
 * Summary of lps_redirectpage_to_login
 *
 * Check if no user is logged in and any of the login URL options are set, then redirect to the selected page
 */
function lps_redirectpage_to_login()
{
	if (! is_user_logged_in()) {
		$login_urls = array(
			'lps_private_login_url',
			'lps_private_login_url2',
			'lps_private_login_url3',
			'lps_private_login_url4',
			'lps_private_login_url5',
		);

		foreach ($login_urls as $option_key) {
			$option_value = get_option($option_key);

			if (! empty($option_value) && is_page($option_value)) {
				$request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

				if (! empty($request_uri)) {
					$redirect_url = home_url('/wp-login.php/?redirect_to=' . rawurlencode($request_uri));
					wp_safe_redirect(esc_url($redirect_url));
					exit;
				}
			}
		}
	}
}


/**
 * Custom function to process items with additional arguments.
 *
 * @param array $items An array of items to process.
 * @param array $args  Additional arguments for processing.
 * @return array       Processed items.
 */
function lps_login_logout_link($items, $args)
{
    // Respect the administrator's menu location choice. The legacy default is primary.
    $location = get_option('lps_menu_location', 'primary');
    if ('all' !== $location && (!isset($args->theme_location) || $args->theme_location !== $location)) {
        return $items;
    }

    $logged_in = is_user_logged_in();
    $option = $logged_in ? 'lps_logout_menu_label' : 'lps_login_menu_label';
    $fallback = $logged_in ? __('Log Out', 'login-page-styler') : __('Log In', 'login-page-styler');
    $label = trim((string) get_option($option, ''));
    $label = $label !== '' ? $label : $fallback;
    $url = $logged_in ? wp_logout_url(home_url('/')) : wp_login_url();

    return $items . '<li class="menu-item menu-item-type-custom lps-auth-menu-item"><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
}

/**
 * Summary of lps_login_redirect_user
 *
 * @param mixed $redirect_to The original redirect URL.
 * @param mixed $request     The login request.
 * @param mixed $user        The user object.
 * @return mixed             The modified redirect URL.
 */
function lps_login_redirect_user($redirect_to, $request, $user)
{
	// is there a user to check?
	if (isset($user->roles) && is_array($user->roles)) {
		// check for admins.
		if (in_array('administrator', $user->roles, true)) {
			// redirect them to the default place.
			return admin_url();
		} else {
			$page = get_page_by_path(get_option('lps_redirect_users', ''));
            return $page ? get_permalink($page) : $redirect_to;
		}
	} else {
		return $redirect_to;
	}
}

/**
 * Summary of lps_logout_redirect_user  on logout
 */
function lps_logout_redirect_destination($redirect_to, $requested_redirect_to, $user)
{
    $slug = get_option('lps_redirectafter_users', '');
    if ($slug === '') {
        return $redirect_to;
    }
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : $redirect_to;
}



/**
 * Summary of lps_action_links
 *
 * @param array  $links An array of existing plugin action links.
 * @param string $file Path to the plugin file.
 * @return array Modified array of plugin action links.
 */
function lps_action_links($links, $file)
{
	if (plugin_basename(dirname(__FILE__) . '/loginPageStyler.php') == $file) {
		$links[] = '<a href="' . get_bloginfo('url') . '/wp-admin/admin.php?page=lps_option">Settings</a>';
	}
	return $links;
}

// Free: no premium lockout tables are created during activation.

/**
 * Summary of lps_login_text_logo
 *
 * Adding text logo for login page
 */

function lps_login_text_logo()
{
	$text_logo            = get_option('lps_login_text_logo');
	$text_logocolor       = get_option('lps_textlogo_color');
	$text_logocolor_hover = get_option('lps_textlogo_color_hover');

	if (! empty($text_logo)) {
		// Output custom CSS to display the text logo and hide the default WordPress logo.
		echo '<style type="text/css">
            .login h1 a {
                background-image: none !important;
                text-indent: 0 !important;
                display: block;
                height: auto;
				width:auto;
                font-size: 60px;
                font-weight: bold;
                color: ' . esc_attr($text_logocolor) . ';
                text-decoration: none;
                font-family: Arial, sans-serif;
            }
				.login h1 a:hover {
            color: ' . esc_attr($text_logocolor_hover) . '!important; /* Hover color */
        }
        </style>';
		echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var loginLogo = document.querySelector(".login h1 a");
                if (loginLogo) {
                    loginLogo.textContent = "' . esc_html($text_logo) . '";
                }
            });
        </script>';
	}
}


/**
 * Summary of lps_auto_remember_me
 *
 * Adding auto auto remember
 */
function lps_auto_remember_me()
{
	if (! is_user_logged_in() && get_option('lps_auto_rememberme') == '1') {
?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var rememberMe = document.getElementById('rememberme');
				if (rememberMe) {
					rememberMe.checked = true;
				}
			});
		</script>
<?php
	}
}


/**
 * Summary of lps_copyright_notice
 *
 * Adding copyright notice  to loin page
 */
function lps_copyright_notice()
{
    $company_name = trim((string) get_option('lps_login_copyright', ''));
    if ($company_name === '') {
        return;
    }

    // WordPress runs login_footer after closing #login. Move only our footer
    // into that container so it always follows the navigation links.
    echo '<div class="copyright" id="lps-login-copyright">&copy; ' . esc_html(gmdate('Y')) . ' ' . esc_html($company_name) . '. All rights reserved.</div>';
    echo '<script id="lps-copyright-place">(function(){'
       . 'function place(){var footer=document.getElementById("lps-login-copyright");'
       . 'var login=document.getElementById("login");'
       . 'if(footer&&login&&footer.parentNode!==login){login.appendChild(footer);}}'
       . 'place();if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",place);'
       . '})();</script>';
}

/**
 * Summary of lps_copyright_textalign
 *
 * Adding copyright notice  text align
 */
function lps_copyright_textalign()
{
    // The shared layout controller places the footer inside #login, after the links.
    // It is not a floating element of the body, which split-screen templates can
    // otherwise position beside the login form.
    echo '<style id="lps-copyright-layout">
    body.login #login > #lps-login-copyright,
    body.login #login > .copyright {
        display: block !important;
        position: relative !important;
        inset: auto !important;
        transform: none !important;
        float: none !important;
        clear: both !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        margin: 20px auto 0 !important;
        padding: 0 8px !important;
        text-align: center !important;
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
        z-index: 10;
    }
    </style>';
}
if (lps_design_should_render()) add_action('login_head', 'lps_copyright_textalign');


/**

 * Adjusts the user session timeout based on the settings.
 *
 * @param int $expirein The default session timeout value in seconds.
 *
 * @return int The adjusted session timeout value in seconds.
 */


function lps_user_session_timeout($expirein)
{
	$session_timeout = get_option('lps_login_session_expire'); // Get the lps session timeout from your settings.

	if (! empty($session_timeout) && is_numeric($session_timeout) && $session_timeout > 0) {
		return $session_timeout * 60; // Convert minutes to seconds.
	}

	return $expirein; // Use default if  session timeout is not set or invalid.
}

/**
 * Summary of lps_boxshadow_style
 *
 * Adding box shadow style to form
 */
function lps_boxshadow_styles()
{
	// Get box shadow properties from options.
	$box_shadow_horizontal = get_option('lps_box_shadow_horizontal', '0');
	$box_shadow_vertical   = get_option('lps_box_shadow_vertical', '0');
	$box_shadow_blur       = get_option('lps_box_shadow_blur', '10');  // Original blur value.
	$box_shadow_spread     = get_option('lps_box_shadow_spread', '0');   // Corrected spread value.
	$box_shadow_color_hex  = get_option('lps_box_shadow_color', ' 000000');
	$box_shadow_opacity    = get_option('lps_box_shadow_opacity', '1');

	// Convert hex color to rgba.
	$box_shadow_color_rgba = lps_hex_to_rgba($box_shadow_color_hex, $box_shadow_opacity);

	echo '<style type="text/css">';

	// Set box shadow style.
	echo '.login form {';
	echo '  box-shadow: ' . esc_attr($box_shadow_horizontal) . 'px ' . esc_attr($box_shadow_vertical) . 'px ' . esc_attr($box_shadow_blur) . 'px ' . esc_attr($box_shadow_spread) . 'px ' . esc_attr($box_shadow_color_rgba) . ';';
	echo '}';

	echo '</style>';
}


/**
 * Convert a hex color code to RGBA format.
 *
 * @param string $color   The hex color code to convert.
 * @param float  $opacity The opacity value, a float between 0 and 1.
 *
 * @return string|false The RGBA color code on success, or false on failure.
 */
/**
 * Convert a hex color code to RGBA format.
 *
 * @param string $color   The hex color code to convert.
 * @param float  $opacity The opacity value, a float between 0 and 1.
 *
 * @return string|false The RGBA color code on success, or false on failure.
 */
function lps_hex_to_rgba($color, $opacity = 1)
{
	// Remove the hash at the start if it's there
	$color = ltrim($color, '#');

	// Check if the input is valid
	if (!preg_match('/^[0-9a-fA-F]{3,6}$/', $color)) {
		return false;
	}

	// Expand shorthand hex to full form (e.g. #abc -> #aabbcc)
	if (strlen($color) == 3) {
		$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
	}

	// Split into RGB components
	list($r, $g, $b) = str_split($color, 2);
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);

	// Ensure opacity is between 0 and 1
	$opacity = max(0, min(1, $opacity));

	return "rgba($r, $g, $b, $opacity)";
}



/**
 * Enqueue styles and add custom animation to the login form based on the selected animation option.
 */
function lps_login_page_animation()
{
	// Get the selected animation option from the WordPress options.
	$selected_animation = get_option('lps_login_animation', 'fadeIn');

	// Enqueue the necessary stylesheets based on the selected animation.
	wp_enqueue_style('lps-local-form-animations', plugins_url('css/lps-local-form-animations.css', __FILE__), array(), (string) filemtime(plugin_dir_path(__FILE__) . 'css/lps-local-form-animations.css'));

	// Add a custom style to apply the selected animation to the login form.
	echo '<style>';
	echo '#login {';
	echo '    animation-name: ' . esc_attr($selected_animation) . ';';
	echo '    animation-duration: 1.2s;';
	// Do not retain animation transforms: they can interfere with Nature Pro backdrop blur.
	echo '    animation-fill-mode: none;';
	echo '}';
	echo '</style>';
}

/**
 * Enqueue styles  for changing login button width.
 */
function lps_login_button_size()
{
	$button_width = get_option('lps_login_button_size');

	echo '<style> .login .button-primary{margin-top:2px; width:' . esc_attr($button_width) . 'px; } </style> ';
}

/**
 * Summary of lps_login_form_color
 * function to add login form color
 */
function lps_notice_color()
{
    // Only output rules for colors the administrator actually selected.
    // A blank setting must not overwrite the template's readable defaults.
    $background = sanitize_hex_color(get_option('lps_notice_bgcolor'));
    $text = sanitize_hex_color(get_option('lps_notice_textcolor'));
    // Match both legacy and current WordPress notice markup.
    $selectors = 'body.login #login #login_error, body.login #login .message, body.login #login .notice, body.login #login .success';
    $rules = array();

    if ($background) {
        $opacity = get_option('lps_notice_bgcolor_opacity', '1');
        $opacity = is_numeric($opacity) ? max(0, min(1, (float) $opacity)) : 1;
        $rgba = lps_hex_to_rgba($background, $opacity);
        $rules[] = 'background-color:' . esc_attr($rgba) . ' !important';
    }
    if ($text) {
        $rules[] = 'color:' . esc_attr($text) . ' !important';
    }
    if ($rules) {
        echo '<style id="lps-notice-custom-colors">' . esc_html($selectors) . '{' . esc_html(implode(';', $rules)) . ';}</style>';
    }
}

require_once __DIR__ . '/includes/lps-free-five-tabs-ui.php';
require_once __DIR__ . '/includes/lps-free-customizer-tier.php';
require 'loginPageStylerOption.php';

require_once __DIR__ . '/includes/lps-free-security-ui.php';
require 'loginPageStylerTempSetting.php';
// Free demo only: temporary access authentication and AJAX handlers are not bundled.

require 'loginPageStylerLogSettings.php';
// Free demo only: login event recording, alert emails and cleanup are not bundled.

require 'lpsBlacklistSettings.php';
// Free demo only: IP and country login-blocking hooks are not bundled.

// Free: login URL rewriting / redirect handlers omitted.

// Free: failed-attempt tracking and login lockout handlers omitted.

// Free edition: slideshow is a Pro-only effect. Do not load frontend Swiper scripts or handlers.
// Its settings remain visible for an admin-only demonstration.


// Security and login behavior have their own settings, independent of design.
require 'lpsReCaptcha.php';
require 'lpsFiltersAndActions.php';
require_once __DIR__ . '/includes/lps-social.php';

// Free: Login/Logout Menu Item shortcode and frontend widget are Pro-only.

// Register activation and deactivation hooks
// Free preview: no premium database tables or background jobs.
// Free preview: no premium database tables or background jobs.
// Free preview: no premium database tables or background jobs.
// Free preview: no premium database tables or background jobs.

function lps_custom_login_notice($message)
{
	// Check if it's the login page (and not any other action like lost password or register)
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only login notice routing; the login action is never processed here.
	if (isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), 'wp-login.php') !== false && !isset($_REQUEST['action'])) {
		// Append your custom notice before any existing messages
		$custom_notice = '<div class="notice notice-info is-dismissible"><p>' . esc_html__('This is your custom notice message with default styling.', 'login-page-styler') . '</p></div>';
		return $custom_notice . $message;
	}
	// Return any existing messages without modification
	return $message;
}
//add_filter('login_message', 'lps_custom_login_notice');

if(get_option('lps_show_math_captcha') == '1') {
add_action('login_form', 'lps_add_math_captcha');
add_filter('authenticate', 'lps_verify_math_captcha', 30, 3);
}
function lps_add_math_captcha() {
    // Generate random numbers for the CAPTCHA
    $num1 = wp_rand(1, 9);
    $num2 = wp_rand(1, 9);

    // Create a unique nonce for the CAPTCHA
    $captcha_nonce = wp_create_nonce('lps_captcha_nonce');
    $transient_key = 'lps_captcha_' . $captcha_nonce;

    // Store the result as an integer in the transient
    set_transient($transient_key, (int)($num1 + $num2), 30 * MINUTE_IN_SECONDS);

    // Output the math question and hidden nonce field in the form
    echo '<p>
            <label for="math_captcha">Please solve: ' . esc_html($num1) . ' + ' . esc_html($num2) . ' = ?</label>
            <input type="text" name="math_captcha" id="math_captcha" class="input" value="" size="25" />
          </p>';
    echo '<input type="hidden" name="lps_captcha_nonce" value="' . esc_attr($captcha_nonce) . '" />';
}


//add_filter('authenticate', 'lps_verify_math_captcha', 30, 3);

function lps_verify_math_captcha($user, $username, $password) {
    if (isset($_POST['math_captcha'], $_POST['lps_captcha_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lps_captcha_nonce'])), 'lps_captcha_nonce')) {
        $captcha_nonce = sanitize_text_field(wp_unslash($_POST['lps_captcha_nonce']));
        $transient_key = 'lps_captcha_' . $captcha_nonce;

        // Retrieve the expected CAPTCHA result from the transient
        $expected_result = get_transient($transient_key);
        $user_input = isset($_POST['math_captcha']) ? absint(wp_unslash($_POST['math_captcha'])) : -1;

        // Never log CAPTCHA answers or submitted credentials.

        // Check if the expected result exists and validate it
        if ($expected_result === false) {
            return new WP_Error('captcha_error', __('<strong>ERROR</strong>: CAPTCHA has expired. Please try again.', 'login-page-styler'));
        }

        // Strictly compare the integer values
        if ($expected_result !== $user_input) {
            return new WP_Error('captcha_error', __('<strong>ERROR</strong>: Incorrect math CAPTCHA.', 'login-page-styler'));
        }

        // If correct, delete the transient to avoid reuse
        delete_transient($transient_key);
    } else {
        return new WP_Error('captcha_error', __('<strong>ERROR</strong>: CAPTCHA is required.', 'login-page-styler'));
    }

    return $user;
}


// The unified styling engine owns the final CSS cascade.

/**
 * Saved login-page logo dimensions. Separate from logo-image selection so
 * changing width/height works for both the default WP mark and custom images.
 */
function lps_recovery_render_logo_dimensions() {
    $width = absint(get_option('lps_login_logo_width'));
    $height = absint(get_option('lps_login_logo_height'));
    if (!$width && !$height) { return; }
    echo '<style id="lps-recovery-logo-dimensions">.login h1 a {';
    if ($width) { echo 'width:' . esc_attr($width) . 'px !important;'; }
    if ($height) { echo 'height:' . esc_attr($height) . 'px !important;'; }
    echo 'background-size:contain !important;background-position:center center !important;background-repeat:no-repeat !important;}</style>';
}

/**
 * Apply saved Google Fonts to the actual login page.
 * The legacy conditionals checked non-existent lps_font* option keys, so
 * changes were visible in JS preview but disappeared after Save.
 */
function lps_recovery_render_fonts() {
    $targets = array(
        'lps_gfonttext_logo' => '.login h1 a',
        'lps_gfont_copyright' => '.login .copyright',
        'lps_gfont_inputtext' => '.login form .input',
        'lps_gfontlab' => '.login label',
        'lps_gfontbtn' => '.login .button-primary',
        'lps_gfontlink' => '.login #nav, .login #backtoblog',
        'lps_gfontmsg' => '.login #login_error, .login .message, .login .success',
    );
    $rules = array();
    $families = array();
    foreach ($targets as $option => $selector) {
        $font = trim((string) get_option($option, ''));
        if ($font === '' || strtolower($font) === 'select a font') { continue; }
        // Fontselect sometimes stores the font family followed by a variant.
        $family = preg_replace('/:[^:]*$/', '', $font);
        $family = trim($family, " \t\n\r\0\x0B\"'");
        if (!preg_match('/^[\p{L}\p{N} ._-]+$/u', $family)) { continue; }
        $families[$family] = true;
        $rules[] = $selector . '{font-family:"' . esc_attr($family) . '",sans-serif !important;}';
    }
    if (!$rules) { return; }
    // CSS imports must appear before style rules.
    $imports = '';
    foreach (array_keys($families) as $family) {
        $imports .= '@import url("https://fonts.googleapis.com/css?family=' . rawurlencode($family) . '&display=swap");';
    }
    lps_print_inline_style('lps-recovery-google-fonts', $imports . implode('', $rules));
}
