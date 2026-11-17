<?php

/**
 * Template Name: Custom Login Page Styler Template
 */

// This constant is set only by this dedicated iframe entry point, never by URL parameters.
define('LPS_DESIGN_PREVIEW', true);

// Include WordPress core files
$path_to_wp_load = dirname(dirname(dirname(__DIR__))) . '/wp-load.php';
if (file_exists($path_to_wp_load)) {
	require_once $path_to_wp_load;
} else {
	die('wp-load.php not found. Please check the path.');
}

// Do not expose unpublished designs or admin-only preview content publicly.
if (!current_user_can('manage_options')) {
    wp_die('Permission denied.', '', array('response' => 403));
}

// This is an interactive admin preview, not a cacheable public page.
// Prevent stale iframe HTML (including inline saved CSS) after refresh.
if (!headers_sent()) {
    nocache_headers();
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true);
}

// The preview is a directly loaded admin iframe, so WordPress must be loaded
// before checking ABSPATH.
if (!defined('ABSPATH')) {
	exit;
}

//require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

$user_login = ''; // Preview never pre-fills a username.

// Retrieve login header URL and text
$login_header_url  = esc_url(home_url());
$login_header_text = get_option('blogname');

// Set up classes for the body tag
$classes = array('login-action-login', 'wp-core-ui', 'locale-' . get_locale());
if (get_option('lps_design_preset', 'default') === 'modern-minimal') $classes[] = 'lps-modern-minimal';

?>


<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<title><?php echo esc_html($login_header_text); ?></title>

	<?php
	wp_enqueue_style('login');
	/**
	 * Fires in the login page header after scripts are enqueued.
	 *
	 * @since 3.1.0
	 */
	do_action('login_enqueue_scripts');

	/**
	 * Fires in the login page header after scripts are enqueued.
	 *
	 * @since 2.1.0
	 */
	do_action('login_head');
	?>
</head>

