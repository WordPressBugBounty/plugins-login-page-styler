<?php
/** Optional cleanup when plugin is deleted through WordPress. */
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
if ((int) get_option('lps_delete_data_on_uninstall', 0) !== 1) { return; }
global $wpdb;
// Only this plugin's lps_ namespace. Do not touch WordPress users or third-party options.
$pattern = $wpdb->esc_like('lps_') . '%';
// One-time uninstall cleanup: no persistent query cache is appropriate because every match is deleted immediately.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cleanup must enumerate all plugin options, including non-autoloaded options.
$names = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern));
foreach ($names as $name) {
    if (strpos($name, 'lps_') === 0) delete_option($name);
}
