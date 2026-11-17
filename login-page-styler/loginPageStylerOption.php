<?php

/** Free UI helper: demonstrate the page picker without enabling any page protection. */
function lps_protection_page_ids() { return array(); }
function lps_free_demo_get_option($key, $default = '') { return $default; }

function lps_pro_upgrade_url() {
    return admin_url('admin.php?page=lps_upgrade_pro');
}

/** Render compact, contextual Pro notices within the original premium tab layouts. */
function lps_free_tab_notice($title) {
    echo '<div class="lps-free-tab-notice" role="note"><div><strong>' . esc_html($title) . ' is available in Pro</strong>' .
         '<p>Explore these controls as a demonstration. Nothing here is saved or applied to your website.</p></div>' .
         '<a class="button button-primary" href="' . esc_url(lps_pro_upgrade_url()) . '">Upgrade to Pro</a></div>';
}

/** Keep custom login paths to one safe, predictable URL segment. */
function lps_sanitize_login_slug($value) {
    $slug = sanitize_title(trim((string) $value, " /\\"));
    $reserved = array('wp-admin', 'wp-login.php', 'wp-json', 'wp-content', 'wp-includes', 'admin', '404');
    if ($slug !== '' && in_array($slug, $reserved, true)) {
        add_settings_error('lps_new_login_url', 'lps_reserved_slug', __('That login slug is reserved. The previous value was kept.', 'login-page-styler'));
        return get_option('lps_new_login_url', '');
    }
    return $slug;
}

/** The old-login destination accepts a page slug or the special 404 value. */
function lps_sanitize_redirect_slug($value) {
    $value = trim((string) $value);
    if ($value === '' || $value === '404') return $value;
    return sanitize_title(trim($value, " /\\"));
}


/** Keep the v3 score threshold within Google's supported range. */
function lps_recaptcha_sanitize_threshold($value) {
    return max(0.1, min(1.0, round((float) $value, 1)));
}

// Security check to prevent direct access.
if (! defined('ABSPATH')) {
	exit;
}

function lps_menu() {
    add_menu_page(
        'Login Page Styler',             // Page title
        'Login Page Styler',             // Menu title
        'manage_options',                 // Capability
        'lps_option',                     // Menu slug
        'lps_settings_page',              // Callback function
        '',                               // Icon (can leave empty)
        20                                 // Position
    );

    // Add submenu for temporary access
    add_submenu_page(
        'lps_option',                     // Parent slug
        'Temporary Access',               // Page title
        'Temporary Access',               // Menu title
        'manage_options',                 // Capability
        'lps_temp_access',                // Menu slug
        'lps_temp_access_page_callback'   // Callback function
    );
	
	// Add submenu for blocking IP and regions
    add_submenu_page(
        'lps_option',                     // Parent slug
        'Block IP & Regions',             // Page title
        'Block IP & Regions',             // Menu title
        'manage_options',                 // Capability
        'lps_block_ip_regions',           // Menu slug
        'lps_display_ip_blocking'       // Callback function to handle blocking IPs and regions
    );

    // Add submenu for user login logs
    add_submenu_page(
        'lps_option',                     // Parent slug
        'User Login Logs',                // Page title
        'User Login Logs',                // Menu title
        'manage_options',                 // Capability
        'lps_user_login_logs',            // Menu slug
        'lps_display_login_logs'          // Callback function to display the logs
    );

}
add_action('admin_menu', 'lps_menu');

/** Register last so Upgrade to Pro appears below Social Login (priority 20). */
function lps_register_upgrade_pro_menu() {
    add_submenu_page(
        'lps_option',
        __('Upgrade to Pro', 'login-page-styler'),
        __('Upgrade to Pro', 'login-page-styler'),
        'manage_options',
        'lps_upgrade_pro',
        'lps_upgrade_pro_page_callback'
    );
}
add_action('admin_menu', 'lps_register_upgrade_pro_menu', 99);

/** Make only this submenu label green without changing the other WordPress menus. */
function lps_upgrade_pro_submenu_color() {
    if (!current_user_can('manage_options')) return;
    echo '<style id="lps-upgrade-pro-menu-label">'
        . '#adminmenu .wp-submenu a[href*="page=lps_upgrade_pro"]{color:#51df9a!important;font-weight:700!important}'
        . '#adminmenu .wp-submenu a[href*="page=lps_upgrade_pro"]:hover,'
        . '#adminmenu .wp-submenu a[href*="page=lps_upgrade_pro"]:focus{' 
        . 'color:#9bf5c5!important}'
        . '</style>';
}
add_action('admin_head', 'lps_upgrade_pro_submenu_color');

// Register options on every admin request, including wp-admin/options.php.
// Registering from lps_menu() misses save requests and can discard changes.
add_action('admin_init', 'lps_register_settings');

/**
 * General Settings use the same existing option names as before.
 * Keeping these names preserves saved values and their existing login hooks.
 */
function lps_register_general_settings() {
    register_setting('lps-general-settings-group', 'lps_auto_rememberme', 'absint');
    register_setting('lps-general-settings-group', 'lps_login_session_expire', 'absint');
    register_setting('lps-general-settings-group', 'lps_login_identifier_policy', array('sanitize_callback' => 'lps_general_sanitize_login_policy', 'default' => 'both'));
    register_setting('lps-general-settings-group', 'lps_registration_allowed_domains', array('sanitize_callback' => 'lps_general_sanitize_domains', 'default' => ''));
    register_setting('lps-general-settings-group', 'lps_registration_blocked_domains', array('sanitize_callback' => 'lps_general_sanitize_domains', 'default' => ''));
    register_setting('lps-general-settings-group', 'lps_delete_data_on_uninstall', 'absint');
    // New account policies are disabled unless an administrator explicitly enables them.
    register_setting('lps-general-settings-group', 'lps_password_expiry_enabled', 'absint');
    register_setting('lps-general-settings-group', 'lps_password_expiry_days', array('sanitize_callback' => 'lps_policy_sanitize_days', 'default' => 90));
    register_setting('lps-general-settings-group', 'lps_password_expiry_roles', array('sanitize_callback' => 'lps_policy_sanitize_roles', 'default' => array()));
    register_setting('lps-general-settings-group', 'lps_password_strength_enforce', 'absint');
    register_setting('lps-general-settings-group', 'lps_password_min_length', array('sanitize_callback' => 'lps_policy_sanitize_length', 'default' => 12));
    register_setting('lps-general-settings-group', 'lps_password_require_upper', 'absint');
    register_setting('lps-general-settings-group', 'lps_password_require_lower', 'absint');
    register_setting('lps-general-settings-group', 'lps_password_require_number', 'absint');
    register_setting('lps-general-settings-group', 'lps_password_require_symbol', 'absint');
}
add_action('admin_init', 'lps_register_general_settings');

/** Validate allowed login identifiers while retaining a safe fallback. */
function lps_general_sanitize_login_policy($value) {
    $value = sanitize_key($value);
    return in_array($value, array('both', 'username', 'email'), true) ? $value : 'both';
}

/** Only exact domain names; excludes wildcards, URLs, and partial suffix matches. */
function lps_general_sanitize_domains($value) {
    $parts = preg_split('/[\s,;]+/', strtolower((string) $value));
    $valid = array();
    foreach ($parts as $part) {
        $part = trim($part, " .\t\r\n");
        if ($part !== '' && strlen($part) <= 253 && preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/D', $part)) {
            $valid[$part] = true;
        }
    }
    return implode("\n", array_keys($valid));
}

/** Do not replace an existing authentication failure or affect OAuth callbacks. */
function lps_general_restrict_login_identifier($user, $username, $password) {
    if (is_wp_error($user) || !($user instanceof WP_User) || trim((string) $username) === '') {
        return $user;
    }
    $policy = get_option('lps_login_identifier_policy', 'both');
    if ($policy === 'username' && is_email($username)) {
        return new WP_Error('lps_identifier_policy', __('This site requires a username to sign in.', 'login-page-styler'));
    }
    if ($policy === 'email' && !is_email($username)) {
        return new WP_Error('lps_identifier_policy', __('This site requires an email address to sign in.', 'login-page-styler'));
    }
    return $user;
}
add_filter('authenticate', 'lps_general_restrict_login_identifier', 50, 3);

/** Validate the built-in WordPress registration email (not other plugins' custom forms). */
function lps_general_validate_registration_domain($errors, $sanitized_user_login, $user_email) {
    if (is_wp_error($errors) && $errors->has_errors()) return $errors;
    $email = strtolower(trim((string) $user_email));
    if (!is_email($email)) return $errors;
    $domain = substr(strrchr($email, '@'), 1);
    $allowed = array_filter(explode("\n", (string) get_option('lps_registration_allowed_domains', '')));
    $blocked = array_filter(explode("\n", (string) get_option('lps_registration_blocked_domains', '')));
    if (($allowed && !in_array($domain, $allowed, true)) || in_array($domain, $blocked, true)) {
        $errors->add('lps_registration_domain', __('Registration is not available for this email domain.', 'login-page-styler'));
    }
    return $errors;
}
add_filter('registration_errors', 'lps_general_validate_registration_domain', 10, 3);




add_action('init', 'lps_load_textdomain');

/**
 * Summary of lps_load_textdomain
 * Function to load textdomain
 */
function lps_load_textdomain()
{
	// WordPress.org supplies translations automatically for the matching text domain.
	// Do not call load_plugin_textdomain() manually on modern WordPress.
}


/**
 * Summary of lps_register_settings
 * Funtion to register settings set by user
 */
function lps_register_settings()
{

	register_setting('lps-settings-group', 'lps_login_logo_hide', 'absint');
	register_setting('lps-settings-group', 'lps_login_logo_msg_hide', 'absint');
	register_setting('lps-settings-group', 'lps_login_on_off', 'absint');
	register_setting('lps-settings-group', 'lps_login_blog_link_hide', 'absint');
	register_setting('lps-settings-group', 'lps_login_lang_hide', 'absint');
	register_setting('lps-settings-group', 'lps_login_nav_link_hide', 'absint');
	register_setting('lps-settings-group', 'lps_login_copyright', 'sanitize_text_field');
	register_setting('lps-settings-group', 'lps_copyright_color', 'sanitize_hex_color');

	register_setting('lps-settings-group', 'lps_login_logo_link', 'esc_url');
	register_setting('lps-settings-group', 'lps_login_logo_tittle', 'sanitize_text_field');
	register_setting('lps-settings-group', 'lps_login_logo', 'esc_url');
	register_setting('lps-settings-group', 'lps_login_logo_width', 'absint');
	register_setting('lps-settings-group', 'lps_login_logo_height', 'absint');
	register_setting('lps-settings-group', 'lps_login_text_logo', 'sanitize_text_field');
	register_setting('lps-settings-group', 'lps_textlogo_color', 'sanitize_text_field');
	register_setting('lps-settings-group', 'lps_textlogo_color_hover', 'sanitize_text_field');

	register_setting('lps-settings-group', 'lps_login_bg_repeat', 'sanitize_key');
	register_setting('lps-settings-group', 'lps_login_bg_size', 'sanitize_text_field');
	register_setting('lps-settings-group', 'lps_login_bg_position', 'sanitize_text_field');
	register_setting('lps-settings-group', 'lps_recaptcha_theme', 'sanitize_key');
	register_setting('lps-settings-group', 'lps_recaptcha_version', 'sanitize_key');
	register_setting('lps-settings-group', 'lps_recaptcha_v3_threshold', array('type' => 'number', 'sanitize_callback' => 'lps_recaptcha_sanitize_threshold'));
	register_setting('lps-settings-group', 'lps_body_bg_img', 'esc_url');
	register_setting('lps-settings-group', 'lps_login_background_color', 'sanitize_hex_color');
    // Optional Modern Minimal gradient controls (existing color/image options stay intact).
    register_setting('lps-settings-group', 'lps_background_type', array('sanitize_callback' => 'lps_gradient_sanitize_type'));

    register_setting('lps-settings-group', 'lps_gradient_color_one', 'sanitize_hex_color');
    register_setting('lps-settings-group', 'lps_gradient_color_two', 'sanitize_hex_color');

	register_setting('lps-settings-group', 'lps_login_background_image', 'esc_url');

	register_setting('lps-settings-group', 'lps_login_form_input_color', 'sanitize_text_field');

	register_setting('lps-settings-group', 'lps_login_form_width', 'absint');
	// register_setting( 'lps-settings-group', 'lps_login_form_height', 'absint' );

	register_setting('lps-settings-group', 'lps_login_form_border_radius', 'absint');

	register_setting('lps-settings-group', 'lps_login_label_size', 'absint');
	register_setting('lps-settings-group', 'lps_login_label_color', 'sanitize_hex_color');
	register_setting('lps-settings-group', 'lps_login_form_border_color', 'sanitize_hex_color');
	register_setting('lps-settings-group', 'lps_login_form_input_feild_border_color', 'sanitize_hex_color');
	register_setting('lps-settings-group', 'lps_login_remember_label_size', 'absint');
	register_setting('lps-settings-group', 'lps_login_form_position', 'absint');
	register_setting('lps-settings-group', 'lps_design_preset', 'lps_sanitize_design_preset');
	register_setting('lps-settings-group', 'lps_login_form_color', 'sanitize_hex_color');
	register_setting('lps-settings-group', 'lps_login_form_input_feild_border_radius', 'absint');
	register_setting('lps-settings-group', 'lps_login_form_input_text_color', 'sanitize_hex_color');

	// register_setting('lps-settings-group', 'lps_login_form_label_font');.

	register_setting('lps-settings-group', 'lps_login_nav_size', 'absint');
	register_setting('lps-settings-group', 'lps_login_nav_color', 'sanitize_hex_color');

	register_setting('lps-settings-group', 'lps_login_button_color', 'sanitize_hex_color');

	register_setting('lps-settings-group', 'lps_login_button_text_color', 'sanitize_hex_color');

	register_setting('lps-settings-group', 'lps_login_button_border_radius', 'absint');
	register_setting('lps-settings-group', 'lps_login_button_size', 'absint');
	register_setting('lps-settings-group', 'lps_notice_textcolor', 'sanitize_hex_color');
	register_setting('lps-settings-group', 'lps_notice_bgcolor', 'sanitize_hex_color');

	register_setting('lps-settings-group', 'lps_login_custom_css', 'sanitize_textarea_field');

	register_setting('lps-settings-group', 'lps_layout', 'sanitize_text_field');

	register_setting('lps-settings-group', 'lps_login_captcha', 'absint');
	register_setting('lps-settings-group', 'lps_reg_captcha', 'absint');
	register_setting('lps-settings-group', 'lps_lost_captcha', 'absint');
	register_setting('lps-settings-group', 'lps_show_math_captcha', 'absint');	
	register_setting('lps-settings-group', 'rs_site_key', 'sanitize_text_field');
	register_setting('lps-settings-group', 'rs_private_key', 'sanitize_text_field');


	// Free: premium option excluded: lps_enable_private_site

	// Free: premium option excluded: lps_protected_page_ids

	// Free: premium option excluded: lps_private_login_url

	// Free: premium option excluded: lps_private_login_url2

	// Free: premium option excluded: lps_private_login_url3

	// Free: premium option excluded: lps_private_login_url4

	// Free: premium option excluded: lps_private_login_url5


	// Free: premium option excluded: lps_enable_lim

	// Free: premium option excluded: lps_login_attempts

	// Free: premium option excluded: lps_attempts_within

	// Free: premium option excluded: lps_lock_time
	// Free: premium Login/Logout settings are not registered.

	// Free: premium option excluded: lps_menu_location

	// Free: premium option excluded: lps_login_menu_label

	// Free: premium option excluded: lps_logout_menu_label

	// Free: premium option excluded: lps_login_widgetButton

	// Free: premium option excluded: lps_register_widgetButton

	// Free: premium option excluded: lps_lostpassword_widgetButton


	// Free: premium option excluded: lps_redirect_users

	// Free: premium option excluded: lps_redirectafter_users
	

	// Free: premium option excluded: lps_new_login_url

	// Free: premium option excluded: lps_new_redirection_url
	
	
	 // Register settings for the temporary access submenu
	 // Premium-only setting not registered in Free.
	 // Premium-only setting not registered in Free.
	 // Premium-only setting not registered in Free.
}

/**
 * Reset all plugin settings to default values.
 */
function lps_reset_settings()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error('Permission denied.', 403);
	}
	check_ajax_referer('lps_reset_settings', 'nonce');

	// List of all options to reset
	$options = array(
		'lps_login_logo_hide',
		'lps_login_logo_msg_hide',
		'lps_login_on_off',
		'lps_login_blog_link_hide',
		'lps_login_lang_hide',
		'lps_login_nav_link_hide',
		'lps_login_copyright',
		'lps_copyright_color',
		'lps_login_logo_link',
		'lps_login_logo_tittle',
		'lps_login_logo',
		'lps_login_logo_width',
		'lps_login_logo_height',
		'lps_login_text_logo',
		'lps_textlogo_color',
		'lps_textlogo_color_hover',
		'lps_login_bg_repeat',
		'lps_login_bg_size',
		'lps_login_bg_position',
		'lps_body_bg_img',
		'lps_login_background_color',
		'lps_login_background_image',

		'lps_login_form_input_color',
		'lps_login_form_input_text_color',

		'lps_login_form_width',

		'lps_login_form_border_radius',

		'lps_login_label_size',
		'lps_login_label_color',
		'lps_login_form_border_color',
		'lps_login_form_input_feild_border_color',
		'lps_login_remember_label_size',
		'lps_login_form_position',
		'lps_login_form_color',
		'lps_login_form_input_feild_border_radius',

		'lps_login_nav_size',
		'lps_login_nav_color',

		'lps_login_button_color',

		'lps_login_button_text_color',

		'lps_login_button_border_radius',
		'lps_login_button_size',

		'lps_login_custom_css',
		'lps_layout',
		'lps_recaptcha_version',
		'lps_recaptcha_v3_threshold',
		'lps_login_captcha',
		'lps_reg_captcha',
		'lps_lost_captcha',
		'rs_site_key',
		'rs_private_key',

		'lps_notice_textcolor',
		'lps_notice_bgcolor',

		'lps_show_math_captcha',

		'lps_login_nav_bgcolor_hover'
	);

    // A complete visual reset must also clear the selected design and its preset.
    // Do not erase the saved template library: users can reuse their designs later.
    // Reset every visual preset and the gradient builder's saved values.
    $options = array_merge($options, array(
        'lps_design_preset', 'lps_background_type', 'lps_gradient_style',
        'lps_gradient_color_one', 'lps_gradient_color_two', 'lps_gradient_angle'
    ));
    $options[] = 'lps_active_design_template';
    $options[] = 'lps_user_custom_css';
    // Security, CAPTCHA, redirects, and access-control settings are not visual styling.
    $security_options = array('lps_recaptcha_version','lps_recaptcha_v3_threshold','lps_login_captcha','lps_reg_captcha','lps_lost_captcha',
        'rs_site_key','rs_private_key','lps_enable_private_site',
        'lps_private_login_url','lps_private_login_url2','lps_private_login_url3',
        'lps_private_login_url4','lps_private_login_url5','lps_enable_lim',
        'lps_login_attempts','lps_attempts_within','lps_lock_time',
        'lps_redirect_users','lps_redirectafter_users');
    $options = array_diff($options, $security_options);

	// Reset each option to its default value
	foreach ($options as $option) {
		delete_option($option);
	}

	// Optionally, send a success response
	wp_send_json_success('Settings reset to default values.');
}

