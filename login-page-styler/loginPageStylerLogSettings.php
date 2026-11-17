<?php
if (!defined('ABSPATH')) exit;
/** Free preview: sample data only. Real login-event storage, emails, exports and endpoints live in Pro. */
function lps_display_login_logs() {
    if (!current_user_can('manage_options')) wp_die('Not permitted');
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filtered sample-log view; the filter is constrained to a fixed allowlist.
    $view = isset($_GET['log_type']) && is_string($_GET['log_type']) && sanitize_key(wp_unslash($_GET['log_type'])) === 'failed' ? 'failed' : 'success';
    ?>
    <div class="wrap lps-log-wrap">
    <style>
    .lps-log-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}.lps-log-metric{padding:18px;background:#f7faff;border:1px solid #dce5f0;border-radius:12px}.lps-log-metric strong{display:block;font-size:29px;margin-top:9px;color:#16395f}.lps-log-chart{overflow-x:auto}.lps-log-chart svg{width:100%;height:210px;min-width:420px}.lps-log-filters{display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin:0 0 18px}.lps-log-filters label{display:flex;flex-direction:column;gap:5px;font-weight:600}.lps-log-filters input,.lps-log-filters select{min-height:34px}@media(max-width:700px){.lps-log-metrics{grid-template-columns:1fr}}.lps-log-wrap{max-width:1150px;color:#233047}.lps-log-hero{background:linear-gradient(110deg,#19325a,#34548a);padding:26px 30px;border-radius:14px;color:#fff;margin:18px 0 22px}.lps-log-hero h1{color:#fff;font-size:26px;margin:0 0 8px}.lps-log-hero p{color:#dce6f6;margin:0}.lps-log-panel{background:#fff;border:1px solid #dce4ee;border-radius:12px;padding:22px;margin-top:18px}.lps-log-tabs{display:flex;gap:12px;margin-bottom:20px}.lps-log-tabs a{padding:10px 16px;text-decoration:none;border-radius:8px;background:#f0f4fa;color:#233047;font-weight:600}.lps-log-tabs a.active{background:#1e477b;color:#fff}.lps-log-controls{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.lps-log-controls form{display:flex;align-items:center;gap:8px}.lps-log-table{width:100%;border-collapse:collapse}.lps-log-table th,.lps-log-table td{text-align:left;padding:13px 10px;border-bottom:1px solid #e7edf4}.lps-log-table th{background:#f7f9fc}.lps-log-table td{overflow-wrap:anywhere}.lps-log-pill{display:inline-block;border-radius:100px;background:#e8f7ef;color:#126b43;padding:5px 10px;font-weight:600}.lps-log-pill.failed{background:#fff0ef;color:#a02e2b}.lps-log-note{color:#617085;line-height:1.6}.lps-log-pagination{margin-top:18px;display:flex;gap:8px;align-items:center}.lps-log-scroll{overflow-x:auto}@media(max-width:782px){.lps-log-controls{align-items:stretch;flex-direction:column}.lps-log-controls form{flex-wrap:wrap}}
    .lps-retention-form{display:flex;align-items:end;gap:14px;flex-wrap:wrap}.lps-retention-form label{display:flex;flex-direction:column;gap:6px;font-weight:600}.lps-retention-form select{min-width:150px}.lps-risk-label{display:inline-block;border-radius:999px;padding:5px 11px;font-weight:600;background:#e8f7ef;color:#126b43}.lps-risk-medium{background:#fff4db;color:#8b5800}.lps-risk-high{background:#ffe9e9;color:#aa2525}.lps-alert-form{display:flex;flex-wrap:wrap;align-items:end;gap:14px}.lps-alert-form label{display:flex;flex-direction:column;gap:6px;font-weight:600}.lps-alert-form label.lps-alert-check{flex-direction:row;align-items:center}.lps-alert-form input[type=number]{width:110px}.lps-alert-form input[type=email]{width:270px;max-width:100%}
    </style>
    <div class="lps-log-hero"><h1>User Login Logs</h1><p>Explore Pro login activity and security reporting with example records.</p></div>
    <?php lps_free_pro_banner('User Login Logs'); ?>
    <div class="lps-log-panel"><h2>Login history retention</h2>
      <p class="lps-log-note">Choose how long successful and failed login records would be kept in Pro.</p>
      <form class="lps-retention-form lps-free-demo-form" onsubmit="return false;">
        <label>Keep login records for <select><option>7 days</option><option selected>30 days</option><option>60 days</option><option>90 days</option></select></label>
        <button class="button button-primary" type="button" data-lps-demo-action="save">Save retention settings</button>
      </form>
      <h3>Security notification emails</h3>
      <p class="lps-log-note">In Pro, you can receive alerts for suspicious login activity and send test emails.</p>
      <form class="lps-alert-form lps-free-demo-form" onsubmit="return false;">
        <label>Failed attempts threshold<input type="number" value="5" min="3" max="50"></label>
        <label>Send alerts to<input type="email" placeholder="admin@example.com"></label>
        <label class="lps-alert-check"><input type="checkbox" checked> Include failed-attempt count</label>
        <button type="button" class="button" data-lps-demo-action="save">Save alert settings</button>
        <button type="button" class="button" data-lps-demo-action="email">Send Test Email</button>
      </form>
    </div>
    <div class="lps-log-panel"><h2>Suspicious login monitoring</h2><p class="lps-log-note">Illustrative monitoring summary — not real login activity.</p>
      <div class="lps-log-metrics"><div class="lps-log-metric">Successful logins <strong>28</strong></div><div class="lps-log-metric">Failed logins <strong>7</strong></div><div class="lps-log-metric">Unique IPs <strong>12</strong></div></div>
      <div class="lps-log-chart"><svg role="img" aria-label="Example seven-day login chart" viewBox="0 0 650 180" preserveAspectRatio="none"><line x1="15" y1="150" x2="625" y2="150" stroke="#dce4ee" stroke-width="2"/><polyline points="20,125 120,100 220,115 320,65 420,90 520,40 620,75" fill="none" stroke="#2d669d" stroke-width="4" stroke-linecap="round"/><polyline points="20,145 120,137 220,145 320,122 420,140 520,126 620,135" fill="none" stroke="#d65a5a" stroke-width="3"/></svg></div>
    </div>
    <div class="lps-log-panel"><div class="lps-log-tabs">
    <a class="<?php echo $view === 'success' ? 'active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=lps_user_login_logs&log_type=success')); ?>">Successful Logins</a>
    <a class="<?php echo $view === 'failed' ? 'active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=lps_user_login_logs&log_type=failed')); ?>">Failed Logins</a></div>
    <div class="lps-log-controls"><form class="lps-free-demo-form" onsubmit="return false;"><label>Period <select><option>Today</option><option selected>7 days</option><option>30 days</option><option>All time</option></select></label><input placeholder="Search username or IP" type="search"><button class="button" type="button" data-lps-demo-action="filter">Apply filters</button></form><button class="button button-secondary" type="button" data-lps-demo-action="csv">Export CSV</button></div>
    <div class="lps-log-scroll"><table class="lps-log-table"><thead><tr><th>Username</th><th>Date & time</th><th>IP</th><th>Browser</th><th>Device</th><th>Status</th></tr></thead><tbody>
    <tr><td>demo_editor</td><td>Example: 09:24</td><td>192.0.2.10</td><td>Chrome</td><td>Desktop</td><td><span class="lps-log-pill<?php echo $view === 'failed' ? ' failed' : ''; ?>"><?php echo $view === 'failed' ? 'Failed' : 'Success'; ?></span></td></tr>
    <tr><td>demo_member</td><td>Example: 08:12</td><td>198.51.100.24</td><td>Firefox</td><td>Mobile</td><td><span class="lps-log-pill<?php echo $view === 'failed' ? ' failed' : ''; ?>"><?php echo $view === 'failed' ? 'Failed' : 'Success'; ?></span></td></tr>
    </tbody></table></div><p class="lps-log-note">These are demonstration records from reserved documentation IP ranges, not actual users or visits.</p></div>
    <p class="lps-free-demo-note">Preview only. Login monitoring, storage, exports, alert emails and activity reports require the separate Pro edition.</p>
    </div>
    <?php
}
