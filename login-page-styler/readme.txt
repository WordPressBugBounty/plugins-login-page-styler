=== Login Page Styler ===
Contributors: Zia Imtiaz
Tags: login, login-customizer, branding, login-page, templates
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 8.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customize the WordPress login page with a live designer, responsive templates, social sign-in, login security, and flexible access controls.

== Description ==

Login Page Styler brings the appearance and management of your WordPress login experience together in one place. Replace the standard login screen with a branded design, adjust individual elements in a live preview, choose a ready-made layout, and configure the way people sign in to your site.

The plugin is designed for website owners, developers, designers, and agencies who want more control over the WordPress login experience without building a custom login page from scratch.

= Visual Login Page Customizer =

Design your login screen using an integrated preview. Change settings and inspect the result before saving your design.

* Live preview of the login page as you customize it.
* Desktop, tablet, and mobile preview modes.
* Built-in preview refresh control.
* Customize the standard WordPress login layout or select a template.
* Adjust the login form's placement on the screen.
* Configure background, branding, form, field, button, and text appearance.
* Save your design and apply it to the actual WordPress login screen.
* Reset design settings or return to the default login layout when needed.

= Login Page Templates =

Choose from a collection of distinct login screen designs rather than starting with a blank canvas.

* Visual template selection gallery.
* Layout choices for different branding and presentation styles.
* Designs with distinctive forms, input styles, and visual treatments.
* Template-specific styling options alongside the main customizer.
* Ability to customize a selected template to match your website.
* Reset template-specific visual settings to the chosen template's defaults.
* Restore the standard WordPress login design when a custom template is not required.

Templates provide a starting layout. The main customization controls can be used to refine applicable elements of the selected design.

= Background and Page Appearance =

Build a login screen that fits your brand and site design.

* Select a page background color or image.
* Control background image positioning and display behavior.
* Choose image sizing options such as cover and contain.
* Adjust image repetition when appropriate.
* Configure visual transparency and blur effects where supported by the selected layout.
* Refine the overall appearance of the login area and surrounding page.

= Logo and Branding =

Make the login page feel like part of your website.

* Replace the default WordPress login logo with a custom brand image.
* Customize the logo's presentation and sizing.
* Adjust the logo and login form's placement as part of the page layout.
* Style supporting login page text and links.
* Configure the copyright text shown with the login design.

= Form, Input Fields, and Buttons =

Fine-tune the visual elements visitors use to log in.

* Change the form background and its transparency.
* Adjust form spacing, padding, positioning, and appearance.
* Configure border and corner-rounding styles.
* Apply box shadow styling to the login form where available.
* Style username and password fields, including their backgrounds and opacity.
* Configure form labels, field text, and related colors.
* Customize the login button to fit your design.
* Style login-related links and supporting text.
* Preview changes at different screen widths.

= General Login and Account Settings =

Control several common aspects of login and account behavior from the plugin settings.

* Choose whether users can sign in with a username, an email address, or both.
* Configure the Remember Me option, including automatic selection.
* Set a session expiration duration.
* Require password changes after a chosen number of days.
* Select the user roles affected by password-reset requirements.
* Configure password strength requirements.
* Restrict registration using permitted or blocked email domains.
* Manage login-related options from a dedicated General Settings area.
* Choose whether plugin settings are removed during uninstall.

Some account settings affect how existing users sign in. Review their effects before enabling them on a production website.

= Social Login =

Offer convenient account sign-in using supported external identity providers.

* Google sign-in integration.
* Facebook sign-in integration.
* Enable or disable individual providers.
* Configure provider credentials in the plugin settings.
* Control the display of social sign-in buttons on the login screen.
* Choose from different button styles and arrangements.
* Show only the providers you have enabled.
* Integrate social sign-in buttons with customized login layouts.

Social sign-in requires configuration with the corresponding identity provider. Provider policies, callback URLs, and WordPress registration settings can affect sign-in and new-account creation.

= Login Security =

Add configurable login protections and tools for reviewing authentication activity.

* Limit failed login attempts.
* Temporarily lock out repeated unsuccessful sign-in attempts.
* Configure lockout-related behavior and visitor messaging.
* Block selected IP addresses from signing in.
* Restrict login access by country or region where configured.
* Integrate Google reCAPTCHA v2 and v3.
* Configure alerts for failed login activity.
* Review activity through the security dashboard.
* Use date filters to inspect different periods of login activity.