// Register AJAX action
add_action('wp_ajax_lps_reset_settings', 'lps_reset_settings');


function lps_handle_reset()
{
	if (isset($_POST['lps_reset_settings']) && is_scalar($_POST['lps_reset_settings']) && '1' === sanitize_text_field(wp_unslash((string) $_POST['lps_reset_settings']))) {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Permission denied.', 'login-page-styler'));
		}
		check_admin_referer('lps_reset_settings', 'lps_reset_settings_nonce');
		// Use the same visual reset list as the AJAX reset.
		// Legacy POST reset is intentionally not used by the modern UI.
		delete_option('lps_active_design_template');
		delete_option('lps_login_custom_css');
		delete_option('lps_user_custom_css');
		wp_safe_redirect(admin_url('admin.php?page=login-page-styler'));
		exit;
	}
}

/**
 * Sanitize the array of background image URLs set by the user.
 *
 * @param array $input The array of background image URLs.
 * @return array Sanitized array of background image URLs.
 */
function sanitize_lps_body_bg_slideshow($input)
{
	$sanitized_input = array();

	// Sanitize each input URL.
	foreach ($input as $url) {
		$sanitized_input[] = esc_url_raw($url);
	}

	return $sanitized_input;
}

/**
 * Summary of lps_delete_settings
 * Funtion to delet registered options
 */
function lps_delete_settings()
{
	delete_option('lps_login_on_off');
	delete_option('lps_login_logo_hide');
	delete_option('lps_login_logo_msg_hide');
	delete_option('lps_login_nav_link_hide');
	delete_option('lps_login_blog_link_hide');
}

register_deactivation_hook(__FILE__, 'lps_delete_settings');

add_action('admin_enqueue_scripts', 'lps_enqueue_color_picker');

/**
 * Function to enqueue color picker, media upload, fonts, and CSS
 */
function lps_enqueue_color_picker()
{
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen gating; sensitive AJAX handlers independently verify capability and nonce.
	if (isset($_GET['page']) && is_string($_GET['page']) && 'lps_option' === sanitize_key(wp_unslash($_GET['page']))) {

		// Check if jQuery is enqueued
		if (! wp_script_is('jquery', 'enqueued')) {
			wp_enqueue_script('jquery');
		}
		// Enqueue necessary styles and scripts
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('custom_wp_admin_css', plugins_url('css/style.css', __FILE__), array(), '2.1.12');
		wp_enqueue_style('lps-modern-admin', plugins_url('css/lps-modern-admin.css', __FILE__), array('custom_wp_admin_css'), '9.7.0');
        // Keep the current settings tab selected across Settings API redirects.
        wp_enqueue_script('lps-admin-tabs', plugins_url('js/lps-admin-tabs.js', __FILE__), array(), (string) filemtime(plugin_dir_path(__FILE__) . 'js/lps-admin-tabs.js'), true);
		wp_enqueue_script('lps-preview-controls', plugins_url('js/lps-preview-controls.js', __FILE__), array('jquery'), '9.7.0', true);
		wp_enqueue_script('lps-ux-help', plugins_url('js/lps-ux-help.js', __FILE__), array('jquery'), '9.7.0', true);
		wp_enqueue_style('lps-customizer-compat', plugins_url('css/lps-customizer-compat.css', __FILE__), array('lps-modern-admin'), (string) filemtime(plugin_dir_path(__FILE__) . 'css/lps-customizer-compat.css'));
		wp_enqueue_script('lps-customizer-compat', plugins_url('js/lps-customizer-compat.js', __FILE__), array('jquery', 'wp-color-picker', 'media-views'), (string) filemtime(plugin_dir_path(__FILE__) . 'js/lps-customizer-compat.js'), true);
		wp_enqueue_style('font_select', plugins_url('css/fontselect.css', __FILE__), array(), '1.001');
		wp_enqueue_script('g-fonts-script', plugins_url('js/jquery.fontselect.js', __FILE__), array('jquery'), '1.021', true);

		wp_enqueue_script('login-page-styler', plugins_url('js/loginPageStyler.js', __FILE__), array('wp-color-picker', 'jquery'), '1.13', true);
		wp_enqueue_script('login-page-styler-mapping', plugins_url('js/loginPageStylerMappingJs.js', __FILE__), array('wp-color-picker', 'jquery'), (string) filemtime(plugin_dir_path(__FILE__) . 'js/loginPageStylerMappingJs.js'), true);
        // Local slideshow renderer is shared by the existing admin preview in both editions.
        wp_enqueue_script('lps-local-slideshow', plugins_url('js/lps-local-slideshow.js', __FILE__), array(), (string) filemtime(plugin_dir_path(__FILE__) . 'js/lps-local-slideshow.js'), true);
        wp_enqueue_script('lps-slideshow-admin-preview', plugins_url('js/lps-slideshow-admin-preview.js', __FILE__), array('lps-local-slideshow'), (string) filemtime(plugin_dir_path(__FILE__) . 'js/lps-slideshow-admin-preview.js'), true);

        // Make the local frontend animation definitions available inside the admin preview iframe.
        wp_add_inline_script(
            'login-page-styler-mapping',
            'window.lpsLocalFormAnimationsUrl = ' . wp_json_encode(
                plugins_url('css/lps-local-form-animations.css', __FILE__) . '?ver=' . filemtime(plugin_dir_path(__FILE__) . 'css/lps-local-form-animations.css')
            ) . ';',
            'before'
        );
		// Pass the option value to the script
		$lps_layout = esc_js(trim(get_option("lps_layout")));
		wp_localize_script('login-page-styler-mapping', 'lpsData', array('layout' => $lps_layout));
	}
}


function enqueue_media_uploader_scripts()
{
	if (is_admin()) {
		wp_enqueue_media();
		wp_enqueue_script('lps-media-uploader', plugin_dir_url(__FILE__) . 'js/lpsMediauploader.js', array('jquery'), '1.01', true);
	}
}

add_action('admin_enqueue_scripts', 'enqueue_media_uploader_scripts');


// Enqueue JavaScript for admin page
// Load only the shared admin header styling and scoped Temporary Access CSS.
function lps_enqueue_temp_access_styles($hook) {
    if ($hook !== 'login-page-styler_page_lps_temp_access') {
        return;
    }
    wp_enqueue_style('lps-modern-admin', plugins_url('css/lps-modern-admin.css', __FILE__), array(), '9.9.15');
    wp_enqueue_style('lps-temp-modern', plugins_url('css/lps-temp-modern.css', __FILE__), array('lps-modern-admin'), '9.9.15');
}
add_action('admin_enqueue_scripts', 'lps_enqueue_temp_access_styles');

