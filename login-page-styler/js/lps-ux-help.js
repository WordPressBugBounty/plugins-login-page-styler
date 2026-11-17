/* Login Page Styler 9.2.0 — non-destructive settings guidance. */
(function ($) {
  'use strict';
  $(function () {
    var help = {
      'Logo': 'Upload your logo and adjust how it appears above the login form.',
      'Background': 'Choose a color or image for the full login page.',
      'Form': 'Adjust the login card width, background, borders, and position.',
      'Google Fonts': 'Change fonts for labels, fields, buttons, and links.',
      'Button': 'Style the login button and its hover appearance.',
      'Navigation Links': 'Control the appearance of lost-password and back-to-site links.',
      'Login Footer': 'Customize footer text and its appearance.',
      'Error Notice Success Messages': 'Choose colors for WordPress login messages.',
      'Slider Background': 'Configure slideshow backgrounds when enabled.',
      'Custom Css': 'Advanced: CSS here can override templates. Use only when needed.'
    };
    $('.lps-modern-shell .accordion-label').each(function () {
      var label = $(this);
      var name = $.trim(label.text()).replace(/\s+/g, ' ');
      if (!help[name] || label.next('.lps-help-note').length) return;
      label.attr('title', help[name]);
    });
  });
})(jQuery);