<body class="login no-js <?php echo esc_attr(implode(' ', $classes)); ?>">
	<script type="text/javascript">
		document.body.className = document.body.className.replace('no-js', 'js');
	</script>

	<?php
	/**
	 * Fires in the login page header after the body tag is opened.
	 *
	 * @since 4.6.0
	 */
	do_action('login_header');

	/**
	 * Fires in the login page header after scripts are enqueued.
	 *
	 * @since 3.1.0
	 */
	do_action('login_enqueue_scripts');
	?>

	<div id="login">
		<h1><a href="<?php echo esc_url($login_header_url); ?>"><?php echo esc_html($login_header_text); ?></a></h1>
		<!-- Your custom login template content here -->
		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Core-compatible public WordPress login form; values are display-only and escaped before output.
		$rememberme = ! empty($_POST['rememberme']);
		$aria_describedby = ''; ?>
		<form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
			<p>
				<label for="user_login"><?php esc_html_e('Username or Email Address', 'login-page-styler'); ?></label>
				<input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" autocapitalize="off" autocomplete="username" required="required" />
			</p>

			<div class="user-pass-wrap">
				<label for="user_pass"><?php esc_html_e('Password', 'login-page-styler'); ?></label>
				<div class="wp-pwd">
					<input type="password" name="pwd" id="user_pass" class="input password-input" value="" size="20" autocomplete="current-password" spellcheck="false" required="required" />
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e('Show password', 'login-page-styler'); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<?php

			/**
			 * Fires following the 'Password' field in the login form.
			 *
			 * @since 2.1.0
			 */
			do_action('login_form');
			/**
			 * Filters the separator used between login form navigation links.
			 *
			 * @since 4.9.0
			 *
			 * @param string $login_link_separator The separator used between login form navigation links.
			 */
			$login_link_separator = apply_filters('login_link_separator', ' | ');


			?>
			<p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked($rememberme); ?> /> <label for="rememberme"><?php esc_html_e('Remember Me', 'login-page-styler'); ?></label></p>
			<p class="submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Log In', 'login-page-styler'); ?>" />
			</p>
		</form>

		<p id="nav">
			<?php

			if (get_option('users_can_register')) {
				$registration_url = sprintf('<a href="%s">%s</a>', esc_url(wp_registration_url()), __('Register', 'login-page-styler'));

				/** This filter is documented in wp-includes/general-template.php */
				echo wp_kses_post(apply_filters('register', $registration_url));

				echo esc_html($login_link_separator);
			}

			$html_link = sprintf('<a href="%s">%s</a>', esc_url(wp_lostpassword_url()), __('Lost your password?', 'login-page-styler'));

			/**
			 * Filters the link that allows the user to reset the lost password.
			 *
			 * @since 6.1.0
			 *
			 * @param string $html_link HTML link to the lost password form.
			 */
			echo wp_kses_post(apply_filters('lost_password_html_link', $html_link));

			?>
		</p>

		<p id="backtoblog">
			<?php
			$html_link = sprintf(
				'<a href="%s">%s</a>',
				esc_url(home_url('/')),
				sprintf(
					/* translators: %s: Site title. */
					_x('&larr; Go to %s', 'site', 'login-page-styler'),
					get_bloginfo('title', 'display')
				)
			);
			/**
			 * Filters the "Go to site" link displayed in the login page footer.
			 *
			 * @since 5.7.0
			 *
			 * @param string $link HTML link to the home URL of the current site.
			 */
			echo wp_kses_post(apply_filters('login_site_html_link', $html_link));
			?>

		</p>

	</div>
	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core-compatible public WordPress login form; values are display-only and escaped before output.
	$interim_login = isset($_REQUEST['interim-login']);

	if (
		! $interim_login &&
		/**
		 * Filters whether to display the Language selector on the login screen.
		 *
		 * @since 5.9.0
		 *
		 * @param bool $display Whether to display the Language selector on the login screen.
		 */
		apply_filters('login_display_language_dropdown', true)
	) {
		$languages = get_available_languages();

		if (! empty($languages)) {
	?>
			<div class="language-switcher">
				<form id="language-switcher" action="" method="get">

					<label for="language-switcher-locales">
						<span class="dashicons dashicons-translation" aria-hidden="true"></span>
						<span class="screen-reader-text">
							<?php
							/* translators: Hidden accessibility text. */
							esc_html_e('Language', 'login-page-styler');
							?>
						</span>
					</label>

					<?php
					$args = array(
						'id'                          => 'language-switcher-locales',
						'name'                        => 'wp_lang',
						'selected'                    => determine_locale(),
						'show_available_translations' => false,
						'explicit_option_en_us'       => true,
						'languages'                   => $languages,
					);

					/**
					 * Filters default arguments for the Languages select input on the login screen.
					 *
					 * The arguments get passed to the wp_dropdown_languages() function.
					 *
					 * @since 5.9.0
					 *
					 * @param array $args Arguments for the Languages select input on the login screen.
					 */
					wp_dropdown_languages(apply_filters('login_language_dropdown_args', $args));
					?>

					<?php if ($interim_login) { ?>
						<input type="hidden" name="interim-login" value="1" />
					<?php } ?>

					<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core-compatible public WordPress login form; values are display-only and escaped before output. ?>
					<?php if (isset($_GET['redirect_to']) && is_string($_GET['redirect_to']) && '' !== esc_url_raw(wp_unslash($_GET['redirect_to']))) { ?>
						<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core-compatible public WordPress login form; values are display-only and escaped before output. ?>
						<input type="hidden" name="redirect_to" value="<?php echo esc_url(esc_url_raw(wp_unslash($_GET['redirect_to']))); ?>" />
					<?php } ?>

					<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core-compatible public WordPress login form; values are display-only and escaped before output. ?>
					<?php if (isset($_GET['action']) && is_string($_GET['action']) && '' !== sanitize_key(wp_unslash($_GET['action']))) { ?>
						<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core-compatible public WordPress login form; values are display-only and escaped before output. ?>
						<input type="hidden" name="action" value="<?php echo esc_attr(sanitize_key(wp_unslash($_GET['action']))); ?>" />
					<?php } ?>

					<input type="submit" class="button" value="<?php esc_attr_e('Change', 'login-page-styler'); ?>">

				</form>
			</div>
		<?php } ?>
	<?php } ?>

</body>

</html>
<?php
/**
 * Fires in the login page footer.
 *
 * @since 3.1.0
 */
do_action('login_footer');
// Temporarily suppress deprecated notices
add_filter('deprecated_function_trigger_error', '__return_false');

// Call wp_footer, but suppress the deprecated warning
wp_footer();

// Re-enable deprecated notices after calling wp_footer
remove_filter('deprecated_function_trigger_error', '__return_false');
?>