Security features are an additional layer of login protection. They do not replace WordPress updates, strong credentials, appropriate hosting security, or other necessary website protections.

= Login Activity and Logs =

Understand authentication events using the plugin's login activity tools.

* Review user login activity and unsuccessful login attempts.
* Inspect login records from the administration interface.
* Filter security activity by date.
* View activity summaries and charts.
* Export relevant login records to CSV for further review.
* Configure email notifications for supported security events.

Email notifications depend on your WordPress site's mail configuration and the ability of the hosting environment or mail service to deliver messages.

= Login URL, Redirect, and Access Features =

Manage how visitors reach the login screen and where they go after authentication.

* Configure login URL behavior.
* Set login-related redirect options.
* Add login/logout navigation links through the supported menu feature.
* Configure login-protected access where required.
* Create temporary access links for controlled access workflows.
* Revoke temporary access links when they are no longer needed.

Test access and redirect settings carefully, especially when changing the login URL. Keep an administrator session open while verifying that the configured login path works.

= Designed for WordPress Workflows =

Login Page Styler works through the WordPress administration area and the familiar WordPress login process. You can customize the visual presentation, review the result, and manage login options without editing theme templates for each change.

Typical uses include:

* Brand the login page for a business or client website.
* Create a consistent login experience for members and staff.
* Offer Google or Facebook sign-in on supported sites.
* Add controls around repeated failed login attempts.
* Review login activity when investigating sign-in problems.
* Manage access and redirects for a WordPress installation.

== Installation ==

= Install from the WordPress dashboard =

1. Sign in to your WordPress administration dashboard.
2. Go to Plugins > Add New Plugin.
3. Search for "Login Page Styler".
4. Select Install Now, and then Activate.
5. Open the Login Page Styler menu in the WordPress dashboard.
6. Choose a starting design or configure the default login layout.
7. Adjust the settings and review the live preview.
8. Save your changes and check the actual WordPress login page.

= Install using a ZIP file =

1. Go to Plugins > Add New Plugin > Upload Plugin.
2. Choose the Login Page Styler ZIP file.
3. Select Install Now, and then Activate Plugin.
4. Open the plugin's settings from the WordPress dashboard.
5. Configure and save the features you want to use.

= First-time setup =

1. Start with the Login Customizer and select a template or the default login layout.
2. Upload your logo and adjust the background, form, fields, buttons, and links.
3. Use desktop, tablet, and mobile preview modes to check the design.
4. Save the design and open the login page in a separate browser window.
5. If using social login, enter provider credentials and configure the authorized redirect URL with the provider.
6. If using reCAPTCHA, create the appropriate site keys and enter them in the relevant settings.
7. Review security and access controls before enabling restrictions.
8. Test the login, lost-password, logout, and registration flows relevant to your site.

== Frequently Asked Questions ==

= Do I need to know CSS, PHP, or JavaScript? =

No. The live customizer is intended to let you adjust common login page design elements through settings instead of writing code.

= Can I replace the standard WordPress login page appearance? =

Yes. You can customize the standard login layout or choose a different built-in template and then adjust its appearance.

= Can I preview changes before saving them? =

Yes. The login customizer includes a live preview, with desktop, tablet, and mobile viewing options.

= Can I use my own company or website logo? =

Yes. You can replace the standard WordPress logo with your own image and adjust its display.

= Can I use a background image? =

Yes. Background image options include controls for how the image is displayed, positioned, and sized.

= Can I make the login form transparent or adjust its opacity? =

The customizer includes transparency-related controls for supported form and field elements. The available visual effect may depend on the selected template.

= Can I change the position of the login form? =

Yes. The customizer includes form positioning controls for supported layouts.

= Are the templates customizable? =

Yes. Templates provide starting designs, and applicable main customizer settings can be used to refine their appearance.

= What happens if I switch templates? =

Selecting another template changes the design and its template-specific visual settings. Review the preview before saving. Authentication and security settings are separate from visual template selection.

= Can I return to the default WordPress login layout? =

Yes. You can clear the selected custom template and work with the default login layout.

= Does the plugin support responsive login pages? =

The login designs are intended for use across screen sizes. Use the built-in desktop, tablet, and mobile preview options to check your configuration.

= Can visitors sign in with Google? =

Google sign-in is supported. You must set up the appropriate Google OAuth credentials and authorized redirect URI before it can work.

= Can visitors sign in with Facebook? =

Facebook sign-in is supported. You must configure the corresponding Facebook application, authorized domain settings, and provider credentials.

