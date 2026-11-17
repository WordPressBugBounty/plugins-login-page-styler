<?php
if (!defined('ABSPATH')) exit;
/** Local admin-only stylesheet and demo controls. No frontend registration. */
function lps_free_five_tabs_assets($hook) {
  if ($hook !== 'toplevel_page_lps_option') return;
  $root = dirname(__DIR__);
  wp_enqueue_style('lps-free-five-tabs',plugins_url('../css/lps-free-five-tabs.css', __FILE__),array(),filemtime($root.'/css/lps-free-five-tabs.css'));
  wp_enqueue_script('lps-free-five-tabs',plugins_url('../js/lps-free-five-tabs.js', __FILE__),array(),filemtime($root.'/js/lps-free-five-tabs.js'),true);
}
add_action('admin_enqueue_scripts','lps_free_five_tabs_assets');
