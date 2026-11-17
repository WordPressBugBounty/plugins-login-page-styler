<?php
if (!defined('ABSPATH')) { exit; }

function lps_temp_access_page_callback() {
    $access_expiration_time = DAY_IN_SECONDS; // Example value; not loaded from live settings.
    $current_user_id = 0; // Demo: no account can be granted access.
    $stored_url = ''; // Free never reads or exposes previous access links.
    $active_temp_sessions = 0; // Demo value only.

    ?>
    <div class="lps-modern-shell lps-temp-shell">
        <div class="lps-admin-header">
            <div class="lps-admin-brand"><span class="lps-admin-mark">L</span>
                <div><h1>Login Page Styler</h1><p>Secure temporary access for trusted collaborators.</p></div>
            </div>
            <div class="lps-header-version"><span class="lps-live-dot"></span> Temporary Access <span>v<?php echo esc_html( get_plugin_data( plugin_dir_path(__FILE__) . 'loginPageStyler.php', false, false )['Version'] ); ?></span></div>
        </div>
        <?php lps_free_pro_banner('Temporary Access'); ?>
        <div class="lps-temp-card">
            <div class="lps-temp-heading"><h2><?php esc_html_e('Temporary Access', 'login-page-styler'); ?></h2>
                <p><?php esc_html_e('See how one-time administrator links are managed in Pro. No links are generated in Free.', 'login-page-styler'); ?></p>
            </div>
            <div class="lps-temp-warning"><strong><?php esc_html_e('Pro feature preview:', 'login-page-styler'); ?></strong> <?php esc_html_e('These controls are demonstrations. They cannot grant access, revoke real links, or terminate sessions in Free.', 'login-page-styler'); ?></div>
        <form class="lps-temp-form lps-free-demo-form" onsubmit="return false;">
            <?php
            // No settings API submission in Free.
            // No premium action nonce in Free.
            ?>
            <table class="form-table">
                <input type="hidden" id="current_user_id" value="<?php echo esc_attr($current_user_id); ?>" />

                <tr>
                    <th scope="row"><?php esc_html_e('Access Expiration Time', 'login-page-styler'); ?></th>
                    <td>
                        <select name="lps_access_expiration_time">
                            <optgroup label="Hours">
                                <?php
                                for ($i = 1; $i <= 24; $i++) {
                                    $selected = ($access_expiration_time == $i * HOUR_IN_SECONDS) ? 'selected' : '';
                                    echo "<option value='" . esc_attr($i * HOUR_IN_SECONDS) . "' " . esc_attr($selected) . ">" . esc_html($i . " Hour" . ($i > 1 ? "s" : "")) . "</option>";
                                }
                                ?>
                            </optgroup>
                            <optgroup label="Days">
                                <?php
                                for ($i = 1; $i <= 7; $i++) {
                                    $selected = ($access_expiration_time == $i * DAY_IN_SECONDS) ? 'selected' : '';
                                    echo "<option value='" . esc_attr($i * DAY_IN_SECONDS) . "' " . esc_attr($selected) . ">" . esc_html($i . " Day" . ($i > 1 ? "s" : "")) . "</option>";
                                }
                                ?>
                            </optgroup>
                        </select>
                        <p class="description"><?php esc_html_e('Choose how long an unused link remains valid (1 hour to 7 days). A link can be used only once. This does not automatically end an existing login session.', 'login-page-styler'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Generate Temporary Access URL', 'login-page-styler'); ?></th>
                    <td>
                        <input type="button" id="generate_temp_url_button" class="button button-primary" value="Generate URL" />
                        <input type="text" aria-label="Temporary access URL" id="lps_temp_access_url" value="<?php echo esc_url($stored_url); ?>" readonly />
                        <p id="lps_temp_access_message" class="description"><?php esc_html_e('Generate a new single-use link. Generating a new link invalidates the previous unused link, but does not end sessions already created.', 'login-page-styler'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Revoke Temporary Access', 'login-page-styler'); ?></th>
                    <td>
                        <input type="button" id="revoke_temp_access_button" class="button" value="Revoke Access" />
                        <p class="description"><?php esc_html_e('Disable the current unused link. This does not sign out someone who has already used it.', 'login-page-styler'); ?></p>
                    </td>
                </tr>
                
            </table>
            <div class="lps-temp-session-panel">
                <h3><?php esc_html_e('Temporary Login Sessions', 'login-page-styler'); ?></h3>
                <p class="description">A temporary link creates a WordPress login session. The session count is refreshed after each action. Revoking a link alone does not end that session. The actions below only target sessions recorded by this feature, not your regular administrator session.</p>
                <p class="lps-temp-count"><strong>Tracked active sessions:</strong> <span id="lps_temp_session_count"><?php echo esc_html($active_temp_sessions); ?></span></p>
                <div class="lps-temp-actions">
                    <button type="button" class="button" id="lps_terminate_temp_sessions">Terminate Sessions</button>
                    <button type="button" class="button" id="lps_revoke_and_terminate">Revoke Link &amp; Log Out</button>
                </div>
                <p class="description"><strong>Terminate Sessions:</strong> Signs out people who used a temporary link generated while session tracking was enabled. The unused link remains valid.</p>
                <p class="description"><strong>Revoke Link &amp; Log Out:</strong> Disables the unused link and signs out tracked temporary sessions together.</p>
                <p class="description"><strong>Important:</strong> Sessions issued by older plugin versions (before 9.9.18) cannot be identified individually. To remove those, use Users → Profile → Session Management → Log Out Everywhere Else. Terminating a session does not undo changes a visitor already made.</p>
                <p id="lps_temp_session_message" role="status" aria-live="polite"></p>
            </div>

            <p class="lps-free-demo-note"><strong>Preview only:</strong> expiration settings cannot be saved in Free. <a href="https://cresario.com/login-page-styler/" target="_blank" rel="noopener noreferrer">Get Pro</a></p>
        </form>
        </div>
    </div>
    <?php
}
