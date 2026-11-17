<?php
if (!defined('ABSPATH')) exit;
function lps_free_pro_banner($feature) {
  echo '<div class="lps-free-pro-banner" role="note"><div><strong>'.esc_html($feature).' is a Pro feature</strong><p>Try these admin controls to see what Pro offers. No changes are saved, no real records or security actions are performed, and your login page is unaffected.</p></div><a class="button button-primary" href="https://cresario.com/login-page-styler/" target="_blank" rel="noopener noreferrer">Explore Pro</a></div>';
}
function lps_free_security_assets($hook) {
  if (!in_array($hook, array('login-page-styler_page_lps_temp_access', 'login-page-styler_page_lps_block_ip_regions', 'login-page-styler_page_lps_user_login_logs'), true)) return;
  wp_enqueue_style('lps-free-security-ui',plugins_url('../css/lps-free-security-ui.css', __FILE__),array(),filemtime(dirname(__DIR__).'/css/lps-free-security-ui.css'));
  wp_enqueue_script('lps-free-security-demo',plugins_url('../js/lps-free-security-demo.js', __FILE__),array(),filemtime(dirname(__DIR__).'/js/lps-free-security-demo.js'),true);
}
add_action('admin_enqueue_scripts','lps_free_security_assets');