function lps_enqueue_admin_scripts() {
    // Ensure we're loading the script on the specific admin page
    wp_enqueue_script('lps-admin-script', plugins_url('js/lps-free-security-demo.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'js/lps-free-security-demo.js'), true);
    
    // Pass the AJAX URL and nonce to the script
}
add_action('admin_enqueue_scripts', 'lps_enqueue_admin_scripts');




/**
 * Summary of lps_settings_page
 * Funtion to make admin settings page
 */
function lps_settings_page()
{ ?>
	<div class="main lps-modern-shell">
		<div class="lps-admin-header">
			<div class="lps-admin-brand">
				<span class="lps-admin-mark">L</span>
				<div><h1>Login Page Styler</h1><p>Design a login experience that feels like your brand.</p></div>
			</div>
			<div class="lps-header-version"><span class="lps-live-dot"></span> Visual customizer <span>v<?php echo esc_html( get_plugin_data( plugin_dir_path(__FILE__) . 'loginPageStyler.php', false, false )['Version'] ); ?></span></div>
		</div>

        <div class="lps-global-notices" role="status" aria-live="polite"><?php settings_errors(); ?></div>

		<input class="tabin" id="tab0" type="radio" name="tabs" checked>
		<label class="tabla" for="tab0">General Settings</label>

		<input class="tabin" id="tab1" type="radio" name="tabs">
		<label class="tabla" for="tab1">Login Customizer</label>


		<!-- Template gallery removed; other plugin features remain available. -->

		<input class="tabin" id="tab3" type="radio" name="tabs">
		<label class="tabla" for="tab3">Google ReCaptcha</label>

		<input class="tabin" id="tab4" type="radio" name="tabs">
		<label class="tabla" for="tab4">Login/Logout Menu Item</label>

		<input class="tabin" id="tab5" type="radio" name="tabs">
		<label class="tabla" for="tab5">Login URL/Login Redirect</label>

		<input class="tabin" id="tab6" type="radio" name="tabs">
		<label class="tabla" for="tab6">Login Protected</label>

		<input class="tabin" id="tab7" type="radio" name="tabs">
		<label class="tabla" for="tab7">Limit Login</label>

		<input class="tabin" id="tab8" type="radio" name="tabs">
		<label class="tabla" for="tab8">Blocked IP </label>

		<div class="content">
            <div id="content0" class="lps-general-settings-panel">
              <div class="lps-gs-layout">
                <header class="lps-gs-header">
                  <div class="lps-gs-heading-icon" aria-hidden="true">⚙</div>
                  <div>
                    <h2><?php esc_html_e('General Settings', 'login-page-styler'); ?></h2>
                    <p><?php esc_html_e('Manage sign-in behavior, registration rules, and data preferences.', 'login-page-styler'); ?></p>
                  </div>
                </header>
                <form method="post" action="options.php" class="lps-gs-form">
                  <?php settings_fields('lps-general-settings-group'); ?>
                  <section class="lps-gs-section" aria-labelledby="lps-gs-session-title">
                    <div class="lps-gs-section-header">
                      <span class="lps-gs-section-number" aria-hidden="true">01</span>
                      <div><h3 id="lps-gs-session-title"><?php esc_html_e('Session & convenience', 'login-page-styler'); ?></h3>
                      <p><?php esc_html_e('Choose how regular WordPress login sessions behave.', 'login-page-styler'); ?></p></div>
                    </div>
                    <div class="lps-gs-rows">
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text">
                          <label for="lps_general_remember"><?php esc_html_e('Automatically check Remember Me', 'login-page-styler'); ?></label>
                          <p id="lps-gs-remember-desc"><?php esc_html_e('Preselect Remember Me on the standard login form. Visitors may still uncheck it.', 'login-page-styler'); ?></p>
                        </div>
                        <div class="lps-gs-field-control">
                          <input type="hidden" name="lps_auto_rememberme" value="0">
                          <label class="lps-gs-switch">
                            <input type="checkbox" id="lps_general_remember" name="lps_auto_rememberme" value="1" aria-describedby="lps-gs-remember-desc" <?php checked(1, absint(get_option('lps_auto_rememberme', 0))); ?>>
                            <span class="lps-gs-switch-track" aria-hidden="true"></span>
                            <span class="screen-reader-text"><?php esc_html_e('Automatically check Remember Me', 'login-page-styler'); ?></span>
                          </label>
                        </div>
                      </div>
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text">
                          <label for="lps_login_session_expire"><?php esc_html_e('Session expiration duration', 'login-page-styler'); ?></label>
                          <p id="lps-general-session-description"><?php esc_html_e('Enter 0 to retain WordPress default session duration.', 'login-page-styler'); ?></p>
                        </div>
                        <div class="lps-gs-field-control">
                          <div class="lps-gs-unit-input"><input type="number" min="0" step="1" max="525600" id="lps_login_session_expire" name="lps_login_session_expire" value="<?php echo esc_attr(absint(get_option('lps_login_session_expire', 0))); ?>" aria-describedby="lps-general-session-description"><span><?php esc_html_e('min', 'login-page-styler'); ?></span></div>
                        </div>
                      </div>
                    </div>
                  </section>
                  <section class="lps-gs-section" aria-labelledby="lps-gs-login-title">
                    <div class="lps-gs-section-header">
                      <span class="lps-gs-section-number" aria-hidden="true">02</span>
                      <div><h3 id="lps-gs-login-title"><?php esc_html_e('Login identification', 'login-page-styler'); ?></h3>
                      <p><?php esc_html_e('Select which credentials visitors can use to sign in.', 'login-page-styler'); ?></p></div>
                    </div>
                    <div class="lps-gs-rows">
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text">
                          <label for="lps_login_identifier_policy"><?php esc_html_e('Sign in using', 'login-page-styler'); ?></label>
                          <p><?php esc_html_e('Applies to standard username/password authentication. Both is recommended for compatibility.', 'login-page-styler'); ?></p>
                        </div>
                        <div class="lps-gs-field-control">
                          <select id="lps_login_identifier_policy" name="lps_login_identifier_policy">
                            <?php foreach (array('both' => __('Username or email', 'login-page-styler'), 'username' => __('Username only', 'login-page-styler'), 'email' => __('Email only', 'login-page-styler')) as $value => $label): ?>
                              <option value="<?php echo esc_attr($value); ?>" <?php selected(get_option('lps_login_identifier_policy', 'both'), $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </section>
                  <section class="lps-gs-section" aria-labelledby="lps-gs-expiry-title">
                    <div class="lps-gs-section-header">
                      <span class="lps-gs-section-number" aria-hidden="true">03</span>
                      <div><h3 id="lps-gs-expiry-title"><?php esc_html_e('Password expiration', 'login-page-styler'); ?></h3>
                      <p><?php esc_html_e('Optionally require selected roles to reset an old password. Administrators are always exempt to protect site recovery.', 'login-page-styler'); ?></p></div>
                    </div>
                    <div class="lps-gs-rows">
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text"><label for="lps_password_expiry_enabled"><?php esc_html_e('Require password changes', 'login-page-styler'); ?></label>
                          <p><?php esc_html_e('Off by default. Applies to ordinary username/password login, not social sign-in.', 'login-page-styler'); ?></p></div>
                        <div class="lps-gs-field-control">
                          <input type="hidden" name="lps_password_expiry_enabled" value="0">
                          <label class="lps-gs-switch"><input type="checkbox" id="lps_password_expiry_enabled" name="lps_password_expiry_enabled" value="1" <?php checked(1, absint(get_option('lps_password_expiry_enabled', 0))); ?>><span class="lps-gs-switch-track" aria-hidden="true"></span></label>
                        </div>
                      </div>
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text"><label for="lps_password_expiry_days"><?php esc_html_e('Password age limit', 'login-page-styler'); ?></label>
                          <p><?php esc_html_e('Existing accounts without a recorded password-change date start their countdown from their next successful password login.', 'login-page-styler'); ?></p></div>
                        <div class="lps-gs-field-control"><div class="lps-gs-unit-input"><input type="number" min="1" max="3650" step="1" id="lps_password_expiry_days" name="lps_password_expiry_days" value="<?php echo esc_attr(absint(get_option('lps_password_expiry_days', 90))); ?>"><span><?php esc_html_e('days', 'login-page-styler'); ?></span></div></div>
                      </div>
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text"><strong><?php esc_html_e('Apply to user roles', 'login-page-styler'); ?></strong>
                          <p><?php esc_html_e('Choose roles explicitly. Leave all unchecked to enforce no expirations.', 'login-page-styler'); ?></p></div>
                        <div class="lps-gs-field-control lps-policy-checks">
                          <?php $lps_expiry_selected = (array) get_option('lps_password_expiry_roles', array());
                          foreach (lps_policy_available_roles() as $role_key => $role_name): ?>
                            <label><input type="checkbox" name="lps_password_expiry_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $lps_expiry_selected, true)); ?>> <?php echo esc_html($role_name); ?></label>
                          <?php endforeach; ?>
                          <input type="hidden" name="lps_password_expiry_roles[]" value="">
                        </div>
                      </div>
                    </div>
                  </section>
                  <section class="lps-gs-section" aria-labelledby="lps-gs-strength-title">
                    <div class="lps-gs-section-header">
                      <span class="lps-gs-section-number" aria-hidden="true">04</span>
                      <div><h3 id="lps-gs-strength-title"><?php esc_html_e('Password strength', 'login-page-styler'); ?></h3>
                      <p><?php esc_html_e('Optional guidance and server-side requirements for the native WordPress password-reset form. Existing passwords are not changed.', 'login-page-styler'); ?></p></div>
                    </div>
                    <div class="lps-gs-rows">
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text">
                          <label for="lps_password_strength_enforce"><?php esc_html_e('Enforce password rules', 'login-page-styler'); ?></label>
                          <p><?php esc_html_e('Off by default. Checks new passwords submitted through the standard WordPress reset flow. WordPress already shows its own password strength indicator.', 'login-page-styler'); ?></p>
                        </div>
                        <div class="lps-gs-field-control">
                          <input type="hidden" name="lps_password_strength_enforce" value="0">
                          <label class="lps-gs-switch"><input type="checkbox" id="lps_password_strength_enforce" name="lps_password_strength_enforce" value="1" <?php checked(1, absint(get_option('lps_password_strength_enforce', 0))); ?>><span class="lps-gs-switch-track" aria-hidden="true"></span></label>
                        </div>
                      </div>
                      <div class="lps-gs-field"><div class="lps-gs-field-text"><label for="lps_password_min_length"><?php esc_html_e('Minimum characters', 'login-page-styler'); ?></label><p><?php esc_html_e('Only enforced when Enforce password rules is on.', 'login-page-styler'); ?></p></div><div class="lps-gs-field-control"><input type="number" id="lps_password_min_length" name="lps_password_min_length" min="8" max="128" value="<?php echo esc_attr(absint(get_option('lps_password_min_length', 12))); ?>"></div></div>
                      <div class="lps-gs-field"><div class="lps-gs-field-text"><strong><?php esc_html_e('Required character types', 'login-page-styler'); ?></strong><p><?php esc_html_e('Choose any combination. Leave unchecked to enforce length only.', 'login-page-styler'); ?></p></div>
                        <div class="lps-gs-field-control lps-policy-checks">
                          <?php foreach (array('lps_password_require_upper' => __('Uppercase A–Z', 'login-page-styler'), 'lps_password_require_lower' => __('Lowercase a–z', 'login-page-styler'), 'lps_password_require_number' => __('Number 0–9', 'login-page-styler'), 'lps_password_require_symbol' => __('Special character', 'login-page-styler')) as $setting => $title): ?>
                            <label><input type="hidden" name="<?php echo esc_attr($setting); ?>" value="0"><input type="checkbox" name="<?php echo esc_attr($setting); ?>" value="1" <?php checked(1, absint(get_option($setting, 0))); ?>> <?php echo esc_html($title); ?></label>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                  </section>
                  <section class="lps-gs-section" aria-labelledby="lps-gs-registration-title">
                    <div class="lps-gs-section-header">
                      <span class="lps-gs-section-number" aria-hidden="true">05</span>
                      <div><h3 id="lps-gs-registration-title"><?php esc_html_e('Registration email domains', 'login-page-styler'); ?></h3>
                      <p><?php esc_html_e('Applies only to standard WordPress registration, not social or third-party registration forms.', 'login-page-styler'); ?></p></div>
                    </div>
                    <div class="lps-gs-domain-grid">
                      <div class="lps-gs-domain-box">
                        <label for="lps_registration_allowed_domains"><?php esc_html_e('Allowed domains', 'login-page-styler'); ?></label>
                        <p id="lps-gs-allow-desc"><?php esc_html_e('Optional allowlist. Leave empty to allow all domains except blocked ones.', 'login-page-styler'); ?></p>
                        <textarea id="lps_registration_allowed_domains" name="lps_registration_allowed_domains" rows="4" placeholder="example.com" aria-describedby="lps-gs-allow-desc"><?php echo esc_textarea(get_option('lps_registration_allowed_domains', '')); ?></textarea>
                        <span class="lps-gs-help"><?php esc_html_e('One domain per line', 'login-page-styler'); ?></span>
                      </div>
                      <div class="lps-gs-domain-box">
                        <label for="lps_registration_blocked_domains"><?php esc_html_e('Blocked domains', 'login-page-styler'); ?></label>
                        <p id="lps-gs-block-desc"><?php esc_html_e('Optional denylist. Exact matches only; blocked entries take precedence.', 'login-page-styler'); ?></p>
                        <textarea id="lps_registration_blocked_domains" name="lps_registration_blocked_domains" rows="4" placeholder="example.net" aria-describedby="lps-gs-block-desc"><?php echo esc_textarea(get_option('lps_registration_blocked_domains', '')); ?></textarea>
                        <span class="lps-gs-help"><?php esc_html_e('One domain per line', 'login-page-styler'); ?></span>
                      </div>
                    </div>
                  </section>
                  <section class="lps-gs-section" aria-labelledby="lps-gs-data-title">
                    <div class="lps-gs-section-header">
                      <span class="lps-gs-section-number" aria-hidden="true">06</span>
                      <div><h3 id="lps-gs-data-title"><?php esc_html_e('Data & uninstall', 'login-page-styler'); ?></h3>
                      <p><?php esc_html_e('Control whether plugin options are retained when the plugin is deleted.', 'login-page-styler'); ?></p></div>
                    </div>
                    <div class="lps-gs-rows">
                      <div class="lps-gs-field">
                        <div class="lps-gs-field-text">
                          <label for="lps_delete_data_on_uninstall"><?php esc_html_e('Remove plugin options on uninstall', 'login-page-styler'); ?></label>
                          <p id="lps-gs-uninstall-desc"><?php esc_html_e('Off by default. Applies only when the plugin is deleted, not when it is deactivated or updated. Does not delete WordPress users.', 'login-page-styler'); ?></p>
                        </div>
                        <div class="lps-gs-field-control">
                          <input type="hidden" name="lps_delete_data_on_uninstall" value="0">
                          <label class="lps-gs-switch">
                            <input type="checkbox" id="lps_delete_data_on_uninstall" name="lps_delete_data_on_uninstall" value="1" aria-describedby="lps-gs-uninstall-desc" <?php checked(1, absint(get_option('lps_delete_data_on_uninstall', 0))); ?>>
                            <span class="lps-gs-switch-track" aria-hidden="true"></span>
                            <span class="screen-reader-text"><?php esc_html_e('Remove plugin options on uninstall', 'login-page-styler'); ?></span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </section>
                  <div class="lps-gs-bottom-bar">
                    <p><?php esc_html_e('Password-expiry and strength-enforcement controls will be added after recovery-flow testing.', 'login-page-styler'); ?></p>
                    <?php submit_button(__('Save General Settings', 'login-page-styler'), 'primary', 'submit', false); ?>
                  </div>
                </form>
              </div>
            </div>
			<div id="content1">
				<div class='wrap'>


					<form method="post" action="options.php">

						<?php settings_fields('lps-settings-group'); ?>

						<?php do_settings_sections('lps_option'); ?>

                        <?php
                        // Visual cards only select the EXISTING preset control. The original
                        // confirmation, safe visual reset and preview refresh stay intact.
                        $lps_designs = array(
                            'default'       => array('Default WordPress Login', 'Classic WordPress layout', 'default'),
                            'modern-minimal'=> array('Modern Minimal', 'Clean, airy sign-in', 'minimal'),
                            'nature-pro'    => array('Nature Pro', 'Forest glass design', 'nature'),
                            'noir-editorial'=> array('Noir Editorial', 'Dark editorial styling', 'noir'),
                            'split-studio'  => array('Split-Screen Studio', 'Branding + login panel', 'split'),
                            'aurora-glass'  => array('Aurora Glass', 'Neon frosted-glass studio', 'aurora'),
                        );
                        $lps_current_design = get_option('lps_design_preset', 'default');
                        ?>
                        <section class="lps-visual-gallery" aria-labelledby="lps-gallery-title">
                            <div class="lps-gallery-heading">
                                <div><h2 id="lps-gallery-title">Choose your login page design</h2>
                                <p>Choose a Free design to publish. Premium designs can be explored in Live Preview only and cannot be applied to the public login page in this edition.</p></div>
                            </div>
                            <label for="lps_design_preset" class="screen-reader-text">Login page design</label>
                            <select name="lps_design_preset" id="lps_design_preset" class="lps-gallery-native-select" aria-label="Login page design">
                                <?php foreach ($lps_designs as $lps_id => $lps_design) : ?>
                                  <option value="<?php echo esc_attr($lps_id); ?>" <?php selected($lps_current_design, $lps_id); ?>><?php echo esc_html($lps_design[0]); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="lps-gallery-grid" role="group" aria-label="Available login page designs">
                              <?php foreach ($lps_designs as $lps_id => $lps_design) : ?>
                                <button type="button" class="lps-gallery-item <?php echo $lps_current_design === $lps_id ? 'is-selected' : ''; ?>" data-lps-design="<?php echo esc_attr($lps_id); ?>" aria-pressed="<?php echo $lps_current_design === $lps_id ? 'true' : 'false'; ?>">
                                  <?php if (!in_array($lps_id, array('default', 'modern-minimal'), true)) : ?>
                                    <span class="lps-gallery-pro-preview-badge" aria-label="Pro Preview: cannot publish in Free">Pro Preview</span>
                                  <?php endif; ?>
                                  <span class="lps-gallery-art lps-gallery-art--<?php echo esc_attr($lps_design[2]); ?>" aria-hidden="true">
                                    <span class="lps-gallery-mockup"><span class="lps-gallery-dot"></span><span class="lps-gallery-lines"></span><span class="lps-gallery-field"></span><span class="lps-gallery-field"></span><span class="lps-gallery-cta"></span></span>
                                  </span>
                                  <span class="lps-gallery-meta"><strong><?php echo esc_html($lps_design[0]); ?></strong><small><?php echo esc_html($lps_design[1]); ?></small></span>
                                  <span class="lps-gallery-selected" aria-hidden="true">✓</span>
                                </button>
                              <?php endforeach; ?>
                            </div>
                        </section>


						<h1><?php esc_html_e('Login Page Styler', 'login-page-styler'); ?></h1>

						<h3><?php esc_html_e('Enable plugin and save settings  for styling to take effect on Frontend  Login page ', 'login-page-styler'); ?></h3>


						<!--
		<iframe  id="loginPagePreview" src="lpsLoginTemplate.php" width="100%" height="600px" frameborder="0"></iframe>
		-->
						<!--<div class="plugin-settings-login-template">
	<iframe id="lps-iframe" src="<?php // echo esc_url(plugin_dir_url(__FILE__) . 'lpsnewLogin.php'); ?>" frameborder="0" width="100%" height="700px"></iframe>
</div>
-->

						<style>
							.wrapper th {
								color: #666;
								font-size: 1em;
								padding-top: 7px;

							}


							.wrapper td {
								padding-left: 1px;
							}

							.wrapper h3 a,
							p a {
								text-decoration: none;
							}

							.wrapper td p {
								color: #666;
								font-size: 1em;
							}

							.wrapper {
								display: flex;
							}

							.accordion-container {
								flex: 1;
								width: 23%;
								margin-left: -11px;
							}

							.accordion input[type="checkbox"] {
								display: none;
							}

							.accordion-label {
								display: block;
								background-color: #1D2327;
								color: #fff;
								padding: 15px;
								cursor: pointer;
								font-weight: bold;
								border: 1px solid #FFBA00;
								border-bottom: none;
								position: relative;
								transition: background-color 0.3s;
								text-align: center;
							}

							.accordion-label:hover {
								background-color: #FFBA00;
							}

							.accordion-content {
								display: none;
								padding: 0px;
								border: 0px solid #2980b9;
								border-top: none;
								background-color: #ffffff;
								color: #333;
								overflow-y: auto;
								max-height: 400px;
								/* Adjust the maximum height as needed */
							}

							.accordion input[type="checkbox"]:checked+.accordion-label+.accordion-content {
								display: block;
							}

							.accordion input[type="checkbox"]:not(:checked)+.accordion-label+.accordion-content {
								display: none;
							}

							.accordion input[type="checkbox"]+.accordion-label::before {
								content: "+";
								float: right;
								font-size: 18px;
								line-height: 18px;
								transition: transform 0.3s;
							}

							.accordion input[type="checkbox"]:checked+.accordion-label::before {
								content: "+";
								transform: rotate(45deg);
							}

							.preview-container {
								padding: 0px;
								border: 1px solid #ddd;
								background-color: #f9f9f9;
								color: #333;
								width: 77%;
							}

							.independent-preview {
								/* Style the independent preview here */
							}

							.accordion-content table {
								max-width: 100%;
								width: auto;
							}

							.accordion-content label {
								color: #FFBA00;
								font-weight: 600;
							}
						</style>

						<!--<label for="inputField">Enter Text:</label>
						<input type="text" class="color_picker" id="inputFieldlps">

						<div id="preview">Live Preview: <span id="previewText"></span></div> -->


						<div class="lps-plugin-enable"><div class="lps-plugin-enable-copy"><strong>Enable Login Page Styler</strong><span>Publish your saved design on the login page. Preview and editing remain available when off. Security settings are independent.</span></div><table class="form-table"><tr valign='top'>
								<th scope='row'><?php esc_html_e('Enable Plugin :', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_on_off" class="onoffswitch-checkbox" id="myonoffswitch" value='1' <?php checked(1, absint(get_option('lps_login_on_off'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitch">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr></table></div>
<div class="lps-design-workspace-intro"><span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span><div class="lps-design-workspace-intro__copy"><strong>Login Customizer</strong><span>Personalize your login page and see changes instantly.</span></div></div>
<div class="wrapper">

							<div class="accordion-container">
                        <div id="lps-aurora-settings" class="lps-split-settings lps-aurora-settings" <?php echo get_option('lps_design_preset','default') === 'aurora-glass' ? '' : 'hidden'; ?>>
                          <details><summary>Aurora Glass Settings <small>Template-specific design controls</small></summary>
                          <?php
                          $aurora_groups=array(
                            'Layout'=>array('brand_side','brand_width','divider'),
                            'Branding Content'=>array('brand_label','headline','description','footer'),
                            'Welcome Content'=>array('welcome','welcome_description'),
                            'Glass & Background'=>array('glass_tint','glass_radius','accent','text_color','decorative_image'),
                            'Typography'=>array('headline_font','headline_size','welcome_font','welcome_size'),
                            'Mobile'=>array('mobile_brand','mobile_first')
                          );
                          foreach($aurora_groups as $title=>$keys){echo '<fieldset class="lps-split-group"><legend>'.esc_html($title).'</legend><div class="lps-split-grid">';
                            foreach($keys as $key){$f=lps_aurora_fields()[$key];$value=lps_aurora_option($key);$id='lps_aurora_'.$key;
                              echo '<label class="lps-split-field"><span>'.esc_html($f[0]).'</span>';
                              if($f[1]==='select'){echo '<select id="'.esc_attr($id).'" name="'.esc_attr($id).'">';foreach($f[3] as $val=>$label)echo '<option value="'.esc_attr($val).'" '.selected($value,$val,false).'>'.esc_html($label).'</option>';echo '</select>';}
                              elseif($f[1]==='checkbox'){echo '<input type="hidden" name="'.esc_attr($id).'" value="0"><input type="checkbox" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="1" '.checked($value,'1',false).'>';}
                              elseif($f[1]==='textarea'){echo '<textarea id="'.esc_attr($id).'" name="'.esc_attr($id).'" rows="3">'.esc_textarea($value).'</textarea>';}
                              elseif($f[1]==='color'){echo '<input type="text" class="lps-aurora-color" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" data-default-color="'.esc_attr($f[2]).'">';}
                              elseif($f[1]==='google_font'){echo '<input type="text" class="lps-aurora-google-font" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" autocomplete="off">';}
                              elseif($key==='decorative_image'){echo '<div class="lps-split-media"><input type="url" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" placeholder="Keep empty for included ribbons image"><button type="button" class="button lps-aurora-upload">Choose Image</button><button type="button" class="button lps-aurora-clear">Remove</button></div>';}
                              else{echo '<input type="'.esc_attr($f[1]).'" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" '.($f[1]==='number'?'min="'.(int)$f[3][0].'" max="'.(int)$f[3][1].'"':'').'>';}
                              echo '</label>';
                            }echo '</div></fieldset>';
                          }
                          ?>
                          <p><button type="button" class="button" id="lps-aurora-reset" data-nonce="<?php echo esc_attr(wp_create_nonce('lps_reset_aurora')); ?>" data-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">Reset This Template</button></p>
                          <p class="description">Main Customizer controls remain responsible for logo, form, fields, buttons, and background blur. Blank optional text is hidden.</p>
                          </details>
                        </div>
                        <div id="lps-split-settings" class="lps-split-settings" <?php echo get_option('lps_design_preset','default') === 'split-studio' ? '' : 'hidden'; ?>>
                          <details><summary>Split-Screen Settings <small>Template-specific customization</small></summary>
                          <?php
                          $groups=array(
                            'Layout & Position'=>array('side','width','panel_align'),
                            'Branding Background'=>array('gradient_one','gradient_two','background_image','overlay','art'),
                            'Branding Content'=>array('brand_icon','brand_label','eyebrow','headline','description','footnote','brand_text_color'),
                            'Typography'=>array('headline_font','headline_size','headline_weight','welcome_font','welcome_size'),
                            'Welcome Content'=>array('welcome','welcome_text','welcome_color'),
                            'Mobile Behavior'=>array('mobile_brand','mobile_first','mobile_height')
                          );
                          foreach($groups as $title=>$keys){ echo '<fieldset class="lps-split-group"><legend>'.esc_html($title).'</legend><div class="lps-split-grid">';
                          foreach($keys as $key){ $field=lps_split_fields()[$key];$value=lps_split_option($key);$id='lps_split_'.$key;
                            echo '<label class="lps-split-field"><span>'.esc_html($field[0]).'</span>';
                            if($field[1]==='google_font'){echo '<input type="text" class="lps-split-google-font" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" autocomplete="off">';}
                            elseif($field[1]==='select'){echo '<select id="'.esc_attr($id).'" name="'.esc_attr($id).'">';foreach($field[3] as $v=>$label)echo '<option value="'.esc_attr($v).'" '.selected($value,$v,false).'>'.esc_html($label).'</option>';echo '</select>';}
                            elseif($field[1]==='checkbox'){echo '<input type="hidden" name="'.esc_attr($id).'" value="0"><input type="checkbox" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="1" '.checked($value,'1',false).'>';}
                            elseif($field[1]==='textarea'){echo '<textarea id="'.esc_attr($id).'" name="'.esc_attr($id).'" rows="2">'.esc_textarea($value).'</textarea>';}
                            elseif($field[1]==='color'){echo '<input type="text" class="lps-split-color" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" data-default-color="'.esc_attr($field[2]).'">';}
                            elseif($key==='background_image'){echo '<div class="lps-split-media"><input type="url" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" placeholder="Choose an image or enter URL"><button type="button" class="button lps-split-upload" data-target="'.esc_attr($id).'">Choose Image</button><button type="button" class="button lps-split-clear" data-target="'.esc_attr($id).'">Remove</button></div>';}
                            
                            else {echo '<input type="'.esc_attr($field[1]).'" id="'.esc_attr($id).'" name="'.esc_attr($id).'" value="'.esc_attr($value).'" '.($field[1]==='number'?'min="'.(int)$field[3][0].'" max="'.(int)$field[3][1].'"':'').'>';}
                            echo '</label>';
                          } echo '</div></fieldset>';}
                          ?>
                          <p><button type="button" id="lps-split-reset" class="button button-secondary" data-nonce="<?php echo esc_attr(wp_create_nonce('lps_reset_split')); ?>" data-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">Reset This Template</button> <span class="description">Restores Split-Screen defaults only, then reloads the editor. Other settings are not changed.</span></p>
                          <p class="description">These controls only affect Split-Screen Studio. Clear optional text to hide it. Global form, logo and Social Login settings remain separate.</p>
                          </details>
                        </div>

<div class="accordion lps-general-accordion">
<input type="checkbox" id="lps-general-section" name="accordion">
<label class="accordion-label" for="lps-general-section">01 · General & login behavior</label>
<div class="accordion-content"><p class="lps-section-hint">Show or hide login elements and configure session behavior.</p>
	                    <table class="form-table">

							

							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Hide Login Logo', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_logo_hide" class="onoffswitch-checkbox" id="myonoffswitch2" value='1' <?php checked(1, absint(get_option('lps_login_logo_hide'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitch2">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>


							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Hide Login Error Msg', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_logo_msg_hide" class="onoffswitch-checkbox" id="myonoffswitch3" value='1' <?php checked(1, absint(get_option('lps_login_logo_msg_hide'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitch3">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>



							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Hide Lost Password Link', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_nav_link_hide" class="onoffswitch-checkbox" id="myonoffswitch4" value='1' <?php checked(1, absint(get_option('lps_login_nav_link_hide'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitch4">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>


							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Hide Back to Blog Link', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_blog_link_hide" class="onoffswitch-checkbox" id="myonoffswitch5" value='1' <?php checked(1, absint(get_option('lps_login_blog_link_hide'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitch5">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Hide Language switcher on Login ', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_login_lang_hide" class="onoffswitch-checkbox" id="myonoffswitch6" value='1' <?php checked(1, absint(get_option('lps_login_lang_hide'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitch6">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

						</table></div></div>
								<div class="accordion">
									<input type="checkbox" id="section1" name="accordion">
									<label class="accordion-label" for="section1">Logo</label>
									<div class="accordion-content">

										<table class="form-table">

											<!-- Logo Image -->
											<tr valign="top">
												<td>
													<label for="lps_login_logo"><?php esc_html_e('Logo Image', 'login-page-styler'); ?></label><br />
													<input id="lps_login_logo" type="text" name="lps_login_logo" value="<?php echo esc_url(get_option('lps_login_logo')); ?>" size="25" />
													<input class="lps-upload-button button" type="button" value="Upload Image" />
													<p class='description'><?php esc_html_e('Use 84px X 84px logo. The default WP logo size is 84px X 84px.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<!-- Logo Width -->
											<tr valign="top">
												<td>
													<label for="lps_login_logo_width"><?php esc_html_e('Logo Width', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_logo_width' name='lps_login_logo_width' min='0' max='350' value='<?php echo absint(get_option('lps_login_logo_width')); ?>' oninput="this.form.amountInputW.value=this.value" />
													<input type="number" name="amountInputW" min="0" max="350" value='<?php echo absint(get_option('lps_login_logo_width')); ?>' size='2' oninput="this.form.lps_login_logo_width.value=this.value" />px
													<p class="description"><?php esc_html_e('For Big Logo change logo width as per your logo width.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<!-- Logo Height -->
											<tr valign="top">
												<td>
													<label for="lps_login_logo_height"><?php esc_html_e('Logo Height', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_logo_height' name='lps_login_logo_height' min='0' max='200' value='<?php echo absint(get_option('lps_login_logo_height')); ?>' oninput="this.form.amountInputH.value=this.value" />
													<input type="number" name="amountInputH" min="0" max="200" value='<?php echo absint(get_option('lps_login_logo_height')); ?>' size='4' oninput="this.form.lps_login_logo_height.value=this.value" />px
													<p class="description"><?php esc_html_e('For Big Logo change height as per your logo height.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<!-- Text Logo -->
											<tr valign="top">
												<td>
													<label for="lps_login_text_logo"><?php esc_html_e('Text Logo', 'login-page-styler'); ?></label><br />
													<input type="text" id="lps_login_text_logo" name="lps_login_text_logo" value="<?php echo esc_attr(get_option('lps_login_text_logo')); ?>" />
													<p class="description"><?php esc_html_e('Enter text if you don\'t have a logo yet.', 'login-page-styler'); ?></p>
													<p class="description"><?php esc_html_e('If you want to use text logo, make sure to leave Logo Title and Logo Image empty.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<!-- Google font for Text Logo -->
											<tr valign="top">
												<td>
													<label for="lps_gfonttext_logo"><?php esc_html_e('Google font for Text Logo', 'login-page-styler'); ?></label><br />
													<input name="lps_gfonttext_logo" id="lps_gfonttext_logo" class="lps_textlogofont" type="text" value="<?php echo esc_attr(get_option('lps_gfonttext_logo')); ?>" />
												</td>
											</tr>

											<!-- Text Logo Color -->
											<tr>
												<td>
													<label for='lps_textlogo_color'><?php esc_html_e('Text Logo Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_textlogo_color' name='lps_textlogo_color' value='<?php echo esc_attr(get_option('lps_textlogo_color')); ?>' />
												</td>
											</tr>

											<!-- Text Logo Color Hover -->
											<tr>
												<td>
													<label for='lps_textlogo_color_hover'><?php esc_html_e('Text Logo Color Hover', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_textlogo_color_hover' name='lps_textlogo_color_hover' value='<?php echo esc_attr(get_option('lps_textlogo_color_hover')); ?>' />
												</td>
											</tr>

											<!-- Logo Link -->
											<tr valign="top">
												<td>
													<label for="lps_login_logo_link"><?php esc_html_e('Logo Link', 'login-page-styler'); ?></label><br />
													<input type="text" placeholder="google.com" id="lps_login_logo_link" name="lps_login_logo_link" size="25" value="<?php echo esc_url(get_option('lps_login_logo_link')); ?>" />
													<p class="description"><?php esc_html_e('It will redirect users when the logo is clicked.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<!-- Logo Title -->
											<tr valign="top">
												<td>
													<label for="lps_login_logo_tittle"><?php esc_html_e('Logo Title', 'login-page-styler'); ?></label><br />
													<input type="text" size="25" id="lps_login_logo_tittle" name="lps_login_logo_tittle" value="<?php echo esc_attr(get_option('lps_login_logo_tittle')); ?>" />
													<p class="description"><?php esc_html_e('Enter the title for the logo, e.g., Powered by Your Site.', 'login-page-styler'); ?></p>
												</td>
											</tr>
										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section2" name="accordion">
									<label class="accordion-label" for="section2">Background</label>
									<div class="accordion-content">


                                        <div class="lps-gradient-controls" id="lps-gradient-controls" style="padding:12px 10px 0;max-width:550px;">
                                            <p><label for="lps_background_type"><strong>Background Type</strong></label><br>
                                            <select id="lps_background_type" name="lps_background_type">
                                                <?php foreach (array('auto'=>'Template Default / Existing Settings','solid'=>'Solid Color','gradient'=>'Gradient','image'=>'Image') as $key=>$name): ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php selected(get_option('lps_background_type','auto'),$key); ?>><?php echo esc_html($name); ?></option>
                                                <?php endforeach; ?>
                                            </select></p>
                                            <div id="lps-gradient-fields" style="padding:12px 14px;border:1px solid #dcdcde;border-radius:9px;background:#f8faff;">
                                                <p><label for="lps_gradient_style">Gradient Style</label><br>
                                                <select id="lps_gradient_style" name="lps_gradient_style"><option value="linear" <?php selected(get_option('lps_gradient_style','linear'),'linear'); ?>>Linear</option><option value="radial" <?php selected(get_option('lps_gradient_style','linear'),'radial'); ?>>Radial</option></select></p>
                                                <p><label for="lps_gradient_color_one">First Color</label><br><input class="color_picker" type="text" id="lps_gradient_color_one" name="lps_gradient_color_one" value="<?php echo esc_attr(get_option('lps_gradient_color_one','#e7e8ff')); ?>"></p>
                                                <p><label for="lps_gradient_color_two">Second Color</label><br><input class="color_picker" type="text" id="lps_gradient_color_two" name="lps_gradient_color_two" value="<?php echo esc_attr(get_option('lps_gradient_color_two','#d9f4f0')); ?>"></p>
                                                <p id="lps-gradient-angle-row"><label for="lps_gradient_angle">Gradient Angle: <output id="lps-gradient-angle-value"><?php echo esc_html(get_option('lps_gradient_angle',135)); ?>°</output></label><br><input id="lps_gradient_angle" name="lps_gradient_angle" type="range" min="0" max="360" step="5" value="<?php echo esc_attr(get_option('lps_gradient_angle',135)); ?>" style="width:90%;"></p>
                                                <p><button type="button" class="button" id="lps-gradient-reset">Reset Gradient to Template Colors</button></p>
                                            </div>
                                            <p class="description">Gradient settings apply to Modern Minimal. Existing background color and image controls remain available below.</p>
                                        </div>
										
                                            <div class="lps-background-animation-field">
                                                <label for="lps_background_animation"><strong>Background Animation</strong></label> <span class="lps-customizer-pro-badge">Pro</span>
                                                <p><select id="lps_background_animation" data-lps-premium="1">
                                                    <option value="none">None</option><option value="drift">Slow Background Drift</option><option value="pulse">Soft Background Pulse</option>
                                                </select></p>
                                                <p class="description">Adds subtle motion to the login background. Respects reduced-motion preferences.</p>
                                            </div>
<table class="form-table">
											<tr valign="top">
												<td>
													<label for="lps_login_background_color"><?php esc_html_e('Background Color', 'login-page-styler'); ?></label><br />
													<input type="text" class="color_picker" id="lps_login_background_color" name="lps_login_background_color" value="<?php echo esc_attr(get_option('lps_login_background_color')); ?>" />
												</td>
											</tr>

											<tr valign="top">
												<td>
													<label for="lps_body_bg_img"><?php esc_html_e('Background Image', 'login-page-styler'); ?></label><br />
													<input id="lps_body_bg_img" type="text" name="lps_body_bg_img" value="<?php echo esc_url(get_option('lps_body_bg_img')); ?>" size="25" /></br>
													<input class="lps-upload-button button" type="button" value="Upload Image" />
													<p class='description'><?php esc_html_e('Upload or Select  Background Image', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign="top">
												<td>
													<label for="lps_login_bg_repeat">
														<?php esc_html_e('Login Body Background Image Repeat', 'login-page-styler'); ?>
													</label><br />
													<select name='lps_login_bg_repeat' id='lps_login_bg_repeat'>
														<option value='no-repeat' <?php selected(sanitize_key(get_option('lps_login_bg_repeat')), 'no-repeat'); ?>>No Repeat</option>
														<option value='repeat-x' <?php selected(sanitize_key(get_option('lps_login_bg_repeat')), 'repeat-x'); ?>>Repeat X</option>
														<option value='repeat-y' <?php selected(sanitize_key(get_option('lps_login_bg_repeat')), 'repeat-y'); ?>>Repeat Y</option>
<option value='repeat' <?php selected(sanitize_key(get_option('lps_login_bg_repeat')), 'repeat'); ?>>Repeat Both</option>
													</select>
<p class='description'>Use No Repeat for a single full-page image.</p>
<label for='lps_login_bg_size'>Background Size</label><br />
<select id='lps_login_bg_size' name='lps_login_bg_size'><?php foreach (array('cover'=>'Cover (recommended)','contain'=>'Contain','auto'=>'Original Size') as $value=>$label) { echo '<option value="'.esc_attr($value).'" '.selected(get_option('lps_login_bg_size','cover'),$value,false).'>'.esc_html($label).'</option>'; } ?></select><br />
<label for='lps_login_bg_position'>Background Position</label><br />
<select id='lps_login_bg_position' name='lps_login_bg_position'><?php foreach (array('center center'=>'Center','center top'=>'Top','center bottom'=>'Bottom','left center'=>'Left','right center'=>'Right') as $value=>$label) { echo '<option value="'.esc_attr($value).'" '.selected(get_option('lps_login_bg_position','center center'),$value,false).'>'.esc_html($label).'</option>'; } ?></select>
</td>
</tr>


										</table>

										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section3" name="accordion">
									<label class="accordion-label" for="section3">Login Form</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign='top'>
												<td><label for='lps_login_form_position'><?php esc_html_e('Form Position', 'login-page-styler'); ?></label><br />
													<select id="lps_login_form_position" name="lps_login_form_position">
                                                        <option value='0' <?php selected(absint(get_option('lps_login_form_position', 0)), 0); ?>>Default (Use Template Layout)</option>
														<option value='1' <?php selected(absint(get_option('lps_login_form_position')), '1'); ?>>Middle-Center</option>
														<option value='2' <?php selected(absint(get_option('lps_login_form_position')), '2'); ?>>Middle-Left</option>
														<option value='3' <?php selected(absint(get_option('lps_login_form_position')), '3'); ?>>Middle-Right</option>
														<option value='4' <?php selected(absint(get_option('lps_login_form_position')), '4'); ?>>Top-Center</option>
														<option value='5' <?php selected(absint(get_option('lps_login_form_position')), '5'); ?>>Top-Left</option>
														<option value='6' <?php selected(absint(get_option('lps_login_form_position')), '6'); ?>>Top-Right</option>
														<option value='7' <?php selected(absint(get_option('lps_login_form_position')), '7'); ?>>Bottom-Center</option>
														<option value='8' <?php selected(absint(get_option('lps_login_form_position')), '8'); ?>>Bottom-Left</option>
														<option value='9' <?php selected(absint(get_option('lps_login_form_position')), '9'); ?>>Bottom-Right</option>
													</select>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_animation'><?php esc_html_e('Form Animation', 'login-page-styler'); ?></label><br />
													<select id="lps_login_animation" name="lps_login_animation">
														<option value="fadeIn" <?php selected(get_option('lps_login_animation'), 'fadeIn'); ?>>Fade In</option>
														<option value="slideInLeft" <?php selected(get_option('lps_login_animation'), 'slideInLeft'); ?>>Slide In from Left</option>
														<option value="bounceIn" <?php selected(get_option('lps_login_animation'), 'bounceIn'); ?>>Bounce In</option>
														<option value="rotateIn" <?php selected(get_option('lps_login_animation'), 'rotateIn'); ?>>Rotate In</option>
														<option value="zoomIn" <?php selected(get_option('lps_login_animation'), 'zoomIn'); ?>>Zoom In</option>
														<option value="flash" <?php selected(get_option('lps_login_animation'), 'flash'); ?>>Flash</option>
														<option value="pulse" <?php selected(get_option('lps_login_animation'), 'pulse'); ?>>Pulse</option>
														<option value="shake" <?php selected(get_option('lps_login_animation'), 'shake'); ?>>Shake</option>
														<option value="rollIn" <?php selected(get_option('lps_login_animation'), 'rollIn'); ?>>Roll In</option>
														<option value="swing" <?php selected(get_option('lps_login_animation'), 'swing'); ?>>Swing</option>
														<option value="rubberBand" <?php selected(get_option('lps_login_animation'), 'rubberBand'); ?>>Rubber Band</option>
														<option value="tada" <?php selected(get_option('lps_login_animation'), 'tada'); ?>>Tada</option>
														<option value="jello" <?php selected(get_option('lps_login_animation'), 'jello'); ?>>Jello</option>
														<!-- Add more animation options as needed -->
													</select>
													<p class="description"><?php esc_html_e('Select login Form animation.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_login_form_bg"><?php esc_html_e('Form Background Image', 'login-page-styler'); ?></label><br />
													<input id="lps_login_form_bg" type="text" name="lps_login_form_bg" value="<?php echo esc_url(get_option('lps_login_form_bg')); ?>" size="25" />
													<input class="lps-upload-button" type="button" value="Upload Image" />
												</td>
											</tr>

											<?php if (true) : ?>
                                            <tr class="lps-nature-blur-setting" style="display:none;">
                                                <td>
                                                    <label for="lps_nature_form_blur"><strong><?php esc_html_e('Form Background Blur (All Templates)', 'login-page-styler'); ?></strong></label><br />
                                                    <input type="range" id="lps_nature_form_blur" name="lps_nature_form_blur" min="0" max="40" step="1" value="<?php echo esc_attr(lps_form_blur_for_preset(get_option('lps_design_preset', 'default'))); ?>" oninput="document.getElementById('lps_nature_blur_value').value=this.value" />
                                                    <output id="lps_nature_blur_value"><?php echo esc_html(lps_form_blur_for_preset(get_option('lps_design_preset', 'default'))); ?></output>px
                                                    <p class="description">0px = no blur; higher values blur the area behind a translucent form. Set Form Color opacity below 1 for a visible effect.</p>
                                                    <button type="button" class="button" id="lps_nature_blur_reset">Reset Blur (0px)</button>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
												<td><label for='lps_login_form_color'><?php esc_html_e('Form Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_color' name='lps_login_form_color' value='<?php echo esc_attr(get_option('lps_login_form_color')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_form_color_opacity'><?php esc_html_e('Form Opacity', 'login-page-styler'); ?></label><br />
													<input type='range' step='0.01' min='0' max='1' id='lps_login_form_color_opacity' name='lps_login_form_color_opacity' value='<?php echo esc_attr(get_option('lps_login_form_color_opacity', '1')); ?>' oninput="this.form.amountInputFormOpacity.value=this.value" />
													<input type='number' step='0.01' min='0' max='1' name='amountInputFormOpacity' value='<?php echo esc_attr(get_option('lps_login_form_color_opacity', '1')); ?>' size='4' oninput="this.form.lps_login_form_color_opacity.value=this.value" />
													<p class='description'><?php esc_html_e('Login form Opacity. This option make form transparent', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_form_border_color'><?php esc_html_e('Form Border Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_border_color' name='lps_login_form_border_color' value='<?php echo esc_attr(get_option('lps_login_form_border_color')); ?>' />
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_login_form_border_size"><?php esc_html_e('Form Border Style', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_form_border_size' name='lps_login_form_border_size' min='0' max='10' value='<?php echo absint(get_option('lps_login_form_border_size')); ?>' oninput="this.form.amountInput3.value=this.value" />
													<input type="number" name="amountInput3" min="0" max="10" value='<?php echo absint(get_option('lps_login_form_border_size')); ?>' size='4' oninput="this.form.lps_login_form_border_size.value=this.value" />px
													<p class="description"><?php esc_html_e(' Form border size', 'login-page-styler'); ?></p>


													<label for="lps_login_form_border_style"></label>
													<select name='lps_login_form_border_style' id='lps_login_form_border_style'>
														<option value='none' <?php selected(sanitize_key(get_option('lps_login_form_border_style')), 'none'); ?>>None</option>
														<option value='solid' <?php selected(sanitize_key(get_option('lps_login_form_border_style')), 'solid'); ?>>Solid</option>
														<option value='dashed' <?php selected(sanitize_key(get_option('lps_login_form_border_style')), 'dashed'); ?>>Dashed</option>
														<option value='dotted' <?php selected(sanitize_key(get_option('lps_login_form_border_style')), 'dotted'); ?>>Dotted</option>
														<option value='double' <?php selected(sanitize_key(get_option('lps_login_form_border_style')), 'double'); ?>>Double</option>
													</select>
													<p class="description"><?php esc_html_e(' Form border style,', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_form_border_radius'><?php esc_html_e('Form Border Radius', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_form_border_radius' name='lps_login_form_border_radius' min='0' max='50' value='<?php echo absint(get_option('lps_login_form_border_radius')); ?>' oninput="this.form.amountInput4.value=this.value" />
													<input type="number" name="amountInput4" min="0" max="50" value='<?php echo absint(get_option('lps_login_form_border_radius')); ?>' size='4' oninput="this.form.lps_login_form_border_size.value=this.value" />px
													<p class="description"><?php esc_html_e(' Form border radius', 'login-page-styler'); ?></p>
												</td>
											</tr>


											<tr valign='top'>
												<td><label for='lps_login_form_width'><?php esc_html_e('Form Width', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_form_width' name='lps_login_form_width' min='320' max='1000' value='<?php echo esc_attr(max(320, absint(get_option('lps_login_form_width')))); ?>' oninput="this.form.amountInputFw.value=this.value" />
													<input type="number" name="amountInputFw" min="320" max="1000" value='<?php echo esc_attr(max(320, absint(get_option('lps_login_form_width')))); ?>' size='4' oninput="this.form.lps_login_form_width.value=this.value" />px
													<p class='description'> <?php esc_html_e('Default width for form is 320', 'login-page-styler'); ?></p>
												</td>
											</tr>

											</table>
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section3fields" name="accordion">
									<label class="accordion-label" for="section3fields">Fields &amp; Labels</label>
									<div class="accordion-content">
										<table class="form-table">
<tr valign='top'>
												<td><label for='lps_login_label_color'><?php esc_html_e('Label Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_label_color' name='lps_login_label_color' value='<?php echo esc_attr(get_option('lps_login_label_color')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_label_size'><?php esc_html_e('Label Size', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_label_size' name='lps_login_label_size' min='14' max='30' value='<?php echo esc_attr(max(14, absint(get_option('lps_login_label_size')))); ?>' oninput="this.form.amountInput.value=this.value" />
													<input type="number" name="amountInput" min="14" max="30" value='<?php echo esc_attr(max(14, absint(get_option('lps_login_label_size')))); ?>' size='4' oninput="this.form.lps_login_label_size.value=this.value" />px
												</td>
											</tr>

											<tr>
												<td><label for='lps_login_form_input_color'><?php esc_html_e('Input Field Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_input_color' name='lps_login_form_input_color' value='<?php echo esc_attr(get_option('lps_login_form_input_color')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_form_input_color_opacity'><?php esc_html_e('Input Field Opacity', 'login-page-styler'); ?></label><br />
													<input type='range' step='0.01' min='0' max='1' id='lps_login_form_input_color_opacity' name='lps_login_form_input_color_opacity' value='<?php echo esc_attr(get_option('lps_login_form_input_color_opacity', '1')); ?>' oninput="this.form.amountInputInputOpacity.value=this.value" />
													<input type='number' step='0.01' min='0' max='1' name='amountInputInputOpacity' value='<?php echo esc_attr(get_option('lps_login_form_input_color_opacity', '1')); ?>' size='4' oninput="this.form.lps_login_form_input_color_opacity.value=this.value" />
													<p class='description'><?php esc_html_e(' Input-Field Opacity. This option make input field transparent', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr>
												<td><label for='lps_login_form_input_text_color'><?php esc_html_e('Input Field Text Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_input_text_color' name='lps_login_form_input_text_color' value='<?php echo esc_attr(get_option('lps_login_form_input_text_color')); ?>' />
												</td>
											</tr>


											<tr valign='top'>
												<td><label for='lps_login_form_input_feild_border_color'><?php esc_html_e('Input Field Border Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_login_form_input_feild_border_color' name='lps_login_form_input_feild_border_color' value='<?php echo esc_attr(get_option('lps_login_form_input_feild_border_color')); ?>' />
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_login_form_input_border_size"><?php esc_html_e('Input Field Border Style', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_form_input_border_size' name='lps_login_form_input_border_size' min='0' max='10' value='<?php echo absint(get_option('lps_login_form_input_border_size')); ?>' oninput="this.form.amountInput5.value=this.value" />
													<input type="number" name="amountInput5" min="0" max="10" value='<?php echo absint(get_option('lps_login_form_input_border_size')); ?>' size='4' oninput="this.form.lps_login_form_border_size.value=this.value" />px
													<p class="description"><?php esc_html_e('Input Field border size.', 'login-page-styler'); ?></p>


													<label for="lps_login_form_input_border_style"></label><br />
													<select name='lps_login_form_input_border_style' id='lps_login_form_input_border_style'>
														<option value='' <?php selected(get_option('lps_login_form_input_border_style',''), ''); ?>>Use Template Default</option>
<option value='none' <?php selected(sanitize_key(get_option('lps_login_form_input_border_style')), 'none'); ?>>None</option>
														<option value='solid' <?php selected(sanitize_key(get_option('lps_login_form_input_border_style')), 'solid'); ?>>Solid</option>
														<option value='dashed' <?php selected(sanitize_key(get_option('lps_login_form_input_border_style')), 'dashed'); ?>>Dashed</option>
														<option value='dotted' <?php selected(sanitize_key(get_option('lps_login_form_input_border_style')), 'dotted'); ?>>Dotted</option>
														<option value='double' <?php selected(sanitize_key(get_option('lps_login_form_input_border_style')), 'double'); ?>>Double</option>
													</select>
													<p class="description"><?php esc_html_e('Input Field border style.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_form_input_feild_border_radius'><?php esc_html_e('Input Field Border Radius', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_form_input_feild_border_radius' name='lps_login_form_input_feild_border_radius' min='0' max='20' value='<?php echo absint(get_option('lps_login_form_input_feild_border_radius')); ?>' oninput="this.form.amountInput7.value=this.value" />
													<input type="number" name="amountInput7" min="0" max="20" value='<?php echo absint(get_option('lps_login_form_input_feild_border_radius')); ?>' size='4' oninput="this.form.lps_login_form_input_feild_border_radius.value=this.value" />px
													<p class="description"><?php esc_html_e('Change Login form input-field border radius.', 'login-page-styler'); ?></p>
												</td>
											</tr>



											<tr valign='top'>
												<td><label for='lps_login_remember_label_size'><?php esc_html_e('Remember Me Label Size', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_login_remember_label_size' name='lps_login_remember_label_size' min='12' max='25' value='<?php echo esc_attr(max(12, absint(get_option('lps_login_remember_label_size')))); ?>' oninput="this.form.amountInput2.value=this.value" />
													<input type="number" name="amountInput2" min="12" max="25" value='<?php echo esc_attr(max(12, absint(get_option('lps_login_remember_label_size')))); ?>' size='4' oninput="this.form.lps_login_remember_label_size.value=this.value" />px
												</td>
											</tr>


											</table>
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section3shadow" name="accordion">
									<label class="accordion-label" for="section3shadow">Form Box Shadow</label>
									<div class="accordion-content">
										<table class="form-table">
<tr>
												<td><label for='lps_box_shadow_color'><?php esc_html_e('Form Box Shadow Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_box_shadow_color' name='lps_box_shadow_color' value='<?php echo esc_attr(get_option('lps_box_shadow_color', '#000000')); ?>' />
													<p class='description'><?php esc_html_e('Set the color for the login box shadow.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_box_shadow_opacity'><?php esc_html_e('Form Box Shadow Opacity', 'login-page-styler'); ?></label><br />
													<input type='range' step='0.1' min='0' max='1' id='lps_box_shadow_opacity' name='lps_box_shadow_opacity' value='<?php echo esc_attr(get_option('lps_box_shadow_opacity', '0.3')); ?>' oninput="this.form.amountInputBSOpacity.value=this.value" />
													<input type='number' step='0.1' min='0' max='1' name='amountInputBSOpacity' value='<?php echo esc_attr(get_option('lps_box_shadow_opacity', '0.3')); ?>' size='4' oninput="this.form.lps_box_shadow_opacity.value=this.value" />
													<p class='description'><?php esc_html_e('Set the opacity for the login box shadow (between 0 and 1).', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_box_shadow_horizontal'><?php esc_html_e('Form Box Shadow Horizontal Offset', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_box_shadow_horizontal' name='lps_box_shadow_horizontal' min='-20' max='20' value='<?php echo esc_attr(get_option('lps_box_shadow_horizontal')); ?>' oninput="this.form.amountInputBSH.value=this.value" />
													<input type="number" name="amountInputBSH" min="-20" max="20" value='<?php echo esc_attr(get_option('lps_box_shadow_horizontal')); ?>' size='4' oninput="this.form.lps_box_shadow_horizontal.value=this.value" />
													<p class="description"><?php esc_html_e('Set the horizontal offset for the login box shadow.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_box_shadow_vertical'><?php esc_html_e('Form Box Shadow Vertical Offset', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_box_shadow_vertical' name='lps_box_shadow_vertical' min='-20' max='20' value='<?php echo absint(get_option('lps_box_shadow_vertical')); ?>' oninput="this.form.amountInputBSV.value=this.value" />
													<input type="number" name="amountInputBSV" min="-20" max="20" value='<?php echo absint(get_option('lps_box_shadow_vertical')); ?>' size='4' oninput="this.form.lps_box_shadow_vertical.value=this.value" />
													<p class="description"><?php esc_html_e('Set the vertical offset for the login box shadow.', 'login-page-styler'); ?></p>
												</td>
											</tr>


											<tr valign='top'>
												<td>
													<label for='lps_box_shadow_blur'><?php esc_html_e('Form Box Shadow Blur Radius', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_box_shadow_blur' name='lps_box_shadow_blur' min='0' max='100' value='<?php echo absint(get_option('lps_box_shadow_blur', '10')); ?>' oninput="this.form.amountInputBSBlur.value=this.value" />
													<input type='number' name='amountInputBSBlur' min='0' max='100' value='<?php echo absint(get_option('lps_box_shadow_blur', '10')); ?>' size='4' oninput="this.form.lps_box_shadow_blur.value=this.value" />
													<p class='description'><?php esc_html_e('Set the blur radius for the login box shadow.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>

												<td>
													<label for='lps_box_shadow_spread'><?php esc_html_e('Form Box Shadow Spread Radius', 'login-page-styler'); ?></label><br />
													<input type='range' id='lps_box_shadow_spread' name='lps_box_shadow_spread' min='0' max='100' value='<?php echo absint(get_option('lps_box_shadow_spread', '0')); ?>' oninput="this.form.amountInputBSSpread.value=this.value" />
													<input type='number' name='amountInputBSSpread' min='0' max='100' value='<?php echo absint(get_option('lps_box_shadow_spread', '0')); ?>' size='4' oninput="this.form.lps_box_shadow_spread.value=this.value" />
													<p class='description'>
														<?php esc_html_e('Set the spread radius for the login box shadow.', 'login-page-styler'); ?>
													</p>
												</td>
											</tr>



										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section4" name="accordion">
									<label class="accordion-label" for="section4">Google Fonts</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td><label for="lps_gfontlab"><?php esc_html_e('Google font Label ', 'login-page-styler'); ?></label><br />
													<input name="lps_gfontlab" id="lps_gfontlab" class="lps_labfont" type="text" value="<?php echo esc_attr(get_option('lps_gfontlab')); ?>" />
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_gfont_inputtext"><?php esc_html_e('Google font Input Field Text ', 'login-page-styler'); ?></label><br />
													<input name="lps_gfont_inputtext" id="lps_gfont_inputtext" class="lps_inputfont" type="text" value="<?php echo esc_attr(get_option('lps_gfont_inputtext')); ?>" />
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_gfontlink"><?php esc_html_e('Google font Navigation Links ', 'login-page-styler'); ?></label><br />
													<input name="lps_gfontlink" id="lps_gfontlink" class="lps_linkfont" type="text" value="<?php echo esc_attr(get_option('lps_gfontlink')); ?>" />
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_gfontmsg"><?php esc_html_e('Google font Error Messages ', 'login-page-styler'); ?></label><br />
													<input name="lps_gfontmsg" id="lps_gfontmsg" class="lps_msgfont" type="text" value="<?php echo esc_attr(get_option('lps_gfontmsg')); ?>" />
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_gfontbtn"><?php esc_html_e('Google font Button ', 'login-page-styler'); ?></label><br />
													<input name="lps_gfontbtn" id="lps_gfontbtn" class="lps_btnfont" type="text" value="<?php echo esc_attr(get_option('lps_gfontbtn')); ?>" />
												</td>
											</tr>
										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section5" name="accordion">
									<label class="accordion-label" for="section5">Button</label>
									<div class="accordion-content">

										<table class="form-table">

											<tr valign='top'>
												<td><label for='lps_login_button_color'><?php esc_html_e('Login Button Color', 'login-page-styler'); ?></label></br>
													<input type='text' class='color_picker' id='lps_login_button_color' name='lps_login_button_color' value='<?php echo esc_attr(get_option('lps_login_button_color')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_button_color_hover'><?php esc_html_e('Login Button Color Hover', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_button_color_hover' name='lps_login_button_color_hover' value='<?php echo esc_attr(get_option('lps_login_button_color_hover')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_button_text_color'><?php esc_html_e('Login Button Text Color', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_button_text_color' name='lps_login_button_text_color' value='<?php echo esc_attr(get_option('lps_login_button_text_color')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_button_text_color_hover'><?php esc_html_e('Login Button Text Color Hover', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_button_text_color_hover' name='lps_login_button_text_color_hover' value='<?php echo esc_attr(get_option('lps_login_button_text_color_hover')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_button_border_color'><?php esc_html_e('Login Button Border Color', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_button_border_color' name='lps_login_button_border_color' value='<?php echo esc_attr(get_option('lps_login_button_border_color')); ?>' />
												</td>
											</tr>


											<tr valign='top'>
												<td><label for='lps_login_button_border_color_hover'><?php esc_html_e('Login Button Border Color Hover', 'login-page-styler'); ?></label></br>
													<input type='text' class='color_picker' id='lps_login_button_border_color_hover' name='lps_login_button_border_color_hover' value='<?php echo esc_attr(get_option('lps_login_button_border_color_hover')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_button_border_radius'><?php esc_html_e('Login Button Border Radius', 'login-page-styler'); ?></label>
													<input type='range' id='lps_login_button_border_radius' name='lps_login_button_border_radius' min='0' max='30' value='<?php echo absint(get_option('lps_login_button_border_radius')); ?>' oninput="this.form.amountInput6.value=this.value" />
													<input type="number" name="amountInput6" min="0" max="30" value='<?php echo absint(get_option('lps_login_button_border_radius')); ?>' size='4' oninput="this.form.lps_login_button_border_radius.value=this.value" />
													<p class="description"><?php esc_html_e('Slide to change login button border radius .', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_button_size'><?php esc_html_e('Login Button Size', 'login-page-styler'); ?></label>
													<input type='range' step='' min='68' max='272' id='lps_login_button_size' name='lps_login_button_size' value='<?php echo esc_attr(max(68, absint(get_option('lps_login_button_size')))); ?>' oninput="this.form.amountInputButtonsize.value=this.value" />
													<input type='number' step='' min='68' max='400' name='amountInputButtonsize' value='<?php echo esc_attr(max(68, absint(get_option('lps_login_button_size')))); ?>' size='4' oninput="this.form.lps_login_button_size.value=this.value" />px
													<p class='description'><?php esc_html_e('Change login button size .68px is default button size 272px matches the size of inputfield .', 'login-page-styler'); ?></p>
												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section6" name="accordion">
									<label class="accordion-label" for="section6">Navigation Links</label>
									<div class="accordion-content">

										<table class="form-table">

											<tr vlaign='top'>
												<td><label for='lps_login_nav_color'><?php esc_html_e('Navigation Links Color', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_color' name='lps_login_nav_color' value='<?php echo esc_attr(get_option('lps_login_nav_color')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_nav_hover_color'><?php esc_html_e('Navigation Hover Links Color', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_hover_color' name='lps_login_nav_hover_color' value='<?php echo esc_attr(get_option('lps_login_nav_hover_color')); ?>' />
												</td>
											</tr>
											
											<tr vlaign='top'>
												<td><label for='lps_login_nav_bgcolor'><?php esc_html_e('Navigation Links Background Color', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_bgcolor' name='lps_login_nav_bgcolor' value='<?php echo esc_attr(get_option('lps_login_nav_bgcolor')); ?>' />
												</td>
											</tr>

											<tr vlaign='top'>
												<td><label for='lps_login_nav_bgcolor_hover'><?php esc_html_e('Navigation Background Color Hover', 'login-page-styler'); ?></label>
													<input type='text' class='color_picker' id='lps_login_nav_bgcolor_hover' name='lps_login_nav_bgcolor_hover' value='<?php echo esc_attr(get_option('lps_login_nav_bgcolor_hover')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_login_nav_size'><?php esc_html_e('Navigation Link Size', 'login-page-styler'); ?></label>
													<input type='range' id='lps_login_nav_size' name='lps_login_nav_size' min='13' max='20' value='<?php echo esc_attr(max(13, absint(get_option('lps_login_nav_size')))); ?>' oninput="this.form.amountInput8.value=this.value" />
													<input type="number" name="amountInput8" min="13" max="20" value='<?php echo esc_attr(max(13, absint(get_option('lps_login_nav_size')))); ?>' size='4' oninput="this.form.lps_login_nav_size.value=this.value" />
												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section7" name="accordion">
									<label class="accordion-label" for="section7">Login Footer</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td><label for="lps_login_copyright"><?php esc_html_e(' Copyright / Add your company', 'login-page-styler'); ?></label><br />
													&copy <?php echo esc_attr(gmdate('Y')); ?> <input type="text" id="lps_login_copyright" name="lps_login_copyright" size="25" placeholder="Your website or Company name " value="<?php echo esc_attr(get_option('lps_login_copyright')); ?>" />All rights reserved.
													<p class="description"><?php esc_html_e(' Shows Copyright in the footer of login page just add your company name  ', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr>
												<td><label for='lps_copyright_color'><?php esc_html_e('Copyright Text Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_copyright_color' name='lps_copyright_color' value='<?php echo esc_attr(get_option('lps_copyright_color')); ?>' />
													<p class='description'><?php esc_html_e('Set the color for the Copyright Footor text.', 'login-page-styler'); ?></p>
												</td>
											</tr>

											<tr valign="top">
												<td><label for="lps_gfont_copyright"><?php esc_html_e('Google font for Copyright ', 'login-page-styler'); ?></label>
													<input name="lps_gfont_copyright" id="lps_gfont_copyright" class="lps_copyrightfont" type="text" value="<?php echo esc_attr(get_option('lps_gfont_copyright')); ?>" />
												</td>
											</tr>

										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section8" name="accordion">
									<label class="accordion-label" for="section8">Error Notice Success Mesages</label>
									<div class="accordion-content">

										<table class="form-table">

											<tr>
												<td>
													<label for='lps_notice_textcolor'><?php esc_html_e('Error, Notice Message Text Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_notice_textcolor' name='lps_notice_textcolor' value='<?php echo esc_attr(get_option('lps_notice_textcolor')); ?>' />
												</td>
											</tr>

											<tr>
												<td>
													<label for='lps_notice_bgcolor'><?php esc_html_e('Error, Notice Message Background Color', 'login-page-styler'); ?></label><br />
													<input type='text' class='color_picker' id='lps_notice_bgcolor' name='lps_notice_bgcolor' value='<?php echo esc_attr(get_option('lps_notice_bgcolor')); ?>' />
												</td>
											</tr>

											<tr valign='top'>
												<td><label for='lps_notice_bgcolor_opacity'><?php esc_html_e('Message Background Opacity', 'login-page-styler'); ?></label><br />
													<input type='range' step='0.1' min='0' max='1' id='lps_notice_bgcolor_opacity' name='lps_notice_bgcolor_opacity' value='<?php echo esc_attr(get_option('lps_notice_bgcolor_opacity', '1')); ?>' oninput="this.form.amountInputMessageOpacity.value=this.value" />
													<input type='number' step='0.1' min='0' max='1' name='amountInputMessageOpacity' value='<?php echo esc_attr(get_option('lps_notice_bgcolor_opacity', '1')); ?>' size='4' oninput="this.form.lps_notice_bgcolor_opacity.value=this.value" />
													<p class='description'><?php esc_html_e('Select opacity for Error , Notice, Success Mesages', 'login-page-styler'); ?></p>
												</td>
											</tr>


										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section9" name="accordion">
									<label class="accordion-label" for="section9">Slider Background</label>
									<div class="accordion-content">
										<table class="form-table">
											<tr valign="top">
												<td>
													<label for="lps_body_bg_slideshow">
														<?php esc_html_e('Login Background Slider', 'login-page-styler'); ?>
													</label></br>
													<?php
													// Get the array of background images.
													$background_images = get_option('lps_body_bg_slideshow', array());
                                                    if (!is_array($background_images)) { $background_images = array(); }
											// Older settings or a Free-tier reset can store an empty string.
											// Normalize before indexing so the demo never reads string characters as URLs.
											$background_images = is_array($background_images) ? $background_images : array();

													for ($i = 0; $i < 4; $i++) {
														$image_url = isset($background_images[$i]) ? esc_url($background_images[$i]) : '';
													?>
														<label for="lps_body_bg_slideshow<?php echo esc_attr($i + 1); ?>">
															Slider Image <?php echo esc_attr($i + 1); ?>:
															<input id="lps_body_bg_slideshow<?php echo esc_attr($i + 1); ?>" type="text" name="lps_body_bg_slideshow[]" value="<?php echo esc_url($image_url); ?>" size="25" />
															<input class="lps-upload-button button" type="button" value="Upload Image" />
														</label>
														<br>
													<?php
													}
													?>
													<p class='description'>
														<?php esc_html_e('Enter background image URLs for each image (one per line)', 'login-page-styler'); ?>
													</p>
												</td>
											</tr>


											<tr valign="top">
												<td>
													<label for="lps_slideshow_animation_style">
														<?php esc_html_e('Slideshow Animation Style', 'login-page-styler'); ?>
													</label><br />
													<select name='lps_slideshow_animation_style' id='lps_slideshow_animation_style'>
														<option value='fade' <?php selected(sanitize_text_field(get_option('lps_slideshow_animation_style')), 'fade'); ?>>Fade</option>
														<option value='slide' <?php selected(sanitize_text_field(get_option('lps_slideshow_animation_style')), 'slide'); ?>>Slide</option>
														<option value='flip' <?php selected(sanitize_text_field(get_option('lps_slideshow_animation_style')), 'flip'); ?>>Flip</option>
														<option value='cube' <?php selected(sanitize_text_field(get_option('lps_slideshow_animation_style')), 'cube'); ?>>Cube</option>
													</select>
													<p class="description">
														<?php esc_html_e('Select animation for the slides', 'login-page-styler'); ?>
													</p>
													<p class="description">
													</p>
												</td>
											</tr>

											<tr valign='top'>
												<td>
													<label for='lps_slideshow_time'>
														<?php esc_html_e('Slideshow Time', 'login-page-styler'); ?>
													</label><br />
													<input type='range' id='lps_slideshow_time' name='lps_slideshow_time' min='1' max='10' value='<?php echo absint(get_option('lps_slideshow_time')); ?>' oninput="this.form.amountInputSst.value=this.value" />
													<input type="number" name="amountInputSst" min="1" max="10" value='<?php echo esc_attr(max(1, absint(get_option('lps_slideshow_time')))); ?>' size='4' oninput="this.form.lps_slideshow_time.value=this.value" />
													<p class='description'>
														<?php esc_html_e('Change the time of Slideshow', 'login-page-styler'); ?>
													</p>
													<p class="description">
													</p>
												</td>
											</tr>
											<!-- Add more content if needed -->
										</table>
									</div>
								</div>

								<div class="accordion">
									<input type="checkbox" id="section10" name="accordion">
									<label class="accordion-label" for="section10">Custom Css</label>
									<div class="accordion-content">

										<table class="form-table">
											<tr valign="top">
												<td><label for="lps_login_custom_css"><?php esc_html_e('Custom CSS', 'login-page-styler'); ?></label>
													<textarea cols="26" rows="7" id="lps_user_custom_css" name="lps_user_custom_css" placeholder="/* Add your own CSS here (optional) */"><?php echo esc_textarea(get_option('lps_user_custom_css', '')); ?></textarea>
                                                    <textarea id="lps_login_custom_css" name="lps_login_custom_css" hidden aria-hidden="true"><?php echo esc_textarea(get_option('lps_login_custom_css', '')); ?></textarea>
													<p class='description'> <?php esc_html_e('Add styling inside this text area.', 'login-page-styler'); ?></p>
												</td>
											</tr>
										</table>
										<!-- Add more content if needed -->
									</div>
								</div>

								
<!-- Repeat the structure for other accordion sections -->

<div class="lps-settings-actions"><div class="lps-settings-actions-copy"><strong>Ready to publish?</strong><span>Save your changes or reset all styling settings.</span></div><div class="lps-settings-actions-buttons"><p class='lpsresetbutton'><button type="button" id="lps-reset-button">Reset Settings</button></p>

								<script>
									document.getElementById('lps-reset-button').addEventListener('click', function() {
										if (confirm('Are you sure you want to reset settings to defaults?')) {
											fetch(ajaxurl, {
													method: 'POST',
													headers: {
														'Content-Type': 'application/x-www-form-urlencoded'
													},
													body: new URLSearchParams({
														'action': 'lps_reset_settings',
														'nonce': '<?php echo esc_js(wp_create_nonce('lps_reset_settings')); ?>'
													})
												})
												.then(response => response.json())
												.then(data => {
													if (data.success) {
														alert(data.data); // Success message
														location.reload(); // Reload the page to reflect changes
													} else {
														alert('Failed to reset settings. Please try again.');
													}
												})
												.catch(error => {
													console.error('Error:', error);
													alert('An error occurred. Please try again.');
												});
										}
									});
								</script>
								<p class="submit">
									<input type="submit" id="lpsbutton-primary-main" class="button-primary" value="<?php esc_html_e('Save Changes', 'login-page-styler'); ?>" />
								</p>
								</div></div>

							</div>

							<div class="preview-container">
								<div class="independent-preview">
									<div class="lps-preview-heading">
										<div><strong>Live preview</strong><span>Changes appear here as you customize your login page.</span></div>
										<div class="lps-preview-actions" role="group" aria-label="Preview device size">
											<button type="button" class="button lps-device-button is-active" data-preview-size="desktop" aria-pressed="true">Desktop</button>
											<button type="button" class="button lps-device-button" data-preview-size="tablet" aria-pressed="false">Tablet</button>
											<button type="button" class="button lps-device-button" data-preview-size="mobile" aria-pressed="false">Mobile</button>
											<button type="button" class="button lps-preview-refresh" id="lps-preview-refresh">Refresh</button>
                                            <button type="button" class="button" id="lps-preview-fullscreen" aria-pressed="false">Fullscreen</button>
										</div>
									</div>
									<div class="lps-preview-stage">
										<iframe id="lps-iframe" title="Login page live preview" src="<?php echo esc_url(add_query_arg('lps_preview_version', '9.9.35.12', plugin_dir_url(__FILE__) . 'lpsnewLogin.php')); ?>" frameborder="0" width="100%" height="800px"></iframe>
									</div>
								</div>
							</div>

						</div>



						<h3 style="text-align: center;"><?php esc_html_e('Get Premium Support 24/7 Through E-mail : ziaimtiaz21@gmail.com ', 'login-page-styler'); ?></h3>
						<h3 style="text-align: center;"><?php esc_html_e('If you want us to Style your login page or want any new Feature Email us : ziaimtiaz21@gmail.com', 'login-page-styler'); ?></h3>
						<p style="text-align: center;"><b><a href="https://wordpress.org/support/plugin/login-page-styler/reviews/" target="_blank">Leave a review</a></b> on WordPress.org.</p>

				</div>
			</div>

			<div id="content3">
				<div class='wrap'>
					<h1><?php esc_html_e('Google reCAPTCHA', 'login-page-styler'); ?></h1>
                    <p class="description">Choose v2 checkbox or v3 score-based protection for login, registration and password recovery. Existing sites continue using v2 unless changed.</p>
                    <?php if ((get_option('lps_login_captcha') || get_option('lps_reg_captcha') || get_option('lps_lost_captcha')) && (!get_option('rs_site_key') || !get_option('rs_private_key'))) : ?>
                    <div class="notice notice-warning inline"><p><strong>reCAPTCHA is not active:</strong> Enter both a site key and a secret key. Protection remains disabled until both are provided, to avoid locking out users.</p></div>
                    <?php endif; ?>
					<table class="form-table">
						<p>You need to <a href="https://www.google.com/recaptcha/admin" rel="external">Register your domain with Google reCAPTCHA</a> and get a matching Site Key and Secret Key for your chosen reCAPTCHA version.</p>

                        <tr><th scope="row"><label for="lps_recaptcha_version">reCAPTCHA version</label></th>
                        <td><select name="lps_recaptcha_version" id="lps_recaptcha_version">
                            <option value="v2" <?php selected(get_option('lps_recaptcha_version', 'v2'), 'v2'); ?>>v2 — Checkbox</option>
                            <option value="v3" <?php selected(get_option('lps_recaptcha_version', 'v2'), 'v3'); ?>>v3 — Invisible score-based</option>
                        </select><p class="description">Use keys created for the selected version. Existing v2 keys will not work with v3.</p></td></tr>
                        <tr class="lps-v3-option"><th scope="row"><label for="lps_recaptcha_v3_threshold">v3 minimum score</label></th>
                        <td><input type="range" min="0.1" max="1" step="0.1" id="lps_recaptcha_v3_threshold" value="<?php echo esc_attr(get_option('lps_recaptcha_v3_threshold', '0.5')); ?>" oninput="document.getElementById('lps-v3-score').textContent=this.value" name="lps_recaptcha_v3_threshold"> <strong id="lps-v3-score"><?php echo esc_html(get_option('lps_recaptcha_v3_threshold', '0.5')); ?></strong><p class="description">0.5 is a reasonable starting point. Higher scores are stricter and may reject legitimate visitors.</p></td></tr>
                        <script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('lps_recaptcha_version');if(!s)return;function update(){document.querySelectorAll('.lps-v3-option').forEach(function(el){el.style.display=s.value==='v3'?'':'none'});document.querySelectorAll('.lps-v2-option').forEach(function(el){el.style.display=s.value==='v2'?'':'none'});}s.addEventListener('change',update);update();});</script>

						<tr valign="top">
							<th scope="row"><?php esc_html_e('Site Key', 'login-page-styler'); ?></th>
							<td><label for="rs_site_key">
									<input type="text" id="rs_site_key" size="50" name="rs_site_key" value="<?php echo esc_attr(get_option('rs_site_key')); ?>" />
									<p class="description"><?php esc_html_e('Enter Site Key ', 'login-page-styler'); ?></p>
								</label></td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php esc_html_e('Secret Key', 'login-page-styler'); ?></th>
							<td><label for="rs_private_key">
									<input type="password" autocomplete="new-password" id="rs_private_key" size="50" name="rs_private_key" value="<?php echo esc_attr(get_option('rs_private_key')); ?>" />
									<p class="description"><?php esc_html_e('Keep this key private. It is only sent server-side to Google.', 'login-page-styler'); ?></p>
								</label></td>
						</tr>

						<tr valign='top'>
							<th scope='row'><?php esc_html_e('Enable Google ReCaptcha On Login', 'login-page-styler'); ?></th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" name="lps_login_captcha" class="onoffswitch-checkbox" id="myonoffswitchcap" value='1' <?php checked(1, absint(get_option('lps_login_captcha'))); ?> />
									<label class="onoffswitch-label" for="myonoffswitchcap">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
							</td>
						</tr>


						<tr valign='top'>
							<th scope='row'><?php esc_html_e('Enable Google ReCaptcha On Registration', 'login-page-styler'); ?></th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" name="lps_reg_captcha" class="onoffswitch-checkbox" id="myonoffswitchcap1" value='1' <?php checked(1, absint(get_option('lps_reg_captcha'))); ?> />
									<label class="onoffswitch-label" for="myonoffswitchcap1">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
							</td>
						</tr>

						<tr valign='top'>
							<th scope='row'><?php esc_html_e('Enable Google ReCaptcha On Lost Password', 'login-page-styler'); ?></th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" name="lps_lost_captcha" class="onoffswitch-checkbox" id="myonoffswitchcap2" value='1' <?php checked(1, absint(get_option('lps_lost_captcha'))); ?> />
									<label class="onoffswitch-label" for="myonoffswitchcap2">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
							</td>
						</tr>


						<tr valign="top" class="lps-v2-option">
							<th scope='row'><?php esc_html_e('Theme for recaptcha', 'login-page-styler'); ?></th>
							<td><label for="lps_recaptcha_theme">
									<select name='lps_recaptcha_theme' id='lps_recaptcha_theme'>
										<option value='light' <?php selected(sanitize_text_field(get_option('lps_recaptcha_theme')), 'light'); ?>>Light</option>
										<option value='dark' <?php selected(sanitize_text_field(get_option('lps_recaptcha_theme')), 'dark'); ?>>Dark</option>
									</select>
									<p class="description">
										<?php esc_html_e('Select Theme for Google recaptcha', 'login-page-styler'); ?>
									</p>

								</label>
							</td>
						</tr>
					</table>
					
					<div id="headings-data">
						<div id="hed3">
							<h3><?php esc_html_e(' Math Captcha  ', 'login-page-styler'); ?></h3>
						</div>
						<table class="form-table">
							
							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Show Math Captcha On Login', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" name="lps_show_math_captcha" class="onoffswitch-checkbox" id="myonoffswitchMathCaptcha" value='1' <?php checked(1, absint(get_option('lps_show_math_captcha'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitchMathCaptcha">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>
						</table>
					</div>
					
					<p class="submit">
						<input type="submit" id="lpsbutton-primary" class="button-primary" value="<?php esc_html_e('Save Changes', 'login-page-styler'); ?>" />
					</p>

				</div>

			</div>


			<div id="content4">
				<div class="wrap">
<?php lps_free_tab_notice('Login/Logout Menu Item'); ?>

					<h1><?php esc_html_e('Login Logout Menu Item', 'login-page-styler'); ?></h1>
					<table class="form-table">
						<tr valign='top'>
							<th scope='row'><?php esc_html_e('Show Login/Logout In Menu', 'login-page-styler'); ?></th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitchmenu" value='1' <?php checked(1, absint(lps_free_demo_get_option('lps_loginout_menu'))); ?> />
									<label class="onoffswitch-label" for="myonoffswitchmenu">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description"><?php esc_html_e('This feature will show a login/logout menu item in your sites menu.', 'login-page-styler'); ?></p>
							</td>
						</tr>
					</table>

					<div class="lps-menu-options" style="max-width:760px;margin:16px 0 24px;padding:18px 20px;background:#f7f9fc;border:1px solid #e1e7f0;border-radius:10px;">
                        <h3 style="margin-top:0;"><?php esc_html_e('Menu link settings', 'login-page-styler'); ?></h3>
                        <p class="description"><?php esc_html_e('Choose where the automatic Login / Log Out link appears. Available locations depend on your theme.', 'login-page-styler'); ?></p>
                        <table class="form-table" role="presentation">
                            <tr><th scope="row"><label for="lps_menu_location"><?php esc_html_e('Menu location', 'login-page-styler'); ?></label></th><td>
                                <select id="lps_menu_location">
                                    <?php
                                    $selected_location = lps_free_demo_get_option('lps_menu_location', 'primary');
                                    $menu_locations = get_registered_nav_menus();
                                    if (!isset($menu_locations['primary'])) {
                                        $menu_locations = array('primary' => __('Primary (legacy default)', 'login-page-styler')) + $menu_locations;
                                    }
                                    foreach ($menu_locations as $slug => $description) {
                                        echo '<option value="' . esc_attr($slug) . '" ' . selected($selected_location, $slug, false) . '>' . esc_html($description) . '</option>';
                                    }
                                    ?>
                                    <option value="all" <?php selected($selected_location, 'all'); ?>><?php esc_html_e('All displayed menus', 'login-page-styler'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('For block-based navigation, theme support may vary.', 'login-page-styler'); ?></p>
                            </td></tr>
                            <tr><th scope="row"><label for="lps_login_menu_label"><?php esc_html_e('Login link text', 'login-page-styler'); ?></label></th><td><input type="text" class="regular-text" id="lps_login_menu_label" value="<?php echo esc_attr(lps_free_demo_get_option('lps_login_menu_label', '')); ?>" placeholder="Log In" /></td></tr>
                            <tr><th scope="row"><label for="lps_logout_menu_label"><?php esc_html_e('Logout link text', 'login-page-styler'); ?></label></th><td><input type="text" class="regular-text" id="lps_logout_menu_label" value="<?php echo esc_attr(lps_free_demo_get_option('lps_logout_menu_label', '')); ?>" placeholder="Log Out" /></td></tr>
                        </table>
                    </div>

                    <div id="headings-data">
						<div id="hed3">
							<h3><?php esc_html_e(' Login Short Code  ', 'login-page-styler'); ?></h3>
						</div>
						<table class="form-table">
							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Show Login Button', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitchWB" value='1' <?php checked(1, absint(lps_free_demo_get_option('lps_login_widgetButton'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitchWB">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>


							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Show Register Button', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitchWB2" value='1' <?php checked(1, absint(lps_free_demo_get_option('lps_register_widgetButton'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitchWB2">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>



							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Show Lost Password Button', 'login-page-styler'); ?></th>
								<td>
									<div class="onoffswitch">
										<input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitchWB3" value='1' <?php checked(1, absint(lps_free_demo_get_option('lps_lostpassword_widgetButton'))); ?> />
										<label class="onoffswitch-label" for="myonoffswitchWB3">
											<span class="onoffswitch-inner"></span>
											<span class="onoffswitch-switch"></span>
										</label>
									</div>
								</td>
							</tr>

							<tr valign='top'>
								<th scope='row'><?php esc_html_e('Login ShortCode', 'login-page-styler'); ?></th>
								<td><label for='lps_login_shortcode'>
										<p class='description'><?php esc_html_e('[lps_login_widget]', 'login-page-styler'); ?></p>
										<p class="description"><?php esc_html_e('Select button which you want to show then save settings and use the  short code ', 'login-page-styler'); ?></p>
									</label></td>
							</tr>

						</table>
					</div>

					<p class="submit">
						<input type="button" data-lps-tab-demo="save" id="lpsbutton-primary" class="button-primary" value="<?php esc_html_e('Save Changes', 'login-page-styler'); ?>" />
					</p>

				</div>
			</div>


			<div id="content5">
				<div class="wrap">
<?php lps_free_tab_notice('Login URL / Login Redirect'); ?>

					<h1><?php esc_html_e('New Login URL / Admin Login URL', 'login-page-styler'); ?></h1>
					<table class="form-table">
						
					<tr valign="top">
    <th scope="row"><?php esc_html_e('New Login URL', 'login-page-styler'); ?></th>
    <td>
        <label for="lps_new_login_url">
            <?php
            // Get the option and ensure a default value if it doesn't exist yet
            $lps_new_login_url = lps_free_demo_get_option('lps_new_login_url', ''); // Default to empty string if option is not set
            ?>
           <p style="background-color: #FFBA00; width:auto; float:left; margin-top:10px;" ><?php echo esc_url(get_site_url() . "/"); ?></p> <input type="text" id="lps_new_login_url" placeholder="" value="<?php echo esc_attr($lps_new_login_url); ?>" />
            <p class="description"><?php esc_html_e('Enter only a short slug, such as my-login (not a full URL). Save the new address before logging out, and test it in a private window.', 'login-page-styler'); ?></p>
        </label>
    </td>
</tr>

<tr valign="top">
    <th scope="row"><?php esc_html_e('Redirect to , When  old wp-login or wp-admin is visited', 'login-page-styler'); ?></th>
    <td>
        <label for="lps_new_redirection_url">
            <?php
            // Get the option and ensure a default value if it doesn't exist yet
            $lps_new_redirection_url = lps_free_demo_get_option('lps_new_redirection_url', ''); // Default to empty string if option is not set
            ?>
           <p style="background-color: #FFBA00; width:auto; float:left; margin-top:10px;" > <?php echo esc_url(get_site_url() . "/"); ?></p><input type="text" id="lps_new_redirection_url" placeholder="" value="<?php echo esc_attr($lps_new_redirection_url); ?>" />
            <p class="description"><?php esc_html_e('Optional: enter a destination page slug, or 404. This applies to logged-out visitors who use the old login URL. Keep your admin session open while testing.', 'login-page-styler'); ?></p>
        </label>
    </td>
</tr>

										
					</table>
					
					<div id="headings-data">
						<div id="hed3">
							<h3><?php esc_html_e(' Login Redirect / Logout Redirect  ', 'login-page-styler'); ?></h3>
						</div>
						<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php esc_html_e('Redirect user after login', 'login-page-styler'); ?></th>
							<td>
								<label for="lps_redirect_users">
									<select id="lps_redirect_users">
										<option value="" <?php selected(lps_free_demo_get_option('lps_redirect_users', ''), ''); ?>><?php esc_html_e('Default WordPress behavior', 'login-page-styler'); ?></option>
										<?php
										$selected_page = lps_free_demo_get_option('lps_redirect_users');
										$pages         = get_pages();
										foreach ($pages as $page) {
											$option_value = $page->post_name;
											$option_label = esc_html($page->post_title);
											$selected     = selected($selected_page, $page->post_name, false);
										?>
											<option  value='<?php echo esc_attr($option_value); ?>' <?php echo esc_attr($selected); ?>><?php echo esc_html($option_label); ?></option>
										<?php } ?>
									</select>
									<p class="description"><?php esc_html_e('Select page which you want user to land on after login.', 'login-page-styler'); ?></p>
									
								</label>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e('Redirect user after logout', 'login-page-styler'); ?></th>
							<td>
								<label for="lps_redirectafter_users">
									<select id="lps_redirectafter_users">
										<option value="" <?php selected(lps_free_demo_get_option('lps_redirectafter_users', ''), ''); ?>><?php esc_html_e('Default WordPress behavior', 'login-page-styler'); ?></option>
										<?php
										$selected_page = lps_free_demo_get_option('lps_redirectafter_users');
										$pages         = get_pages();
										foreach ($pages as $page) {
											$option_value = esc_attr($page->post_name);
											$option_label = esc_html($page->post_title);
											$selected     = selected($selected_page, $page->post_name, false);
										?>
											<option value='<?php echo esc_attr($option_value); ?>' <?php echo esc_attr($selected); ?>><?php echo esc_html($option_label); ?></option>
										<?php } ?>
									</select>
									<p class="description"><?php esc_html_e('Select page which you want user to land on after logout', 'login-page-styler'); ?></p>
									
								</label>
							</td>
						</tr>
						</table>
					</div>
					
					<p class="submit">
						<input type="button" data-lps-tab-demo="save" id="lpsbutton-primary" class="button-primary" value="<?php esc_html_e('Save Changes', 'login-page-styler'); ?>" />
					</p>

				</div>
			</div>


			<div id="content6">
				<div class="wrap">
<?php lps_free_tab_notice('Login Protected'); ?>
					<h1><?php esc_html_e('Login Protected Pages', 'login-page-styler'); ?></h1>
					<table class="form-table">

						<tr valign='top'>
							<th scope='row'><?php esc_html_e('Enable Private Site', 'login-page-styler'); ?></th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitchprivatesite" value='1' <?php checked(1, absint(lps_free_demo_get_option('lps_enable_private_site'))); ?> />
									<label class="onoffswitch-label" for="myonoffswitchprivatesite">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description"><?php esc_html_e('Require visitors to log in before viewing public pages across your site. Leave this off to protect only the pages selected below.', 'login-page-styler'); ?></p>
							</td>
						</tr>

                        <tr valign="top">
                            <th scope="row"><label for="lps-page-search"><?php esc_html_e('Protected Pages', 'login-page-styler'); ?></label></th>
                            <td>
                                <p class="description"><?php esc_html_e('Check every page that should require login. You can select as many as you need.', 'login-page-styler'); ?></p>
                                <div class="lps-page-picker">
                                    <input type="search" id="lps-page-search" class="regular-text" placeholder="<?php esc_attr_e('Search pages by title…', 'login-page-styler'); ?>" autocomplete="off" />
                                    <div class="lps-page-picker-toolbar">
                                        <strong id="lps-protected-count" aria-live="polite"></strong>
                                        <button type="button" class="button button-small" id="lps-clear-protected"><?php esc_html_e('Clear selection', 'login-page-styler'); ?></button>
                                    </div>
                                    <?php $protected_ids = lps_protection_page_ids(); ?>
                                    <!-- Ensures clearing all checkboxes saves an empty selection. -->
                                    <input type="hidden" value="" />
                                    <div class="lps-page-picker-list" id="lps-protected-pages" role="group" aria-label="<?php esc_attr_e('Pages to protect', 'login-page-styler'); ?>">
                                        <?php foreach (get_pages(array('sort_column' => 'post_title', 'post_status' => 'publish')) as $protected_page) : ?>
                                            <label class="lps-page-picker-option" data-page-title="<?php echo esc_attr(strtolower($protected_page->post_title)); ?>">
                                                <input type="checkbox" value="<?php echo esc_attr($protected_page->ID); ?>" <?php checked(in_array((int) $protected_page->ID, $protected_ids, true)); ?> />
                                                <span><?php echo esc_html($protected_page->post_title); ?></span>
                                                <small>#<?php echo esc_html($protected_page->ID); ?></small>
                                            </label>
                                        <?php endforeach; ?>
                                        <p id="lps-page-no-results" class="description" hidden><?php esc_html_e('No matching pages found.', 'login-page-styler'); ?></p>
                                    </div>
                                </div>
                                <style>
                                    .lps-page-picker{max-width:560px;margin-top:12px;padding:16px;background:#fff;border:1px solid #dcdcde;border-radius:10px;box-sizing:border-box}
                                    .lps-page-picker #lps-page-search{width:100%;max-width:none;margin:0 0 12px;min-height:38px}
                                    .lps-page-picker-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}
                                    .lps-page-picker-list{max-height:290px;overflow-y:auto;border:1px solid #dcdcde;border-radius:7px;background:#fafafa;padding:4px}
                                    .lps-page-picker-option{display:flex;align-items:center;gap:10px;padding:9px 10px;margin:0;border-radius:5px;cursor:pointer;line-height:1.4}
                                    .lps-page-picker-option:hover,.lps-page-picker-option:focus-within{background:#eef3fb}
                                    .lps-page-picker-option input{margin:0;flex:none}
                                    .lps-page-picker-option span{flex:1;min-width:0;overflow-wrap:anywhere}
                                    .lps-page-picker-option small{color:#646970}
                                    .lps-page-picker-option[hidden]{display:none}
                                </style>
                                <script>
                                (function () {
                                    'use strict';
                                    var search = document.getElementById('lps-page-search');
                                    var list = document.getElementById('lps-protected-pages');
                                    var count = document.getElementById('lps-protected-count');
                                    var clear = document.getElementById('lps-clear-protected');
                                    var empty = document.getElementById('lps-page-no-results');
                                    if (!search || !list || !count || !clear) return;
                                    var rows = Array.prototype.slice.call(list.querySelectorAll('.lps-page-picker-option'));
                                    function update() {
                                        var query = search.value.toLocaleLowerCase().trim();
                                        var selected = 0;
                                        var visible = 0;
                                        rows.forEach(function (row) {
                                            var checkbox = row.querySelector('input[type="checkbox"]');
                                            if (checkbox.checked) selected++;
                                            var match = row.getAttribute('data-page-title').indexOf(query) !== -1;
                                            row.hidden = !match;
                                            if (match) visible++;
                                        });
                                        count.textContent = selected + (selected === 1 ? ' page selected' : ' pages selected');
                                        empty.hidden = visible !== 0;
                                    }
                                    search.addEventListener('input', update);
                                    list.addEventListener('change', update);
                                    clear.addEventListener('click', function () {
                                        rows.forEach(function (row) { row.querySelector('input[type="checkbox"]').checked = false; });
                                        update();
                                    });
                                    update();
                                })();
                                </script>
                            </td>
                        </tr>

					</table>

					<p class="submit">
						<input type="button" data-lps-tab-demo="save" id="lpsbutton-primary" class="button-primary" value="<?php esc_html_e('Save Changes', 'login-page-styler'); ?>" />
					</p>

				</div>
			</div>


			<div id="content7">
				<div class="wrap">
<?php lps_free_tab_notice('Limit Login'); ?>
					<h1><?php esc_html_e('Limit Login Security', 'login-page-styler'); ?></h1>

					<table class="form-table">
						<tr valign='top'>
							<th scope='row'><?php esc_html_e('Enable Limit Login Security', 'login-page-styler'); ?></th>
							<td>
								<div class="onoffswitch">
									<input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitchl" value='1' <?php checked(1, absint(lps_free_demo_get_option('lps_enable_lim'))); ?> />
									<label class="onoffswitch-label" for="myonoffswitchl">
										<span class="onoffswitch-inner"></span>
										<span class="onoffswitch-switch"></span>
									</label>
								</div>
								<p class="description"><?php esc_html_e('Select Yes to Enable limit login on your login page ', 'login-page-styler'); ?></p>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e('Login Attempts', 'login-page-styler'); ?></th>
							<td><label for="lps_login_attempts">
									<input type="number" id="lps_login_attempts" placeholder="2" size="40" min="1" max="100" value="<?php echo esc_attr(lps_free_demo_get_option('lps_login_attempts', 5)); ?>" /> Attempts.
									<p class="description"><?php esc_html_e('Number of Attempts before login lockdown.', 'login-page-styler'); ?></p>
								</label></td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e('Count Failed Attempts Within ', 'login-page-styler'); ?></th>
							<td><label for="lps_attempts_within">
									<input type="number" id="lps_attempts_within" placeholder="1" size="40" min="1" max="1440" value="<?php echo esc_attr(lps_free_demo_get_option('lps_attempts_within', 30)); ?>" /> Minutes
									<p class="description"><?php esc_html_e(' Failed Attempts within this time period will be blocked.', 'login-page-styler'); ?></p>
								</label></td>
						</tr>


						<tr valign="top">
							<th scope="row"><?php esc_html_e('Lockdown Time', 'login-page-styler'); ?></th>
							<td><label for="lps_lock_time">
									<input type="number" id="lps_lock_time" placeholder="2" size="40" min="1" max="10080" value="<?php echo esc_attr(lps_free_demo_get_option('lps_lock_time', 15)); ?>" /> Minutes
									<p class="description"><?php esc_html_e(' How long an IP remains blocked after reaching the failed-attempt limit.  ', 'login-page-styler'); ?></p>
								</label></td>
						</tr>

					</table>

					<p class="submit">
						<input type="button" data-lps-tab-demo="save" id="lpsbutton-primary" class="button-primary" value="<?php esc_html_e('Save Changes', 'login-page-styler'); ?>" />
					</p>

					</form>

				</div>
			</div>




            <div id="content8">
  <div class="wrap">
    <?php lps_free_tab_notice('Blocked IP'); ?>
    <h1><?php esc_html_e('Blocked IP Addresses', 'login-page-styler'); ?></h1>
    <p class="description">Preview the list of active temporary login lockouts and the controls for releasing them. The example data below is not actual login activity.</p>
    <div class="lps-ip-panel" id="lps-ip-panel">
      <div class="lps-ip-panel-head"><strong>Active lockouts: <span id="lps-ip-count">2</span></strong><span class="description">Sample entries — no IPs are blocked by Free.</span></div>
      <table class="widefat striped" id="lps-ip-table">
        <thead><tr><th><input type="checkbox" id="lps-ip-select-all" aria-label="Select all demo IPs"></th><th>IP address</th><th>Failed attempts</th><th>Locked at (UTC)</th><th>Time remaining</th><th>Action</th></tr></thead>
        <tbody>
          <tr class="lps-ip-row"><td><input type="checkbox" class="lps-ip-checkbox"></td><td><code>192.0.2.10</code></td><td>5</td><td>Example: 09:24</td><td class="lps-ip-remaining">10 min</td><td><button class="button" type="button" data-lps-tab-demo="release">Release</button></td></tr>
          <tr class="lps-ip-row"><td><input type="checkbox" class="lps-ip-checkbox"></td><td><code>198.51.100.24</code></td><td>8</td><td>Example: 09:32</td><td class="lps-ip-remaining">15 min</td><td><button class="button" type="button" data-lps-tab-demo="release">Release</button></td></tr>
        </tbody>
      </table>
      <div class="lps-ip-actions"><button class="button button-primary" id="lps-ip-release-selected" type="button" data-lps-tab-demo="release">Release Selected</button> <span id="lps-ip-selected-count" class="description">0 selected</span></div>
    </div>
    <p class="lps-free-demo-note">Preview only. IP lockout tracking, real IP records, and release actions are only included in Pro.</p>
  </div>
</div>

		</div>

	</div>

<?php }; ?>
<?php

/** User-authored CSS is separate from generated design rules. */
/* Legacy CSS hook disabled: the unified styling engine emits user CSS last.
add_action('login_head', function () {
    $custom = get_option('lps_user_custom_css', '');
    if (is_string($custom) && trim($custom) !== '') {
        echo '<style id="lps-user-authored-css">' . wp_strip_all_tags($custom) . '</style>';
    }
}, PHP_INT_MAX);
*/


require_once __DIR__ . '/includes/lps-unified-style-engine.php';

// Shared, responsive layout for the published login and preview iframe.
require_once __DIR__ . '/includes/lps-layout-controller.php';
require_once __DIR__ . '/includes/lps-modern-minimal.php';
require_once __DIR__ . '/includes/lps-nature-pro.php';
require_once __DIR__ . '/includes/lps-noir-editorial.php';
require_once __DIR__ . '/includes/lps-split-studio.php';
require_once __DIR__ . '/includes/lps-aurora-glass.php';

/** One-time migration from template gallery to Default Login only.
 * Keep common Customizer settings, user CSS, and security options unchanged.
 * Saved template editor data is left in the database for safe rollback.
 */
function lps_default_only_migration() {
    if (get_option('lps_default_only_migrated_99294')) return;
    delete_option('lps_active_design_template');
    delete_option('lps_layout');
    $css = get_option('lps_login_custom_css', '');
    if (is_string($css) && strpos($css, '/* LPS TEMPLATE START */') !== false) {
        $clean = preg_replace('~/\* LPS TEMPLATE START \*/[\s\S]*?/\* LPS TEMPLATE END \*/~', '', $css);
        if (is_string($clean)) update_option('lps_login_custom_css', trim($clean));
    }
    update_option('lps_default_only_migrated_99294', 1, false);
}
add_action('admin_init', 'lps_default_only_migration', 1);

/**
 * Visual styling keys that can safely be cleared when choosing a new design.
 * IMPORTANT: Do not add account, OAuth, CAPTCHA, security, login URL, or
 * redirect settings to this list. The plugin enable toggle also stays put.
 */
function lps_template_visual_reset_keys() {
    return array(
        'lps_login_logo', 'lps_login_logo_width', 'lps_login_logo_height',
        'lps_login_text_logo', 'lps_textlogo_color', 'lps_textlogo_color_hover',
        'lps_login_logo_hide', 'lps_login_logo_link', 'lps_login_logo_tittle',
        'lps_login_bg_repeat', 'lps_login_bg_size', 'lps_login_bg_position',
        'lps_body_bg_img', 'lps_login_background_color', 'lps_login_background_image',
        'lps_background_type', 'lps_gradient_style', 'lps_gradient_color_one',
        'lps_gradient_color_two', 'lps_gradient_angle',
        'lps_login_form_color', 'lps_login_form_bg', 'lps_login_form_color_opacity',
        'lps_login_form_input_color', 'lps_login_form_input_text_color',
        'lps_login_form_input_color_opacity', 'lps_login_form_border_style',
        'lps_login_form_border_color', 'lps_login_form_border_size',
        'lps_login_form_border_radius', 'lps_login_form_width',
        'lps_login_form_input_border_style', 'lps_login_form_input_border_size',
        'lps_login_form_input_feild_border_color',
        'lps_login_form_input_feild_border_radius',
        'lps_login_label_color', 'lps_login_label_size', 'lps_login_remember_label_size',
        'lps_box_shadow_vertical', 'lps_box_shadow_horizontal',
        'lps_box_shadow_blur', 'lps_box_shadow_spread',
        'lps_box_shadow_color', 'lps_box_shadow_opacity',
        'lps_login_form_position', 'lps_login_animation',
        'lps_login_button_color', 'lps_login_button_border_color',
        'lps_login_button_color_hover', 'lps_login_button_text_color',
        'lps_login_button_text_color_hover', 'lps_login_button_border_color_hover',
        'lps_login_button_border_radius', 'lps_login_button_size',
        'lps_login_nav_size', 'lps_login_nav_color', 'lps_login_nav_hover_color',
        'lps_login_nav_bgcolor', 'lps_login_nav_bgcolor_hover',
        'lps_copyright_color', 'lps_login_copyright',
        'lps_notice_textcolor', 'lps_notice_bgcolor', 'lps_notice_bgcolor_opacity',
        'lps_gfontlab', 'lps_gfont_inputtext', 'lps_gfontlink',
        'lps_gfontmsg', 'lps_gfontbtn', 'lps_gfonttext_logo', 'lps_gfont_copyright',
        'lps_login_custom_css', 'lps_user_custom_css',
        'lps_slideshow_animation_style', 'lps_slideshow_time', 'lps_body_bg_slideshow'
    );
}

/** A design switch is explicit: reset visual overrides and display its fresh defaults.
 * AJAX reload ensures legacy JS/CSS in the iframe never persists across designs.
 */
function lps_switch_visual_template_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied.'), 403);
    }
    check_ajax_referer('lps_switch_visual_template', 'nonce');
    $preset = isset($_POST['preset']) ? sanitize_key(wp_unslash($_POST['preset'])) : '';
    $preset = lps_sanitize_design_preset($preset);
    if (!isset($_POST['preset']) || $preset !== sanitize_key(wp_unslash($_POST['preset']))) {
        wp_send_json_error(array('message' => 'Unknown template.'), 400);
    }
    foreach (lps_template_visual_reset_keys() as $key) {
        delete_option($key);
    }
    // Clear only the incoming template's own values; leave other templates'
    // customization records intact for later reuse.
    if ($preset === 'aurora-glass' && function_exists('lps_aurora_fields')) {
        foreach (array_keys(lps_aurora_fields()) as $key) delete_option('lps_aurora_' . $key);
    }
    if ($preset === 'split-studio' && function_exists('lps_split_fields')) {
        foreach (array_keys(lps_split_fields()) as $key) delete_option('lps_split_' . $key);
    }
    $blur = get_option('lps_form_blur_by_preset', array());
    if (!is_array($blur)) $blur = array();
    unset($blur[$preset]); // Intentional frosted presets default to 24; others to 0.
    update_option('lps_form_blur_by_preset', $blur, false);
    update_option('lps_design_preset', $preset);
    wp_send_json_success(array('preset' => $preset));
}
add_action('wp_ajax_lps_switch_visual_template', 'lps_switch_visual_template_ajax');

add_action('admin_footer', function () {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen gating; sensitive AJAX handlers independently verify capability and nonce.
    if (!isset($_GET['page']) || !is_string($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'lps_option') return;
    ?>
<script id="lps-template-switch-reset">
(function(){'use strict';
  function init(){
    var select=document.getElementById('lps_design_preset');
    if(!select)return;
    var previous=select.value, busy=false;
    function syncGallery(){
      document.querySelectorAll('.lps-gallery-item[data-lps-design]').forEach(function(card){
        var active=card.getAttribute('data-lps-design')===select.value;
        card.classList.toggle('is-selected',active);
        card.setAttribute('aria-pressed',active?'true':'false');
      });
    }
    document.querySelectorAll('.lps-gallery-item[data-lps-design]').forEach(function(card){
      card.addEventListener('click',function(){
        if(busy)return;
        var value=card.getAttribute('data-lps-design');
        if(value===select.value)return;
        select.value=value;
        syncGallery();
        select.dispatchEvent(new Event('change',{bubbles:true}));
      });
    });

    select.addEventListener('change',function(){
      syncGallery();
      if(busy || select.value===previous)return;
      var next=select.value;
      if(!window.confirm('Switch to this template? This will reset general visual styling settings and restore the new template defaults. Social Login and security settings will be preserved. Unsaved Customizer changes will be lost.')){
        select.value=previous;
        syncGallery();
        select.dispatchEvent(new Event('input',{bubbles:true}));
        return;
      }
      busy=true;select.disabled=true;
      var body=new URLSearchParams({action:'lps_switch_visual_template',nonce:<?php echo wp_json_encode(wp_create_nonce('lps_switch_visual_template')); ?>,preset:next});
      fetch(<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
        .then(function(r){return r.json();})
        .then(function(result){if(!result.success)throw new Error(result.data&&result.data.message||'Unable to change template.');window.location.reload();})
        .catch(function(err){busy=false;select.disabled=false;select.value=previous;syncGallery();alert(err.message);});
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
}());
</script>
    <?php
});

/** Only allow known, safe design identifiers. */
function lps_sanitize_design_preset($value) {
    return in_array($value, array('default','modern-minimal','nature-pro','noir-editorial','split-studio','aurora-glass'), true) ? $value : 'default';
}

add_action('admin_footer', function () { ?>
<script id="lps-nature-blur-admin">
(function(){
 function init(){
  var preset=document.getElementById('lps_design_preset');
  var range=document.getElementById('lps_nature_form_blur');
  var reset=document.getElementById('lps_nature_blur_reset');
  if(!preset||!range)return;
  function toggle(){document.querySelectorAll('.lps-nature-blur-setting').forEach(function(row){row.style.display='';});}
  // Keep unsaved per-template edits independent when switching the design dropdown.
  var savedBlurValues=<?php echo wp_json_encode(lps_form_blur_map()); ?> || {};
  var currentPreset=preset.value;
  savedBlurValues[currentPreset]=Number(range.value)||0;
  var switching=false;
  range.addEventListener('input',function(){
    if (!switching) savedBlurValues[preset.value]=Number(range.value)||0;
  });
  preset.addEventListener('change',function(){
    switching=true;
    var next=Object.prototype.hasOwnProperty.call(savedBlurValues,preset.value)
      ? savedBlurValues[preset.value] : ((preset.value==='nature-pro'||preset.value==='aurora-glass')?24:0);
    range.value=String(next);
    var output=document.getElementById('lps_nature_blur_value');
    if(output) output.value=String(next);
    currentPreset=preset.value;
    range.dispatchEvent(new Event('input',{bubbles:true}));
    switching=false;
  });
  preset.addEventListener('change',toggle);toggle();
  // Preview updates are managed by the same engine as form opacity.
  if(reset)reset.addEventListener('click',function(){
    range.value='0';
    var output=document.getElementById('lps_nature_blur_value');
    if(output)output.value='0';
    range.dispatchEvent(new Event('input',{bubbles:true}));
  });
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
}());
</script>
<?php });


/** Enqueue dedicated Upgrade to Pro admin styles. */
function lps_enqueue_upgrade_pro_styles($hook) {
    if ($hook !== 'login-page-styler_page_lps_upgrade_pro') {
        return;
    }
    wp_enqueue_style('lps-upgrade-pro', plugins_url('css/lps-upgrade-pro.css', __FILE__), array(), (string) filemtime(plugin_dir_path(__FILE__) . 'css/lps-upgrade-pro.css'));
}
add_action('admin_enqueue_scripts', 'lps_enqueue_upgrade_pro_styles');

/** Dedicated Upgrade to Pro page for the Freemium build. */
function lps_upgrade_pro_page_callback() {
    // Every row describes a setting group from the existing Free and full builds.
    // A 'Preview only' value means the Free admin can demonstrate the control,
    // but cannot save it or apply it on the actual login page.
    $features = array(
        'Login templates' => array(
            array('Default WordPress Login', 'Available', 'Available'),
            array('Modern Minimal', 'Available', 'Available'),
            array('Nature Pro', 'Preview only', 'Full template'),
            array('Noir Editorial', 'Preview only', 'Full template'),
            array('Split-Screen Studio', 'Preview only', 'Full template'),
            array('Aurora Glass', 'Preview only', 'Full template'),
        ),
        'Live Customizer & branding' => array(
            array('Live preview and responsive desktop / tablet / mobile views', 'Included', 'Included'),
            array('Enable custom login design', 'Included', 'Included'),
            array('Logo image, size, link and title', 'Included', 'Included'),
            array('Text logo, color and hover color', 'Included', 'Included'),
            array('Google Font for text logo', 'Preview only', 'Included'),
            array('Login logo, navigation, notices and language visibility', 'Included', 'Included'),
        ),
        'Background styling' => array(
            array('Background color and image', 'Included', 'Included'),
            array('Background repeat, cover / contain, and positioning', 'Included', 'Included'),
            array('Basic two-color gradient', 'Included', 'Included'),
            array('Radial gradient and custom angle', 'Preview only', 'Included'),
            array('Background animation: Drift and Pulse', 'Preview only', 'Included'),
            array('Background slideshow and timing / transition controls', 'Preview only', 'Included'),
        ),
        'Login form & fields' => array(
            array('Login form position, width, background color and radius', 'Included', 'Included'),
            array('Labels, label size and color', 'Included', 'Included'),
            array('Input background, text color, border color and radius', 'Included', 'Included'),
            array('Remember Me label size', 'Included', 'Included'),
            array('Login form background image', 'Preview only', 'Included'),
            array('Form transparency / opacity and blur', 'Preview only', 'Included'),
            array('Login form entrance animation', 'Preview only', 'Included'),
            array('Advanced form borders and input opacity', 'Preview only', 'Included'),
            array('Form box shadow: color, opacity, X/Y, blur and spread', 'Preview only', 'Included'),
        ),
        'Buttons, links, footer & messages' => array(
            array('Login button background, text color, size and radius', 'Included', 'Included'),
            array('Button hover text / background and border hover effects', 'Preview only', 'Included'),
            array('Navigation link color and size', 'Included', 'Included'),
            array('Navigation hover and background styling', 'Preview only', 'Included'),
            array('Copyright / footer text and text color', 'Included', 'Included'),
            array('Copyright font controls', 'Preview only', 'Included'),
            array('Error and notice text / background colors', 'Included', 'Included'),
            array('Message background opacity', 'Preview only', 'Included'),
            array('Advanced Google Fonts for form labels, inputs, links, buttons and messages', 'Preview only', 'Included'),
            array('Custom CSS editor', 'Preview only', 'Included'),
        ),
        'Login settings & protection' => array(
            array('General sign-in and registration settings', 'Included', 'Included'),
            array('Google reCAPTCHA settings', 'Included', 'Included'),
            array('Login / logout menu integration', 'Preview only', 'Included'),
            array('Custom login URL and login redirects', 'Preview only', 'Included'),
            array('Protected pages and private-site login', 'Preview only', 'Included'),
            array('Login attempt limits and lockout duration', 'Preview only', 'Included'),
            array('Blocked IP records and unblock actions', 'Preview only', 'Included'),
            array('IP and country / region blocking', 'Preview only', 'Included'),
            array('Temporary access links and access expiry', 'Preview only', 'Included'),
            array('User login logs, activity charts and CSV reporting', 'Preview only', 'Included'),
        ),
        'Social Login' => array(
            array('Social Login settings studio and button preview', 'Demo only', 'Included'),
            array('Google, Facebook, LinkedIn, Discord and Microsoft providers', 'Demo only', 'Working authentication'),
            array('Button templates, layouts, icons and display styling', 'Demo only', 'Available on login page'),
            array('OAuth credentials and login callbacks', 'Not included', 'Included'),
        ),
    );
    ?>
    <div class="wrap lps-upgrade-page">
        <div class="lps-upgrade-hero">
            <div class="lps-upgrade-hero__content">
                <span class="lps-upgrade-badge">Login Page Styler Pro</span>
                <h1>Unlock premium templates, social login, and advanced security tools.</h1>
                <p>Keep the easy live preview you already love and unlock the premium features that turn a basic login screen into a branded, conversion-friendly experience.</p>
                <div class="lps-upgrade-actions">
                    <a class="button button-primary button-hero" href="https://cresario.com/login-page-styler/" target="_blank" rel="noopener noreferrer">Upgrade to Pro</a>
                </div>
                <ul class="lps-upgrade-highlights">
                    <li>Premium templates and visual effects</li>
                    <li>Working Social Login providers</li>
                    <li>Advanced login security and access tools</li>
                </ul>
            </div>
            <div class="lps-upgrade-hero__panel">
                <div class="lps-upgrade-pricecard">
                    <h3>Why users upgrade</h3>
                    <ul>
                        <li>Make your login page look premium</li>
                        <li>Let users sign in with social accounts</li>
                        <li>Strengthen security around login access</li>
                        <li>Save time with ready-made templates</li>
                    </ul>
                    <a class="button button-primary button-block" href="https://cresario.com/login-page-styler/" target="_blank" rel="noopener noreferrer">Get Login Page Styler Pro</a>
                </div>
            </div>
        </div>

        <div class="lps-upgrade-feature-grid">
            <div class="lps-upgrade-card">
                <h3>Premium Templates</h3>
                <p>Unlock Nature Pro, Noir Editorial, Split-Screen Studio, and Aurora Glass for polished, client-ready designs.</p>
            </div>
            <div class="lps-upgrade-card">
                <h3>Social Login</h3>
                <p>Enable branded social login buttons and let users sign in with their favorite providers.</p>
            </div>
            <div class="lps-upgrade-card">
                <h3>Advanced Security</h3>
                <p>Use login logs, IP and region controls, temporary access links, and premium access protection tools.</p>
            </div>
            <div class="lps-upgrade-card">
                <h3>Customizer Power</h3>
                <p>Go beyond the essentials with blur, opacity, shadows, animations, Google Fonts, and Custom CSS.</p>
            </div>
        </div>

        <div class="lps-upgrade-compare-wrap">
            <div class="lps-upgrade-section-head">
                <h2>Free vs Pro</h2>
                <p>Explore the complete feature comparison by settings group. “Preview only” features do not save or run on the live login page in Free.</p>
            </div>
            <div class="lps-upgrade-compare">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Free</th>
                            <th>Pro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($features as $section_title => $items) : ?>
                            <tr class="lps-upgrade-group-row"><th colspan="3" scope="colgroup"><?php echo esc_html($section_title); ?></th></tr>
                            <?php foreach ($items as $feature) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($feature[0]); ?></strong></td>
                                    <td><?php echo esc_html($feature[1]); ?></td>
                                    <td><?php echo esc_html($feature[2]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lps-upgrade-bottom-cta">
            <div>
                <h2>Ready to unlock the full plugin?</h2>
                <p>Upgrade when you are ready to publish premium templates, enable social login, and use advanced security tools on your live website.</p>
            </div>
            <a class="button button-primary button-hero" href="https://cresario.com/login-page-styler/" target="_blank" rel="noopener noreferrer">Upgrade to Pro</a>
        </div>
    </div>
    <?php
}
