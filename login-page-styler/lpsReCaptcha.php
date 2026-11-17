<?php
/**
 * Google reCAPTCHA v2 checkbox and v3 score-based verification.
 * Existing installations keep v2 unless an administrator selects v3.
 */
if (!defined('ABSPATH')) { exit; }

function lps_recaptcha_version() {
    return get_option('lps_recaptcha_version', 'v2') === 'v3' ? 'v3' : 'v2';
}
function lps_recaptcha_ready() {
    return trim((string) get_option('rs_site_key', '')) !== ''
        && trim((string) get_option('rs_private_key', '')) !== '';
}
function lps_recaptcha_enabled_for($action) {
    $options = array('login' => 'lps_login_captcha', 'register' => 'lps_reg_captcha', 'lostpassword' => 'lps_lost_captcha');
    return isset($options[$action]) && (bool) get_option($options[$action]);
}
function lps_recaptcha_enqueue() {
    if (!lps_recaptcha_ready()) { return; }
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public WordPress login flow; action selects UI and Google verifies the submitted CAPTCHA token server-side.
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
    if ($action === 'rp') { $action = 'lostpassword'; }
    if (!lps_recaptcha_enabled_for($action)) { return; }
    $url = 'https://www.google.com/recaptcha/api.js';
    if (lps_recaptcha_version() === 'v3') {
        $url = add_query_arg('render', rawurlencode((string) get_option('rs_site_key')), $url);
    }
    // Google hosts this API; a stable version makes the enqueue explicit for Plugin Check.
    wp_enqueue_script('lps-google-recaptcha', $url, array(), '3.0', true);
}
add_action('login_enqueue_scripts', 'lps_recaptcha_enqueue');

/** Form field: v2 widget or v3 token placeholder. */
function lps_recaptcha_field($action) {
    if (!lps_recaptcha_ready() || !lps_recaptcha_enabled_for($action)) { return; }
    if (lps_recaptcha_version() === 'v2') {
        $theme = get_option('lps_recaptcha_theme') === 'dark' ? 'dark' : 'light';
        echo '<div class="lps-recaptcha-container" style="margin:12px 0;max-width:100%;overflow:hidden">';
        echo '<div class="g-recaptcha" data-theme="' . esc_attr($theme) . '" data-sitekey="' . esc_attr(get_option('rs_site_key')) . '"></div></div>';
    } else {
        echo '<input type="hidden" name="lps_recaptcha_v3_token" class="lps-recaptcha-v3-token" value="">';
        echo '<p class="description lps-recaptcha-disclosure">Protected by reCAPTCHA. Google <a href="https://policies.google.com/privacy">Privacy</a> and <a href="https://policies.google.com/terms">Terms</a> apply.</p>';
    }
}
add_action('login_form', function() { lps_recaptcha_field('login'); });
add_action('register_form', function() { lps_recaptcha_field('register'); });
add_action('lostpassword_form', function() { lps_recaptcha_field('lostpassword'); });

/** Get a fresh v3 token immediately before submitting the relevant form. */
function lps_recaptcha_v3_footer() {
    if (!lps_recaptcha_ready() || lps_recaptcha_version() !== 'v3') { return; }
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public WordPress login flow; action selects UI and Google verifies the submitted CAPTCHA token server-side.
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
    if ($action === 'rp') { $action = 'lostpassword'; }
    if (!lps_recaptcha_enabled_for($action)) { return; }
    ?>
    <script>
    (function () {
        'use strict';
        var key = <?php echo wp_json_encode((string) get_option('rs_site_key')); ?>;
        var action = <?php echo wp_json_encode($action); ?>;
        var form = document.querySelector('form:has(.lps-recaptcha-v3-token)');
        if (!form) { return; }
        var busy = false, authorized = false;
        form.addEventListener('submit', function (event) {
            if (authorized) { return; }
            event.preventDefault();
            if (busy) { return; }
            busy = true;
            var button = form.querySelector('[type="submit"]');
            if (button) { button.disabled = true; }
            function fail() {
                busy = false;
                if (button) { button.disabled = false; }
                window.alert('reCAPTCHA could not load. Please try again.');
            }
            if (!window.grecaptcha || !window.grecaptcha.ready) { fail(); return; }
            window.grecaptcha.ready(function () {
                window.grecaptcha.execute(key, {action: action}).then(function (token) {
                    form.querySelector('.lps-recaptcha-v3-token').value = token;
                    authorized = true;
                    if (form.requestSubmit) { form.requestSubmit(button || undefined); }
                    else { form.submit(); }
                }).catch(fail);
            });
        });
    }());
    </script>
    <?php
}
add_action('login_footer', 'lps_recaptcha_v3_footer', 100);

