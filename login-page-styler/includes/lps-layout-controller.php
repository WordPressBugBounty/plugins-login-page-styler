<?php
/**
 * Legacy login position behavior, restored from the original plugin.
 * This file is the sole owner of form placement on the actual login page.
 * It does not add any HTML wrapper, flex container, or visible panel.
 */
if (!defined('ABSPATH')) exit;

function lps_legacy_position_padding($position, $hide_blog = null, $hide_nav = null, $hide_logo = null) {
    $position = (int) $position;
    // These arguments let the live preview calculate the same values from
    // unsaved controls. On the frontend, use the saved WordPress options.
    if ($hide_blog === null) $hide_blog = get_option('lps_login_blog_link_hide');
    if ($hide_nav === null) $hide_nav = get_option('lps_login_nav_link_hide');
    if ($hide_logo === null) $hide_logo = get_option('lps_login_logo_hide');
    $blog = (string) $hide_blog === '1';
    $nav  = (string) $hide_nav === '1';
    $logo = (string) $hide_logo === '1';

    switch ($position) {
        case 1: return 'top:0;right:0;bottom:0;left:0;padding:10% 0 0 0!important;';
        case 2: return 'top:0;right:auto;bottom:0;left:0;padding:10% 70% 0 0!important;';
        case 3: return 'top:0;right:0;bottom:0;left:auto;padding:10% 0 0 70%!important;';
        case 4: return 'top:0;right:auto;bottom:auto;left:auto;padding:1% 0 0 0!important;';
        case 5: return 'top:0;right:auto;bottom:0;left:0;padding:1% 70% 0 0!important;';
        case 6: return 'top:0;right:0;bottom:0;left:0;padding:1% 0 0 70%!important;overflow:hidden;';
        case 7:
        case 8:
        case 9:
            // Original bottom-row offsets depend on which login elements are visible.
            if ($blog && $logo && !$nav) $top = '36%';
            elseif ($blog && $nav && $logo) $top = '36%';
            elseif (!$blog && $nav && $logo) $top = '32%';
            elseif ($blog && !$nav && !$logo) $top = '26%';
            elseif (!$blog && $nav && !$logo) $top = '26%';
            elseif (!$blog && !$nav && $logo) $top = '30%';
            elseif ($blog && $nav && !$logo) $top = '29%';
            else $top = '23.5%';
            $right = $position === 8 ? '70%' : '0';
            $left  = $position === 9 ? '70%' : '0';
            return 'top:auto;right:auto;bottom:auto;left:auto;padding:'.$top.' '.$right.' 0 '.$left.'!important;';
        default: return 'padding:8% 0 0 0!important;';
    }
}

/** Nine reliable positions for the Modern Minimal design (also used by live preview). */
function lps_modern_minimal_position_css($position) {
    $position = (int) $position;
    if ($position < 1 || $position > 9) $position = 1;
    $vertical = in_array($position, array(4, 5, 6), true) ? 'flex-start' : (in_array($position, array(7, 8, 9), true) ? 'flex-end' : 'center');
    $horizontal = in_array($position, array(2, 5, 8), true) ? 'flex-start' : (in_array($position, array(3, 6, 9), true) ? 'flex-end' : 'center');
    return 'body.login.lps-modern-minimal{min-height:100vh!important;display:flex!important;flex-direction:column!important;align-items:' . $horizontal . '!important;justify-content:' . $vertical . '!important;padding:24px!important;box-sizing:border-box!important;}'
         . 'body.login.lps-modern-minimal #login{width:min(420px,100%)!important;max-width:100%!important;min-width:0!important;position:relative!important;top:auto!important;right:auto!important;bottom:auto!important;left:auto!important;float:none!important;margin:0!important;padding:0!important;box-sizing:border-box!important;}'
         . 'body.login.lps-modern-minimal #login form{box-sizing:border-box!important;max-width:100%!important;}';
}

function lps_layout_position_css($position, $hide_blog = null, $hide_nav = null, $hide_logo = null) {
    // Split Studio always places the real form in the accessible right-hand panel.
    // The art panel is decorative and collapses on narrow viewports.
    if (get_option('lps_design_preset','default') === 'split-studio') {
        return 'body.login.lps-split-studio{display:flex!important;align-items:var(--lps-split-align,center)!important;justify-content:flex-end!important;padding:var(--lps-split-padding,32px) max(3%,20px) var(--lps-split-padding,32px) calc(var(--lps-split-width,51%) + 3%)!important;min-height:100vh!important;box-sizing:border-box!important;}body.login.lps-split-studio #login{width:min(var(--lps-split-form-width,440px),100%)!important;max-width:100%!important;min-width:0!important;position:relative!important;top:auto!important;right:auto!important;bottom:auto!important;left:auto!important;float:none!important;margin:0!important;padding:0!important;box-sizing:border-box!important;}body.login.lps-split-studio.lps-split-right{padding-left:max(3%,20px)!important;padding-right:calc(var(--lps-split-width,51%) + 3%)!important;justify-content:flex-start!important;}@media(max-width:760px){body.login.lps-split-studio,body.login.lps-split-studio.lps-split-right{padding:32px 18px!important;justify-content:center!important;}}';
    }
    // Aurora's glass card uses a wider centered presentation than Modern Minimal.
    // Keep this in the shared layout controller so frontend and iframe agree.
    if (get_option('lps_design_preset', 'default') === 'aurora-glass') {
        return 'body.login.lps-aurora-glass{min-height:100vh!important;display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;padding:26px 16px!important;box-sizing:border-box!important;}'
             . 'body.login.lps-aurora-glass #login{width:min(1020px,100%)!important;max-width:100%!important;position:relative!important;top:auto!important;right:auto!important;bottom:auto!important;left:auto!important;float:none!important;margin:auto!important;box-sizing:border-box!important;}';
    }
    // Modern Minimal moves the complete login panel instead of shrinking it with 70% padding.
    if (in_array(get_option('lps_design_preset', 'default'), array('modern-minimal','nature-pro','aurora-glass'), true)) {
        return str_replace('lps-modern-minimal', get_option('lps_design_preset') === 'nature-pro' ? 'lps-nature-pro' : (get_option('lps_design_preset') === 'aurora-glass' ? 'lps-aurora-glass' : 'lps-modern-minimal'), lps_modern_minimal_position_css($position));
    }
    return 'div#login{' . lps_legacy_position_padding($position, $hide_blog, $hide_nav, $hide_logo) . '}';
}

function lps_layout_output() {
    if (!lps_design_should_render()) return;
    // No injected wrapper, body flex layout, script or relocated copyright.
    lps_print_inline_style('lps-layout-position', lps_layout_position_css(get_option('lps_login_form_position', '')));
}
add_action('login_head', 'lps_layout_output', PHP_INT_MAX);
