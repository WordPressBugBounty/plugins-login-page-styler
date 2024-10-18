<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Deactivate wp-login and activate the new URL
 */
add_action('plugins_loaded', 'lps_plugin_on_page_loaded');
function lps_plugin_on_page_loaded() {
    global $pagenow, $lps_is_login;
    $request = parse_url(rawurldecode($_SERVER['REQUEST_URI']));
    $custom_login_url = get_option('lps_new_login_url', '');

    if ($custom_login_url) {
        // Check for wp-login.php or custom login URL
        if ((strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-login.php') !== false) && !is_admin()) {
            $lps_is_login = true;
            $pagenow = 'index.php';
        } elseif ($request['path'] == site_url($custom_login_url, 'relative')) {
            $lps_is_login = false;
            $pagenow = 'wp-login.php';
        }
    }
}

/**
 * Handle redirections and special cases like logout
 */
add_action('wp_loaded', 'lps_redirect_page', 1);
function lps_redirect_page() {
    global $pagenow, $lps_is_login;

    if (get_option('lps_new_login_url')) {
        // Check if the user is trying to log out
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            // Let WordPress handle the logout and redirection
            wp_logout();
            wp_safe_redirect(home_url());
            exit;
        }

        if (! (isset($_GET['action']) && isset($_POST['post_password']) && $_GET['action'] == 'postpass')) {
            if ($lps_is_login) {
                nocache_headers();
                if (get_option('lps_new_redirection_url')) {
                    wp_safe_redirect(get_site_url() . "/" . get_option('lps_new_redirection_url'));
                } else {
                    wp_safe_redirect(get_site_url() . "/404");
                }
                exit;
            } elseif ($pagenow == 'wp-login.php') {
                global $user_login, $error;
                $redirect_admin = admin_url();
                $redirect_url = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : "";
                
                if (is_user_logged_in() && !isset($_REQUEST['action'])) {
                    nocache_headers();
                    wp_safe_redirect(apply_filters('lps_redirect_if_connected_login', $redirect_admin, $redirect_url));
                    exit();
                }
                
                require_once(ABSPATH . 'wp-login.php');
                exit;
            }

            if (is_admin() && !is_user_logged_in() && !defined('WP_CLI') && !wp_doing_ajax() && !defined('DOING_CRON') && $pagenow !== 'admin-post.php') {
                nocache_headers();
                if (get_option('lps_new_redirection_url')) {
                    wp_safe_redirect(get_site_url() . "/" . get_option('lps_new_redirection_url'));
                } else {
                    wp_safe_redirect(get_site_url() . "/404");
                }
                exit;
            }
        }
    }
}

add_filter('site_url', 'lps_filter_login_url', 10, 4);
function lps_filter_login_url($url, $path, $scheme, $blog_id) {
    $custom_login_url = get_option('lps_new_login_url', '');
    
    if (strpos($url, 'wp-login.php') !== false) {
        $args = explode('?', $url);
        if (isset($args[1])) {
            parse_str($args[1], $args);
            $url = add_query_arg($args, get_site_url() . "/" . $custom_login_url);
        } else {
            $url = get_site_url() . "/" . $custom_login_url;
        }
    }

    return $url;
}

add_filter('login_url', 'lps_login_url', 10, 3);
function lps_login_url($login_url, $redirect, $force_reauth) {
    if (get_option('lps_new_login_url')) {
        if (mb_strpos($_SERVER['REQUEST_URI'], "wp-admin/install.php")) {
            return admin_url();
        }
        
        if (is_404()) {
            nocache_headers();
            return '#';
        }

        if ($force_reauth === false) {
            return $login_url;
        }

        if (empty($redirect)) {
            return $login_url;
        }

        $redirect = explode('?', $redirect);

        if ($redirect[0] === admin_url('options.php')) {
            $login_url = admin_url();
        }
    }

    return $login_url;
}