= Will social login automatically create new WordPress users? =

New-account behavior depends on the provider response, the plugin's account handling, and your WordPress registration settings. Test the expected behavior with a separate account before offering social login to visitors.

= Does the plugin include Google reCAPTCHA? =

Yes. It supports reCAPTCHA v2 and v3. You must register your website with the appropriate Google service and configure the matching keys.

= Can I limit failed login attempts? =

Yes. The plugin includes failed-login limits and lockout functionality to help reduce repeated unauthorized sign-in attempts.

= Can I block an IP address from logging in? =

Yes. You can configure IP-based restrictions in the security settings. Be careful not to block an address you need for site administration.

= Can I restrict login access by country? =

The plugin includes country-based login restrictions. Their accuracy can depend on the quality of IP geolocation data and the site's network or proxy setup.

= Can I see login attempts and activity? =

Yes. Login activity tools provide records, dashboard views, date filtering, and CSV export options.

= Can the plugin send emails about failed login attempts? =

Yes, supported security alerts can be configured. Delivery depends on the site's WordPress email setup; using a reliable SMTP or transactional mail service may improve delivery.

= Can I change the WordPress login URL? =

Login URL settings are available. Before changing them, ensure you know how to access the site's administration area and test the new URL while an existing administrator session remains open.

= Can I redirect users after login? =

The plugin includes login-related redirect controls. Configure the desired behavior in the appropriate settings area and test with the user roles that access your site.

= Can I add a login or logout link to a WordPress menu? =

Yes. The plugin includes a login/logout menu item feature for supported navigation workflows.

= Does the plugin support temporary access links? =

Yes. Temporary access link management allows links to be created and revoked. Access should be issued only to people you trust, and you should review active WordPress sessions separately when needed.

= Can I require users to change passwords periodically? =

The General Settings include options for a password-reset interval and the user roles to which it applies.

= Can I set password strength rules? =

Yes. Password-related settings include configurable strength requirements.

= Can I allow only certain email domains to register? =

Yes. The registration settings include allowed-domain and blocked-domain controls.

= Can I choose whether people log in using a username or email address? =

Yes. You can configure supported username and email sign-in behavior.

= Can I change the session duration or Remember Me behavior? =

Yes. General login settings include session expiration and Remember Me controls.

= Will the plugin work with my WordPress theme? =

The plugin styles WordPress's login experience rather than relying on a theme's normal page template. Other plugins or custom code that modify the login process may require compatibility testing.

= Will the plugin prevent every type of attack? =

No. Login restrictions, reCAPTCHA, and monitoring can strengthen sign-in protection, but a secure website also requires updates, strong account security, backups, secure hosting, and proper access management.

= What should I do if I cannot log in after changing security or URL settings? =

If possible, use an existing administrator session to reverse the setting. Otherwise, follow your hosting provider's recovery procedure, which may involve temporarily disabling the plugin through file management. Make sure you can access a recent backup before making significant changes.

= Can I remove the plugin settings when uninstalling? =

The plugin includes a setting related to removing its stored configuration on uninstall. Review that option before deleting the plugin, particularly if you plan to reinstall it later.

== Screenshots ==

1. Login Page Styler customizer with live preview and visual styling controls.
2. Login page template selection gallery with different design choices.
3. Background, logo, and page branding controls.
4. Login form, input fields, button, and typography customization settings.
5. General settings for account, password, registration, and session behavior.
6. Social login settings and supported provider configuration.
7. Login security options including reCAPTCHA, attempt limits, and access restrictions.
8. Login activity dashboard, date filters, and log management.
9. Responsive desktop, tablet, and mobile login preview.
10. Login URL, redirects, and temporary access configuration.

== Changelog ==

= 8.0.1 =
* Corrected release packaging and restored plugin PHP files.


= 8.0.0 =
* Updated visual Login Customizer and responsive preview.
* Added the Modern Minimal free design and admin-only previews of additional templates.
* Introduced clearer Free-versus-premium settings and the Upgrade to Pro information page.
* Corrected default-template frontend restrictions for preview-only premium designs.
* Fixed copyright placement beneath login navigation links and its setting registration.
* Added locally served animation effects and updated slideshow demonstrations.
* Improved admin layout, organized form controls, and asset cache-busting.

== Upgrade Notice ==

= 8.0.0 =
Major update to the login design interface. Back up your settings and test your login page after upgrading from 7.1.2.
