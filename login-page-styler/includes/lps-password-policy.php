<?php
/**
 * Login Page Styler: optional native WordPress password policies.
 * No policies are enforced unless the administrator explicitly enables them.
 * Does not change OAuth, REST/API, or other plugins' account forms.
 */
if (!defined('ABSPATH')) { exit; }

/** Non-administrator roles only. Exempt administrators to preserve recovery access. */
function lps_policy_available_roles() {
    if (!function_exists('wp_roles')) { return array(); }
    $available = array();
    foreach (wp_roles()->roles as $key => $details) {
        if ($key === 'administrator') { continue; }
        $available[$key] = translate_user_role($details['name']);
    }
    return $available;
}
function lps_policy_sanitize_days($value) { return max(1, min(3650, absint($value))); }
function lps_policy_sanitize_length($value) { return max(8, min(128, absint($value))); }
function lps_policy_sanitize_roles($value) {
    $allowed = array_keys(lps_policy_available_roles());
    return array_values(array_unique(array_intersect(array_map('sanitize_key', (array) $value), $allowed)));
}
function lps_policy_role_applies($user) {
    if (!($user instanceof WP_User) || user_can($user, 'manage_options')) { return false; }
    $selected = lps_policy_sanitize_roles(get_option('lps_password_expiry_roles', array()));
    return (bool) array_intersect((array) $user->roles, $selected);
}
/** Save baseline on first successful native login; never immediately expire legacy accounts. */
function lps_policy_note_login($user_login, $user) {
    if ($user instanceof WP_User && !get_user_meta($user->ID, '_lps_password_changed', true)) {
        update_user_meta($user->ID, '_lps_password_changed', time());
    }
}
add_action('wp_login', 'lps_policy_note_login', 10, 2);

/** Check only successful standard password authentication. OAuth bypasses this filter. */
function lps_policy_expiry_on_auth($user, $username, $password) {
    if (!absint(get_option('lps_password_expiry_enabled', 0)) || !($user instanceof WP_User) || !lps_policy_role_applies($user)) { return $user; }
    $changed = absint(get_user_meta($user->ID, '_lps_password_changed', true));
    if (!$changed) { return $user; }
    $days = lps_policy_sanitize_days(get_option('lps_password_expiry_days', 90));
    if (time() < $changed + $days * DAY_IN_SECONDS) { return $user; }
    $url = wp_lostpassword_url();
    return new WP_Error('lps_password_expired', sprintf(
        /* translators: %s is a password reset URL. */
        __('Your password has expired. Please <a href="%s">reset your password</a> to continue.', 'login-page-styler'),
        esc_url($url)
    ));
}
add_filter('authenticate', 'lps_policy_expiry_on_auth', 95, 3);

/** WordPress fires this after generating a new password hash. */
function lps_policy_note_password_change($user) {
    if ($user instanceof WP_User) { update_user_meta($user->ID, '_lps_password_changed', time()); }
}
add_action('after_password_reset', 'lps_policy_note_password_change', 10, 1);
/** Covers admin-initiated and profile password changes using wp_set_password(). */
function lps_policy_note_set_password($password, $user_id) {
    update_user_meta(absint($user_id), '_lps_password_changed', time());
}
add_action('wp_set_password', 'lps_policy_note_set_password', 10, 2);

/** Validate password characters without storing or logging the actual password. */
function lps_policy_password_errors($password) {
    $errors = array();
    if (strlen($password) < lps_policy_sanitize_length(get_option('lps_password_min_length', 12))) {
        $errors[] = __('Password is shorter than the required minimum.', 'login-page-styler');
    }
    if (get_option('lps_password_require_upper') && !preg_match('/[A-Z]/', $password)) { $errors[] = __('Use at least one uppercase letter.', 'login-page-styler'); }
    if (get_option('lps_password_require_lower') && !preg_match('/[a-z]/', $password)) { $errors[] = __('Use at least one lowercase letter.', 'login-page-styler'); }
    if (get_option('lps_password_require_number') && !preg_match('/[0-9]/', $password)) { $errors[] = __('Use at least one number.', 'login-page-styler'); }
    if (get_option('lps_password_require_symbol') && !preg_match('/[^a-zA-Z0-9]/', $password)) { $errors[] = __('Use at least one special character.', 'login-page-styler'); }
    return $errors;
}
/** WordPress reset_password() is only reached after validate_password_reset hook passes. */
function lps_policy_validate_password_reset($errors, $user) {
    if (!get_option('lps_password_strength_enforce') || !($errors instanceof WP_Error)) { return; }
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WordPress password reset form is validated by core reset-key checks; password must remain byte-accurate.
    if (!isset($_POST['pass1']) || !is_string($_POST['pass1'])) { return; }
    // Do not sanitize passwords: spaces, punctuation and case are meaningful.
    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- WordPress password reset form is validated by core reset-key checks; password must remain byte-accurate.
    $password = wp_unslash($_POST['pass1']);
    if ($password === '') { return; }
    foreach (lps_policy_password_errors($password) as $message) {
        $errors->add('lps_password_policy', $message);
    }
}
add_action('validate_password_reset', 'lps_policy_validate_password_reset', 10, 2);
