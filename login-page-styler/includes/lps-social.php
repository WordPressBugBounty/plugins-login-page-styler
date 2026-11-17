<?php
/** Admin-only Social Login demonstration in the Free edition. No OAuth or frontend hooks. */
if (!defined('ABSPATH')) exit;
/**
 * Remove options from the temporary v9.9.35.3/35.4 diagnostic release.
 * No sensitive OAuth data is kept by the production login handler.
 */
add_action('admin_init', function () {
    if (false !== get_option('lps_social_debug_enabled', false)) delete_option('lps_social_debug_enabled');
    if (false !== get_option('lps_social_debug_events', false)) delete_option('lps_social_debug_events');
});
function lps_social_providers() {
    // Names only: Free does not ship provider endpoints or authentication code.
    return array(
        'google' => array('name' => 'Google'),
        'facebook' => array('name' => 'Facebook'),
        'linkedin' => array('name' => 'LinkedIn'),
        'discord' => array('name' => 'Discord'),
        'microsoft' => array('name' => 'Microsoft'),
    );
}
/** Keep a stable, administrator-configurable display order without affecting linked identities. */
function lps_social_ordered_providers($o) {
    $providers = lps_social_providers();
    $defaults = array('google'=>10,'facebook'=>20,'linkedin'=>30,'discord'=>40,'microsoft'=>50);
    uksort($providers, function ($a, $b) use ($o, $defaults) {
        $a_order = isset($o['providers'][$a]['order']) ? absint($o['providers'][$a]['order']) : $defaults[$a];
        $b_order = isset($o['providers'][$b]['order']) ? absint($o['providers'][$b]['order']) : $defaults[$b];
        return ($a_order <=> $b_order) ?: ($defaults[$a] <=> $defaults[$b]);
    });
    return $providers;
}
/** Curated gallery: two versatile text-button designs, eight genuinely different variations.
 * Legacy presets remain valid so updating does not alter an existing site's saved design.
 */
function lps_social_presets(){return array(
    'classic'=>'Clean Minimal',
    'midnight'=>'Midnight Luxe',
    'icon-brand'=>'Brand Circles',
    'icon-black'=>'Black Icon Circles',
    'icon-outline'=>'Outlined Icon Circles',
    'icon-square'=>'Modern Icon Squares',
    'icon-soft'=>'Soft Icon Tiles',
    'icon-glass'=>'Glass Icon Tiles',
    'compact-brand'=>'Compact Brand Buttons',
    'pill-outline'=>'Outline Pills'
);}
function lps_social_legacy_presets(){return array(
    'outline'=>'Elegant Outline', 'brand'=>'Brand Glow', 'glass'=>'Frosted Glass',
    'compact'=>'Slimline', 'circle'=>'Soft Rounded', 'pill'=>'Capsule',
    'soft'=>'Floating Card', 'aurora'=>'Aurora Gradient'
);}