function lps_recaptcha_submitted_token() {
    $name = lps_recaptcha_version() === 'v3' ? 'lps_recaptcha_v3_token' : 'g-recaptcha-response';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public WordPress login flow; action selects UI and Google verifies the submitted CAPTCHA token server-side.
    return isset($_POST[$name]) && is_string($_POST[$name])
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public WordPress login flow; action selects UI and Google verifies the submitted CAPTCHA token server-side.
        ? sanitize_text_field(wp_unslash($_POST[$name])) : '';
}
/** Validate Google response, including v3 score, action and site hostname. */
function lps_verify_recaptcha_token($token, $expected_action) {
    if (!lps_recaptcha_ready() || !is_string($token) || $token === '') { return false; }
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
        'timeout' => 8, 'redirection' => 0, 'sslverify' => true,
        'body' => array('secret' => get_option('rs_private_key'), 'response' => $token),
    ));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) { return false; }
    $result = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($result) || empty($result['success'])) { return false; }
    $host = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
    $verified_host = strtolower((string) ($result['hostname'] ?? ''));
    if ($host === '' || $verified_host === '' || preg_replace('/^www\./', '', $host) !== preg_replace('/^www\./', '', $verified_host)) { return false; }
    if (lps_recaptcha_version() === 'v3') {
        $threshold = max(0.1, min(1.0, (float) get_option('lps_recaptcha_v3_threshold', 0.5)));
        return ($result['action'] ?? '') === $expected_action
            && isset($result['score']) && is_numeric($result['score'])
            && (float) $result['score'] >= $threshold;
    }
    return true;
}

if (get_option('lps_login_captcha')) {
    add_filter('authenticate', function($user, $username, $password) {
        if (!lps_recaptcha_ready() || is_wp_error($user)) { return $user; }
        $script = isset($_SERVER['SCRIPT_NAME']) ? basename(sanitize_text_field(wp_unslash($_SERVER['SCRIPT_NAME']))) : '';
        if ($script !== 'wp-login.php' || strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'] ?? ''))) !== 'POST') { return $user; }
        if (lps_verify_recaptcha_token(lps_recaptcha_submitted_token(), 'login')) { return $user; }
        return new WP_Error('lps_recaptcha_failed', __('reCAPTCHA verification failed. Please try again.', 'login-page-styler'));
    }, 40, 3);
}
if (get_option('lps_reg_captcha')) {
    add_filter('registration_errors', function($errors, $username, $email) {
        if (lps_recaptcha_ready() && !lps_verify_recaptcha_token(lps_recaptcha_submitted_token(), 'register')) {
            $errors->add('lps_recaptcha_failed', __('reCAPTCHA verification failed. Please try again.', 'login-page-styler'));
        }
        return $errors;
    }, 10, 3);
}
if (get_option('lps_lost_captcha')) {
    add_action('lostpassword_post', function($errors) {
        if (lps_recaptcha_ready() && !lps_verify_recaptcha_token(lps_recaptcha_submitted_token(), 'lostpassword')) {
            $errors->add('lps_recaptcha_failed', __('reCAPTCHA verification failed. Please try again.', 'login-page-styler'));
        }
    });
}