function lps_social_options(){return wp_parse_args(array('enabled'=>1,'providers'=>array('google'=>array('enabled'=>1),'facebook'=>array('enabled'=>1))),array('enabled'=>0,'register'=>0,'google_account_chooser'=>0,'preset'=>'classic','layout'=>'vertical','position'=>'below','size'=>'medium','spacing'=>10,'radius'=>12,'padding_x'=>16,'padding_y'=>12,'icon_size'=>19,'button_width'=>100,'button_align'=>'center','button_type'=>'preset','shape'=>'preset','color_mode'=>'preset','shadow'=>'preset','border_mode'=>'preset','divider_style'=>'plain','divider'=>'Or continue with','redirect'=>'','providers'=>array()));}
function lps_social_url($provider){return 'Available after installing Login Page Styler Pro';}
function lps_social_start_url($provider){return ''; /* No OAuth URLs in Free. */}
function lps_social_ready($key,$o){return false; /* Demo only: never authenticated. */}
add_action('admin_menu',function(){add_submenu_page('lps_option','Social Login','Social Login','manage_options','lps_social','lps_social_admin');},20);
function lps_social_assets(){wp_enqueue_style('lps-social',plugin_dir_url(dirname(__FILE__)).'assets/lps-social.css',array(),filemtime(plugin_dir_path(dirname(__FILE__)).'assets/lps-social.css'));}
// Free: styles are loaded only in the Social Login admin submenu.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing for an admin stylesheet; no settings are changed.
add_action('admin_enqueue_scripts',function($hook){if(isset($_GET['page']) && is_string($_GET['page']) && sanitize_key(wp_unslash($_GET['page']))==='lps_social'){lps_social_assets();wp_enqueue_style('lps-modern-admin',plugin_dir_url(dirname(__FILE__)).'css/lps-modern-admin.css',array(),filemtime(plugin_dir_path(dirname(__FILE__)).'css/lps-modern-admin.css'));wp_enqueue_script('lps-social-admin',plugin_dir_url(dirname(__FILE__)).'assets/lps-social.js',array(),filemtime(plugin_dir_path(dirname(__FILE__)).'assets/lps-social.js'),true);}});
function lps_social_button($key,$p,$url='',$preview=false){
    // Provider branding remains consistent; presets style the surrounding frame.
    $label='Sign in with '.$p['name'];
    $mark=array(
        'google'=>'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.4a4.6 4.6 0 0 1-2 3v2.5h3.3c1.9-1.7 2.9-4.2 2.9-7.3Z"/><path fill="#34A853" d="M12 22c2.7 0 5-.9 6.7-2.5L15.4 17c-.9.6-2 1-3.4 1-2.6 0-4.9-1.8-5.7-4.2H2.9v2.6A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.3 13.8a6 6 0 0 1 0-3.6V7.6H2.9a10 10 0 0 0 0 8.8Z"/><path fill="#EA4335" d="M12 6c1.5 0 2.8.5 3.9 1.5l2.9-2.9A9.6 9.6 0 0 0 12 2a10 10 0 0 0-9.1 5.6l3.4 2.6C7.1 7.8 9.4 6 12 6Z"/></svg>',
        'facebook'=>'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="#1877f2"/><path fill="#fff" d="M13.5 22V13h3l.5-3.5h-3.5V7.3c0-1 .3-1.7 1.8-1.7H17V2.5h-2.7c-3 0-5 1.8-5 5v2H6V13h3.3v9Z"/></svg>',
        'linkedin'=>'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><rect width="24" height="24" rx="3" fill="#0a66c2"/><path fill="#fff" d="M5 9h3v10H5zM6.5 4.5a1.7 1.7 0 1 1 0 3.4 1.7 1.7 0 0 1 0-3.4M10 9h2.9v1.4c.6-1.1 1.6-1.6 3.1-1.6 2.6 0 3.5 1.6 3.5 4.1V19h-3v-5.4c0-1.3-.1-2.2-1.6-2.2-1.4 0-1.9 1-1.9 2.2V19h-3z"/></svg>',
        'discord'=>'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><rect width="24" height="24" rx="5" fill="#5865f2"/><path fill="#fff" d="M17.9 7.4a14 14 0 0 0-3.5-1.1l-.4.8a13 13 0 0 0-4 0l-.4-.8a14 14 0 0 0-3.5 1.1C4 10.5 3.4 13.5 3.7 16.5c1.3 1 2.7 1.8 4.2 2.2l.9-1.4c-.5-.2-1-.5-1.5-.8l.3-.2c2.9 1.4 5.9 1.4 8.8 0l.3.2c-.5.3-1 .6-1.5.8l.9 1.4c1.5-.4 2.9-1.2 4.2-2.2.4-3.4-.6-6.3-2.4-9.1zM9.2 14.9c-.8 0-1.4-.7-1.4-1.5s.6-1.5 1.4-1.5 1.4.7 1.4 1.5-.6 1.5-1.4 1.5zm5.6 0c-.8 0-1.4-.7-1.4-1.5s.6-1.5 1.4-1.5 1.4.7 1.4 1.5-.6 1.5-1.4 1.5z"/></svg>',
        'microsoft'=>'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#f25022" d="M1 1h10v10H1z"/><path fill="#7fba00" d="M13 1h10v10H13z"/><path fill="#00a4ef" d="M1 13h10v10H1z"/><path fill="#ffb900" d="M13 13h10v10H13z"/></svg>'
    )[$key] ?? '';
    $inside='<span class="lps-social-mark lps-social-'.esc_attr($key).'" aria-hidden="true">'.$mark.'</span><span class="lps-social-label">'.esc_html($label).'</span>';
    return '<span class="lps-social-button lps-social-provider-'.esc_attr($key).'" aria-label="'.esc_attr($label).'">'.$inside.'</span>';
}
function lps_social_markup($o,$preview=false,$preview_all=false){
    $preset=$o['preset']; if($preset==='auto') $preset=get_option('lps_design_preset')==='nature-pro'?'glass':'classic';
    $buttons=''; 
    foreach(lps_social_ordered_providers($o) as $k=>$p){
        // The main login customizer preview must match the actual login page.
        // Only the social settings editor can render disabled providers for interactive editing.
        if(!$preview_all && !lps_social_ready($k,$o))continue;
        if($preview_all==='sample' && !in_array($k,array('google','facebook'),true))continue;
        $buttons.=lps_social_button($k,$p,lps_social_start_url($k),$preview);
    }
    if(!$buttons)return '';
    $style='--lps-social-gap:'.absint($o['spacing']).'px;--lps-social-radius:'.absint($o['radius']).'px;--lps-social-pad-x:'.absint($o['padding_x']).'px;--lps-social-pad-y:'.absint($o['padding_y']).'px;--lps-social-icon-size:'.absint($o['icon_size']).'px;--lps-social-width:'.absint($o['button_width']).'%;';
    $divider_style=$o['divider_style']==='hidden'?'hidden':$o['divider_style'];
    $visual = ''; foreach(array('button_type'=>'type','shape'=>'shape','color_mode'=>'colors','shadow'=>'shadow','border_mode'=>'border') as $field=>$class){$visual.=' lps-social-'.$class.'-'.esc_attr($o[$field]??'preset');}
    return '<div class="lps-social-wrap lps-social-preset-'.esc_attr($preset).' lps-social-layout-'.esc_attr($o['layout']).' lps-social-size-'.esc_attr($o['size']).' lps-social-align-'.esc_attr($o['button_align']).' lps-social-divider-'.esc_attr($divider_style).$visual.'" style="'.esc_attr($style).'">'.($o['divider']!==''&&$divider_style!=='hidden'?'<div class="lps-social-divider">'.esc_html($o['divider']).'</div>':'').'<div class="lps-social-buttons">'.$buttons.'</div></div>';
}
function lps_social_admin(){if(!current_user_can('manage_options'))return; $o=lps_social_options(); ?>
<div class="lps-modern-shell lps-social-admin">
<div class="lps-admin-header"><div class="lps-admin-brand"><span class="lps-admin-mark">L</span><div><h1>Login Page Styler</h1><p>Social Login Studio — interactive Pro feature preview.</p></div></div><div class="lps-header-version"><span class="lps-live-dot"></span> Social Login <span>Free · Pro Preview</span></div></div>
<div class="lps-social-free-banner" role="note"><div><strong>Social Login is a Pro feature</strong><p>Explore every provider, button template, and display setting with the interactive preview. These demo changes are not saved and never affect your real login page.</p></div><a href="https://cresario.com/login-page-styler/" class="button button-primary" target="_blank" rel="noopener noreferrer">Explore Pro</a></div>
<form id="lps-social-demo-form" autocomplete="off" onsubmit="return false;">
<div class="lps-social-master"><div><h2>Social Login</h2><p>Try the enable switch and preview the buttons. Authentication requires Pro.</p></div><label class="lps-social-toggle"><input type="checkbox" name="lps_social_options[enabled]" value="1" <?php checked($o['enabled']); ?>><span></span> Preview social login</label></div>
<nav class="lps-social-tabs" aria-label="Social login settings"><button type="button" data-panel="providers">Providers</button><button type="button" data-panel="templates">Button Templates</button><button type="button" data-panel="display">Display Settings</button><button type="button" data-panel="advanced">Advanced</button></nav>
<div class="lps-social-studio"><div class="lps-social-editor">
<section data-section="providers"><h2>Authentication providers</h2><p>Explore provider controls, button order, and the authentication setup available in Pro. Do not enter real OAuth credentials: this is a demonstration.</p><?php foreach(lps_social_ordered_providers($o) as $k=>$p): $v=$o['providers'][$k]??array(); ?><details><summary><?php echo esc_html($p['name']); ?> <span class="lps-provider-status"><?php echo lps_social_ready($k,$o)?'Demo only':'Demo only'; ?></span></summary><p><label><input type="checkbox" name="lps_social_options[providers][<?php echo esc_attr($k); ?>][enabled]" value="1" <?php checked(!empty($v['enabled'])); ?>> Enable provider</label></p><p><label>Button order (1–99)<br><input type="number" min="1" max="99" name="lps_social_options[providers][<?php echo esc_attr($k); ?>][order]" value="<?php echo esc_attr($v['order']??(array_search($k,array_keys(lps_social_providers()),true)+1)*10); ?>"></label></p><p><label>Client ID<br><input class="regular-text" name="lps_social_options[providers][<?php echo esc_attr($k); ?>][id]" value="<?php echo esc_attr($v['id']??''); ?>"></label></p><p><label>Client secret (leave blank to retain)<br><input type="password" autocomplete="new-password" class="regular-text" name="lps_social_options[providers][<?php echo esc_attr($k); ?>][secret]"></label> <label><input type="checkbox" name="lps_social_options[providers][<?php echo esc_attr($k); ?>][clear]" value="1"> Clear secret</label></p><p>Callback URL (Pro only)<br><input readonly class="large-text" value="<?php echo esc_attr(lps_social_url($k)); ?>" onclick="this.select()"><button type="button" class="button lps-copy-url">Copy URL</button></p><p>OAuth configuration and real callback URLs are available in the separate Pro plugin.</p></details><?php endforeach; ?></section>
<section data-section="templates"><h2>Button Templates</h2><p>Ten distinctive styles: two classic text-button designs and eight modern variants. Existing sites keep their saved design until you choose a new one.</p><div class="lps-social-grid"><?php $gallery = array('auto'=>'Auto — Match design') + lps_social_presets(); if(isset(lps_social_legacy_presets()[$o['preset']])) $gallery[$o['preset']] = 'Current saved design (legacy): '.lps_social_legacy_presets()[$o['preset']]; foreach($gallery as $k=>$name): ?><label class="lps-social-tile"><input type="radio" name="lps_social_options[preset]" value="<?php echo esc_attr($k); ?>" <?php checked($o['preset'],$k); ?>> <?php echo esc_html($name); ?><?php echo wp_kses(lps_social_markup(array_merge($o,array('preset'=>$k,'layout'=>strpos($k,'icon-')===0?'horizontal':'vertical','divider'=>'','size'=>'medium','button_type'=>'preset','shape'=>'preset','color_mode'=>'preset','shadow'=>'preset','border_mode'=>'preset')),true,'sample'), array('div'=>array('class'=>true,'style'=>true), 'span'=>array('class'=>true,'aria-hidden'=>true,'aria-label'=>true), 'svg'=>array('viewBox'=>true,'width'=>true,'height'=>true,'aria-hidden'=>true), 'path'=>array('fill'=>true,'d'=>true), 'circle'=>array('cx'=>true,'cy'=>true,'r'=>true,'fill'=>true), 'rect'=>array('width'=>true,'height'=>true,'rx'=>true,'fill'=>true))); ?></label><?php endforeach; ?></div></section>
<section data-section="display"><h2>Display Settings</h2><p>Customize spacing, alignment and responsive button layout. Changes are reflected in the live preview.</p>
<div class="lps-social-display-grid">
<p><label>Layout <select name="lps_social_options[layout]"><?php foreach(array('vertical'=>'Stacked — full width','horizontal'=>'Inline — wrapping','side-by-side'=>'Side by side — two columns','grid'=>'Grid — responsive columns') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['layout'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Position <select name="lps_social_options[position]"><?php foreach(array('above'=>'Above login fields','below'=>'Below login fields') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['position'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Button size <select name="lps_social_options[size]"><?php foreach(array('small','medium','large') as $v): ?><option <?php selected($o['size'],$v); ?>><?php echo esc_html(ucfirst($v)); ?></option><?php endforeach; ?></select></label></p>
<p><label>Button content <select name="lps_social_options[button_type]"><?php foreach(array('preset'=>'Use template style','full'=>'Icon + text (full)','compact'=>'Icon + text (compact)','icon'=>'Icon only') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['button_type'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Button shape <select name="lps_social_options[shape]"><?php foreach(array('preset'=>'Use template shape','square'=>'Square','rounded'=>'Rounded square','pill'=>'Pill','circle'=>'Circle') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['shape'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Color treatment <select name="lps_social_options[color_mode]"><?php foreach(array('preset'=>'Use template palette','brand'=>'Provider brand colors','mono'=>'Black & white','custom'=>'Neutral (site-friendly)') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['color_mode'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Shadow <select name="lps_social_options[shadow]"><?php foreach(array('preset'=>'Use template shadow','none'=>'None','soft'=>'Soft','floating'=>'Floating') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['shadow'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Border <select name="lps_social_options[border_mode]"><?php foreach(array('preset'=>'Use template border','none'=>'None','thin'=>'Thin','outline'=>'Bold outline') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['border_mode'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Content alignment <select name="lps_social_options[button_align]"><?php foreach(array('center'=>'Centered','left'=>'Left aligned') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['button_align'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Button gap (px) <input type="number" min="0" max="40" name="lps_social_options[spacing]" value="<?php echo esc_attr($o['spacing']); ?>"></label></p>
<p><label>Corner radius (px) <input type="number" min="0" max="40" name="lps_social_options[radius]" value="<?php echo esc_attr($o['radius']); ?>"></label></p>
<p><label>Horizontal padding (px) <input type="number" min="4" max="40" name="lps_social_options[padding_x]" value="<?php echo esc_attr($o['padding_x']); ?>"></label></p>
<p><label>Vertical padding (px) <input type="number" min="4" max="28" name="lps_social_options[padding_y]" value="<?php echo esc_attr($o['padding_y']); ?>"></label></p>
<p><label>Icon size (px) <input type="number" min="14" max="30" name="lps_social_options[icon_size]" value="<?php echo esc_attr($o['icon_size']); ?>"></label></p>
<p><label>Button group width (%) <input type="number" min="45" max="100" name="lps_social_options[button_width]" value="<?php echo esc_attr($o['button_width']); ?>"></label></p>
<p><label>Divider style <select name="lps_social_options[divider_style]"><?php foreach(array('plain'=>'Simple text','lines'=>'Text between lines','hidden'=>'No divider') as $v=>$label): ?><option value="<?php echo esc_attr($v); ?>" <?php selected($o['divider_style'],$v); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label></p>
<p><label>Divider text <input name="lps_social_options[divider]" value="<?php echo esc_attr($o['divider']); ?>"></label></p>
</div><p class="description">On narrow screens, the side-by-side layout automatically stacks buttons to prevent overflow. Pick a visual preset, then optionally override its shape, content, colors, border, and shadow. Icon-only buttons still have accessible names.</p></section>
<section data-section="advanced"><h2>Advanced</h2><p><label><input type="checkbox" name="lps_social_options[register]" value="1" <?php checked($o['register']); ?>> Allow new social users (Subscriber only; Microsoft requires email confirmation)</label></p><p>Existing accounts must link their provider from their WordPress profile. Facebook requires an available email from Meta. LinkedIn and Discord require verified email claims. Microsoft requires an email confirmation link before first login. Matching email addresses never automatically link accounts. Social sign-in is limited to Subscriber and Customer accounts.</p><p><label><input type="checkbox" name="lps_social_options[google_account_chooser]" value="1" <?php checked($o['google_account_chooser']); ?>> Always show the Google account chooser</label></p><p class="description">When enabled, Google offers an account selection screen on each new sign-in. Google may still use an existing signed-in session; this does not force password re-entry.</p><p><label>Optional local redirect <input class="regular-text" name="lps_social_options[redirect]" value="<?php echo esc_attr($o['redirect']); ?>"></label></p></section>
<div class="lps-social-demo-footer"><span><strong>Preview only.</strong> Changes are temporary and cannot be saved in Free.</span><a class="button button-primary" href="https://cresario.com/login-page-styler/" target="_blank" rel="noopener noreferrer">Get Login Page Styler Pro</a></div></div>
<aside class="lps-social-preview"><div class="lps-preview-heading"><div><strong>Live button preview</strong><span>Illustrative login form · no authentication</span></div><select id="lps-social-preview-theme" aria-label="Preview background"><option value="light">Light</option><option value="dark">Dark</option><option value="nature">Nature</option></select></div><div class="lps-social-stage" data-theme="light"><div class="lps-social-mock"><h3>Welcome back</h3><p>Sign in to your account</p><div id="lps-social-demo" data-auto="<?php echo get_option('lps_design_preset')==='nature-pro'?'glass':'classic'; ?>"><?php echo wp_kses(lps_social_markup($o,true,true), array('div'=>array('class'=>true,'style'=>true), 'span'=>array('class'=>true,'aria-hidden'=>true,'aria-label'=>true), 'svg'=>array('viewBox'=>true,'width'=>true,'height'=>true,'aria-hidden'=>true), 'path'=>array('fill'=>true,'d'=>true), 'circle'=>array('cx'=>true,'cy'=>true,'r'=>true,'fill'=>true), 'rect'=>array('width'=>true,'height'=>true,'rx'=>true,'fill'=>true))); ?></div><div class="lps-social-mock-fields"><span>Username or email</span><div class="lps-social-fake-input"></div><span>Password</span><div class="lps-social-fake-input"></div><div class="lps-social-fake-submit">Log In</div></div></div></div><p class="lps-social-preview-note">This is an admin-only demonstration. Real OAuth sign-in and frontend social buttons are included only with Pro.</p></aside>
</div></form>
</div><?php }
