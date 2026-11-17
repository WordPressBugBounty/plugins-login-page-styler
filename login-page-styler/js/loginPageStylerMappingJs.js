jQuery(document).ready(function ($) {

/// Define a mapping between input fields, target elements, and CSS properties
var mappings = {
    'lps_login_logo': { targetSelector: '.login h1 a', cssProperty: 'background-image' },
    'lps_body_bg_img': { targetSelector: 'body', cssProperty: 'background-image' },
    'lps_login_form_bg': { targetSelector: '.login form', cssProperty: 'background-image', additionalProperties: { 'background-size': 'cover' } }
    // Add more mappings as needed
};

// Function to set the target element with the image URL and additional properties
function setTargetElement(targetSelector, imageUrl, additionalProperties) {
    var iframe = $('#lps-iframe')[0];
    var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    var $iframeContent = jQuery(iframeContent);

    var targetElement = $iframeContent.find(targetSelector);

    if (imageUrl.trim()) {
        // Apply the background image if the URL is not empty
        targetElement.css('background-image', 'url("' + imageUrl + '")');
    } else {
        // Do nothing if the URL is empty
        targetElement.css('background-image', '');
    }

    // Apply additional properties if any
    if (additionalProperties) {
        targetElement.css(additionalProperties);
    }
}

// Function to initialize media uploader for a specific input field
function initializeMediaUploader(inputId) {
    var inputField = $('#' + inputId);
    var mapping = mappings[inputId];
    var targetSelector = mapping.targetSelector;
    var additionalProperties = mapping.additionalProperties;

    // Use the 'input' event for immediate feedback
    inputField.on('input', function () {
        var imageUrl = $(this).val();

        // Set the target element with the selected image URL or do nothing
        setTargetElement(targetSelector, imageUrl, additionalProperties);

        if (imageUrl.trim()) {
            $('#previewText').text('Selected Image: ' + imageUrl);
        } else {
            $('#previewText').text('No Image Selected');
        }
    });

    $(document).on('imageSelected', function (event, imageUrl) {
        if (inputField.val() === imageUrl) {
            $('#previewText').text('Selected Image: ' + imageUrl);
            setTargetElement(targetSelector, imageUrl, additionalProperties);
        }
    });
}

// Function to update background size based on width and height sliders
function initializeBgSizeSliders() {
    $('#lps_login_logo_width, #lps_login_logo_height').on('input', function () {
        var widthValue = $('#lps_login_logo_width').val() + 'px';
        var heightValue = $('#lps_login_logo_height').val() + 'px';
        var sizeValue = widthValue + ' ' + heightValue; // Combine width and height

        var targetSelector = mappings['lps_login_logo'].targetSelector;
        var iframe = $('#lps-iframe')[0];
        var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
        var $iframeContent = jQuery(iframeContent);
        var targetElement = $iframeContent.find(targetSelector);

        targetElement.css('background-size', sizeValue);
        $('#bgWidthValue').text(widthValue); // Update width display
        $('#bgHeightValue').text(heightValue); // Update height display
    });
}

// Example usage
initializeMediaUploader('lps_login_logo');
initializeMediaUploader('lps_body_bg_img');
initializeMediaUploader('lps_login_form_bg');
initializeBgSizeSliders(); // Initialize the range sliders

// Add more input field initializations as needed

    // Rest of your code...




// Add more input field initializations as needed
// Input field change event for color picker
  function initializeColorPicker(colorInputId, targetSelector, cssProperty, isHover = false) {
    // Color Picker Initialization
    $('#' + colorInputId).wpColorPicker({
        change: function () {
            updateElementStyle(colorInputId, targetSelector, cssProperty, isHover);
        }
    });

    // Opacity Slider Initialization
    var opacityInputId = getOpacityInputId(colorInputId);
    if (opacityInputId) {
        $('#' + opacityInputId).on('input', function () {
            updateElementStyle(colorInputId, targetSelector, cssProperty, isHover);
        });
    }
}

// Function to update the target element style
function updateElementStyle(colorInputId, targetSelector, cssProperty, isHover) {
    var selectedColor = $('#' + colorInputId).wpColorPicker('color');
    var opacityInputId = getOpacityInputId(colorInputId);
    var opacity = opacityInputId ? $('#' + opacityInputId).val() : 1;
    opacity = Math.min(Math.max(opacity, 0), 1); // Ensure opacity is between 0 and 1

    var colorWithOpacity = rgba(selectedColor, opacity);
    
    // Determine if !important should be added
    var useImportant = (colorInputId === 'lps_login_form_input_color_opacity' && cssProperty === 'background');

    var iframe = $('#lps-iframe')[0];
    if (!iframe) return;

    var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    if (!iframeContent) return;

    var $iframeContent = jQuery(iframeContent);
    var targetElement = $iframeContent.find(targetSelector);

    if (isHover) {
        // Add or update a style element for hover styles
        var styleElement = $iframeContent.find('#dynamic-hover-styles');
        if (styleElement.length === 0) {
            styleElement = $('<style id="dynamic-hover-styles"></style>').appendTo($iframeContent.find('head'));
        }

        // Clear existing hover styles
        styleElement.html('');

        // Update or add hover styles
        var hoverStyle = targetSelector + ':hover {' +
            cssProperty + ': ' + colorWithOpacity + ' !important;' +
            '}';

        styleElement.append(hoverStyle);
    } else {
        // Apply the style to the target element within the iframe
        var styleValue = colorWithOpacity + (useImportant ? ' !important' : '');

        // If the property is border-color, also set the border style
        if (cssProperty === 'border-color') {
            targetElement.css({
                'border': '1px solid ' + styleValue,
                'border-color': styleValue
            });
        } else {
            targetElement.css(cssProperty, styleValue);
        }
    }
}

// Function to get the corresponding opacity input ID
function getOpacityInputId(colorInputId) {
    for (var opacityId in opacityMappings) {
        if (opacityMappings[opacityId] === colorInputId) {
            return opacityId;
        }
    }
    return null;
}

// Helper function to convert color to RGBA
function rgba(color, opacity) {
    // Extract RGB values from the color (assuming it's in hex format)
    var r, g, b;
    if (color.startsWith('#')) {
        var hex = color.slice(1);
        r = parseInt(hex.substring(0, 2), 16);
        g = parseInt(hex.substring(2, 4), 16);
        b = parseInt(hex.substring(4, 6), 16);
    } else {
        throw new Error('Unsupported color format');
    }
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

// Define opacity mappings
var opacityMappings = {
    'lps_login_form_color_opacity': 'lps_login_form_color',
    'lps_login_form_input_color_opacity': 'lps_login_form_input_color',
    'lps_box_shadow_opacity': 'lps_box_shadow_color'

};


var lpsTemplate = lpsData.layout;

switch (lpsTemplate) {
    case 'lay1':
        initializeColorPicker('lps_login_form_color', '.login form, .login h1', 'background');
        break;
    case 'lay2':
        initializeColorPicker('lps_login_form_color', '.login form, .login h1', 'background');
        break;
    case 'lay3':
        initializeColorPicker('lps_login_form_color', '.login form, .login h1', 'background');
        break;
    default:
        initializeColorPicker('lps_login_form_color', '.login form', 'background');
        break;
}
// Usage:
initializeColorPicker('lps_login_button_color', '.login .button-primary', 'background-color');
initializeColorPicker('lps_login_button_text_color', '.login .button-primary', 'color');
initializeColorPicker('lps_login_button_border_color', '.login .button-primary', 'border-color');
initializeColorPicker('lps_login_button_color_hover', '.login .button-primary', 'background-color', true);
initializeColorPicker('lps_login_button_text_color_hover', '.login .button-primary', 'color', true);
initializeColorPicker('lps_login_button_border_color_hover', '.login .button-primary', 'border-color', true);
initializeColorPicker('lps_textlogo_color', '.login h1 a', 'color');
initializeColorPicker('lps_textlogo_color_hover', '.login h1 a', 'color', true);
initializeColorPicker('lps_login_background_color', 'body', 'background-color');
initializeColorPicker('lps_login_label_color', '.login label', 'color');
initializeColorPicker('lps_login_form_input_color', '.login form .input', 'background');
initializeColorPicker('lps_login_form_input_text_color', '.login form .input', 'color');
initializeColorPicker('lps_login_form_border_color', '.login form', 'border-color');
initializeColorPicker('lps_login_form_input_feild_border_color', '.login form .input', 'border-color');
initializeColorPicker('lps_login_nav_color', '#backtoblog a, #nav a', 'color');
initializeColorPicker('lps_login_nav_hover_color', '#backtoblog a, #nav a', 'color', true);
initializeColorPicker('lps_login_nav_bgcolor', '#backtoblog a, #nav a', 'background');
initializeColorPicker('lps_login_nav_bgcolor_hover', '#backtoblog a, #nav a', 'background',true);
initializeColorPicker('lps_copyright_color', '.login .copyright', 'color');
initializeColorPicker('lps_box_shadow_color', '.login form', 'box-shadow');


    // Add more color picker initializations as needed


    function initializeCheckbox(inputId, targetSelector, cssProperty) {
        // Checkbox change event
        $('#' + inputId).on('change', function () {
            // Get the checkbox state
            var isChecked = this.checked;
    
            // Update the text in the preview based on the checkbox state
            $('#previewText').text(isChecked ? 'Checkbox is checked' : 'Checkbox is unchecked');
    
            // Access the iframe content
            var iframe = $('#lps-iframe')[0];
            var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    
            // Use WordPress provided jQuery
            var $iframeContent = jQuery(iframeContent);
    
            // Get the corresponding target element within the iframe
            var targetElement = $iframeContent.find(targetSelector);
    
            // Toggle display property based on checkbox state
            targetElement.css(cssProperty, isChecked ? 'none' : 'block');
        });
    }
    
    // Example usage:
    initializeCheckbox('myonoffswitch1', '.some-target-class', 'color');
    initializeCheckbox('myonoffswitch2', '.login h1 a', 'display');
    initializeCheckbox('myonoffswitch3', '#login_error,.login .message', 'display');
    initializeCheckbox('myonoffswitch4', '.login #nav', 'display');
    initializeCheckbox('myonoffswitch5', '.login #backtoblog', 'display');
    
    function initializeSelect(inputId, targetSelector, cssProperty) {
        // Select field change event
        $('#' + inputId).on('input', function () {
            // Get the selected value
            var value = $(this).val();
    
            // Access the iframe content
            var iframe = $('#lps-iframe')[0];
            if (!iframe) return;
    
            var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
            if (!iframeContent) return;
    
            // Use WordPress provided jQuery
            var $iframeContent = jQuery(iframeContent);
    
            // Get the corresponding target element within the iframe
            var targetElement = $iframeContent.find(targetSelector);
            if (targetElement.length === 0) return;
    
            // Update the CSS property with the selected value
            targetElement.each(function() {
                $(this).css(cssProperty, value);
                // Adding important rule using CSSText
                var cssText = $(this).attr('style') || '';
                $(this).attr('style', cssText + cssProperty + ': ' + value + ' !important;');
            });
        });
    }
    
    // Example usage:
    initializeSelect('lps_login_form_border_style', '.login form', 'border-style');
    initializeSelect('lps_login_form_input_border_style', '.login form .input', 'border-style');
    




 // Define a mapping between input fields, target elements, and CSS properties for range sliders
var rangeMappings = {
    'lps_login_logo_width': { targetSelector: '.login h1 a', cssProperty: 'width' },
    'lps_login_logo_height': { targetSelector: '.login h1 a', cssProperty: 'height' },
    'lps_login_label_size': { targetSelector: '.login label[for="user_login"], .login label[for="user_pass"], .login label[for="user_email"]', cssProperty: 'font-size' },
    'lps_login_remember_label_size': { targetSelector: '.login label[for="rememberme"]', cssProperty: 'font-size' },
    'lps_login_form_width': { targetSelector: '#login', cssProperty: 'width' },
    'lps_login_form_border_size': { targetSelector: '.login form', cssProperty: 'border-width' },
    'lps_login_form_border_radius': { targetSelector: '.login form', cssProperty: 'border-radius' },
    'lps_login_form_input_border_size': { targetSelector: '.login form .input', cssProperty: 'border-width' },
    'lps_login_form_input_feild_border_radius': { targetSelector: '.login form .input', cssProperty: 'border-radius' },
    'lps_login_button_size': { targetSelector: '.login .button-primary', cssProperty: 'width' },
    'lps_login_button_border_radius': { targetSelector: '.login .button-primary', cssProperty: 'border-radius' },
    'lps_login_nav_size': { targetSelector: '.login #nav, .login #backtoblog', cssProperty: 'font-size' }
};

// Function to set the target element with the range slider value
function setRangeTargetElement(targetSelector, cssProperty, value) {
    var iframe = $('#lps-iframe')[0];
    var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    var $iframeContent = jQuery(iframeContent);

    var targetElement = $iframeContent.find(targetSelector);
    
    targetElement.each(function() {
        this.style.setProperty(cssProperty, value, 'important'); // Use 'important' if necessary
    });
}

// Function to initialize range slider for a specific input field
function initializeRangeSlider(inputId) {
    var inputField = $('#' + inputId);
    var mapping = rangeMappings[inputId];
    var targetSelector = mapping.targetSelector;
    var cssProperty = mapping.cssProperty;

    inputField.on('input', function() {
        var value = $(this).val();
        $('#amountInput' + inputId.charAt(inputId.length - 1)).val(value);
        setRangeTargetElement(targetSelector, cssProperty, value + 'px');
    });

    // Initialize the number input
    $('#amountInput' + inputId.charAt(inputId.length - 1)).on('input', function() {
        var value = $(this).val();
        inputField.val(value);
        setRangeTargetElement(targetSelector, cssProperty, value + 'px');
    });
}

// Example usage:
initializeRangeSlider('lps_login_logo_width');
initializeRangeSlider('lps_login_logo_height');
initializeRangeSlider('lps_login_label_size');
initializeRangeSlider('lps_login_remember_label_size');
initializeRangeSlider('lps_login_form_width');
initializeRangeSlider('lps_login_form_border_size');
initializeRangeSlider('lps_login_form_border_radius');
initializeRangeSlider('lps_login_form_input_border_size');
initializeRangeSlider('lps_login_form_input_feild_border_radius');
initializeRangeSlider('lps_login_button_size');
initializeRangeSlider('lps_login_button_border_radius');
initializeRangeSlider('lps_login_nav_size');

// Update the same footer node used on the published login page.
function initializeCopyrightPreview(inputId) {
    $('#' + inputId).on('input change', function () {
        var companyName = $(this).val().trim();
        var iframe = $('#lps-iframe')[0];
        if (!iframe || !iframe.contentDocument) return;
        var doc = iframe.contentDocument;
        var login = doc.getElementById('login');
        if (!login) return;
        var notice = doc.getElementById('lps-login-copyright') || doc.querySelector('.copyright');
        if (!companyName) {
            if (notice) notice.remove();
            return;
        }
        if (!notice) {
            notice = doc.createElement('div');
            notice.className = 'copyright';
            notice.id = 'lps-login-copyright';
        }
        // The footer must be beneath #nav and #backtoblog, not on the left
        // edge of the iframe's body. Append is safe to call repeatedly.
        if (notice.parentNode !== login || login.lastElementChild !== notice) {
            login.appendChild(notice);
        }
        notice.textContent = '© ' + new Date().getFullYear() + ' ' + companyName + '. All rights reserved.';
    });
}

// Initialize copyright input
initializeCopyrightPreview('lps_login_copyright');




// JavaScript for Custom Login Page Styler

// Define a mapping for text logo properties
var textLogoMappings = {
    'lps_login_text_logo': { targetSelector: '.login h1 a', method: 'text' },
    'font_size': { targetSelector: '.login h1 a', cssProperty: 'font-size' },
    'font_weight': { targetSelector: '.login h1 a', cssProperty: 'font-weight' },
    'color': { targetSelector: '.login h1 a', cssProperty: 'color' },
};

// Define a mapping for Google Fonts
var fontMappings = {
    'gfonttextlogo': { targetSelector: '.login h1 a', method: 'font-family' },
    'gfontlabel': { targetSelector: '.login label', method: 'font-family' },
    'gfontbtn': { targetSelector: '.login .button-primary', method: 'font-family' },
    'gfontlink': { targetSelector: '.login #nav, .login #backtoblog', method: 'font-family' },
    'gfontmsg': { targetSelector: '.login #login_error, .login .message, .login .success', method: 'font-family' },
    'gfontcopyright': { targetSelector: '.login .copyright', method: 'font-family' },
    'gfontinput': { targetSelector: '.login .input', method: 'font-family' }
};

// Function to unbind the text logo
function unbindTextLogo($iframeContent) {
    $iframeContent.find('.login h1 a').removeClass('custom-text-logo').text(''); // Clear the text
    removeInjectedCSS($iframeContent, true); // Remove only logo styles
}

// Function to set text logo properties
function setTextLogoProperties(mappingKey, value) {
    var mapping = textLogoMappings[mappingKey];

    if (!mapping) {
        return;
    }

    var iframe = $('#lps-iframe')[0];
    if (!iframe) {
        return;
    }

    var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    var $iframeContent = jQuery(iframeContent);

    // Handle empty value case for text logo
    if (mappingKey === 'lps_login_text_logo' && !value.trim()) {
        unbindTextLogo($iframeContent); // Call to unbind the text logo
        return;
    }

    // Inject CSS into the iframe document
    injectCSS($iframeContent);

    if (mapping.targetSelector) {
        var targetElement = $iframeContent.find(mapping.targetSelector);
        if (targetElement.length) {
            if (mapping.method === 'text') {
                targetElement.text(value).addClass('custom-text-logo');
            } else if (mapping.cssProperty) {
                $iframeContent.find(mapping.targetSelector).css(mapping.cssProperty, value + ' !important');
            }
        }
    }
}

// Function to set font properties
function setFontProperties(mappingKey, value) {
    var mapping = fontMappings[mappingKey];

    if (!mapping) {
        return;
    }

    var iframe = $('#lps-iframe')[0];
    if (!iframe) {
        return;
    }

    var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    var $iframeContent = jQuery(iframeContent);

    if (mapping.targetSelector) {
        applyGoogleFontToIframe(value, $iframeContent, mapping.targetSelector);
    }
}

// Function to apply Google Font styling inside the iframe
function applyGoogleFontToIframe(fontFamily, $iframeContent, targetSelector) {
    var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + fontFamily.replace(/\+/g, '%20');

    if (!$iframeContent.find('link[href*="' + googleFontUrl + '"]').length) {
        var existingLink = $iframeContent.find('link[href*="fonts.googleapis.com"]');
        if (existingLink.length) {
            var existingHref = existingLink.attr('href');
            if (!existingHref.includes(fontFamily)) {
                existingLink.attr('href', existingHref + '|' + fontFamily.replace(/\+/g, '%20'));
            }
        } else {
            $('<link rel="stylesheet" type="text/css" href="' + googleFontUrl + '">').appendTo($iframeContent.find('head'));
        }
    }

    $iframeContent.find(targetSelector).attr('style', function(i, style) {
        return (style || '') + 'font-family:' + fontFamily + ' !important;';
    });
}

// Function to inject CSS into the iframe document
function injectCSS($iframeContent) {
    var css = `
        /* Remove default WordPress logo background */
        .login h1 a {
            background: none !important; /* Remove any default background */
            text-indent: 0; /* Remove any text-indent if applied */
            overflow: hidden; /* Ensure overflow is hidden for custom text */
            display: block; /* Ensure it displays inline if needed */
        }

        /* Apply additional styles as needed */
        .login h1 a.custom-text-logo {
            font-size: 50px !important; /* Adjust as necessary */
            font-weight: bold !important; /* Adjust as necessary */
            text-align: center !important; /* Adjust as necessary */
        }
    `;

    var style = $('<style class="text-logo-styles">').text(css); // Use a specific class to identify logo styles
    $iframeContent.find('head').append(style);
}

// Function to remove injected CSS from the iframe document
function removeInjectedCSS($iframeContent, onlyLogoStyles = false) {
    if (onlyLogoStyles) {
        $iframeContent.find('style.text-logo-styles').remove(); // Remove only styles related to text logo
    } else {
        $iframeContent.find('style').remove(); // Remove all style elements if needed
    }
}

// Bind events for text logo inputs
function bindTextLogoInput() {
    $('#lps_login_text_logo').on('input', function () {
        var textLogoText = $(this).val();
        setTextLogoProperties('lps_login_text_logo', textLogoText);
    });

    $('#lps_font_size').on('input', function () {
        var fontSize = $(this).val();
        setTextLogoProperties('font_size', fontSize);
    });

    $('#lps_font_weight').on('input', function () {
        var fontWeight = $(this).val();
        setTextLogoProperties('font_weight', fontWeight);
    });

    $('#lps_color').on('input', function () {
        var color = $(this).val();
        setTextLogoProperties('color', color);
    });
}

// Bind events for font settings
function bindFontChangeEvents() {
    $('#lps_gfonttext_logo').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfonttextlogo', selectedFont);
    });

    $('#lps_gfont_copyright').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfontcopyright', selectedFont);
    });

    $('#lps_gfontlab').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfontlabel', selectedFont);
    });

    $('#lps_gfontlink').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfontlink', selectedFont);
    });

    $('#lps_gfontmsg').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfontmsg', selectedFont);
    });

    $('#lps_gfontbtn').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfontbtn', selectedFont);
    });

    $('#lps_gfont_inputtext').change(function () {
        var selectedFont = $(this).val();
        setFontProperties('gfontinput', selectedFont);
    });
}

// Initialize event bindings
$(document).ready(function () {
    bindFontChangeEvents();
    bindTextLogoInput();
});




// Function to initialize the animation selection for a specific input field
function initializeAnimationSelection(inputId) {
    var animationDropdown = $('#' + inputId);
    var targetSelector = '#login'; // Targeting the login form directly
    var animationDuration = '1.2s'; // You can add a range slider for this if needed

    // Event listener to handle animation change
    animationDropdown.on('change', function() {
        var selectedAnimation = $(this).val();
        applyAnimationToIframe(targetSelector, selectedAnimation, animationDuration);
    });

    // Initialize with the current selected animation
    var initialAnimation = animationDropdown.val();
    applyAnimationToIframe(targetSelector, initialAnimation, animationDuration);
}

// Apply the chosen animation without retaining its transform/opacity after playback.
// Retained animation transforms on #login can stop browsers compositing
// Nature Pro's backdrop-filter on the nested #loginform correctly.
function applyAnimationToIframe(targetSelector, animationName, duration) {
    var iframe = document.getElementById('lps-iframe');
    if (!iframe || !iframe.contentDocument) return;

    var doc = iframe.contentDocument;
    var $doc = $(doc);
    // Use the same local animation stylesheet as the frontend.
    // The source URL is supplied by PHP, never fetched from a third-party CDN.
    if (!$doc.find('link[data-lps-form-animations="1"]').length &&
        window.lpsLocalFormAnimationsUrl) {
        $('<link>', {
            rel: 'stylesheet',
            href: window.lpsLocalFormAnimationsUrl,
            'data-lps-form-animations': '1'
        }).appendTo($doc.find('head'));
    }

    var element = doc.querySelector(targetSelector);
    if (!element) return;

    // Cancel any pending animation from the previous selection.
    if (element.lpsAnimationTimer) {
        clearTimeout(element.lpsAnimationTimer);
        element.lpsAnimationTimer = null;
    }
    if (element.lpsAnimationEndHandler) {
        element.removeEventListener('animationend', element.lpsAnimationEndHandler);
        element.lpsAnimationEndHandler = null;
    }

    element.style.animationName = 'none';
    element.style.animationDuration = '0s';
    element.style.animationFillMode = 'none';

    if (!animationName || animationName === 'none') return;

    // Reflow allows reselecting the same animation to play it again.
    void element.offsetWidth;
    element.lpsAnimationEndHandler = function (event) {
        if (event.target !== element) return;
        element.style.animationName = 'none';
        element.style.animationDuration = '0s';
        element.style.animationFillMode = 'none';
        element.removeEventListener('animationend', element.lpsAnimationEndHandler);
        element.lpsAnimationEndHandler = null;
    };
    element.addEventListener('animationend', element.lpsAnimationEndHandler);
    element.style.animationName = animationName;
    element.style.animationDuration = duration;
    element.style.animationFillMode = 'none';
}

// Call the initialization function when the document and iframe are ready
$(document).ready(function() {
    // Ensure the iframe is fully loaded before applying animations
    $('#lps-iframe').on('load', function() {
        initializeAnimationSelection('lps_login_animation');
    });
});

// Position changes are handled by the unified layout controller.
// Do not assign percentage padding or cssText to #login here.

// Function to update the box shadow settings
function updateBoxShadowSettings() {
    var horizontal = $('#lps_box_shadow_horizontal').val();
    var vertical = $('#lps_box_shadow_vertical').val();
    var blur = $('#lps_box_shadow_blur').val();
    var spread = $('#lps_box_shadow_spread').val();
    
    // Get the box shadow color from the color picker (base color)
    var baseColor = $('#lps_box_shadow_color').wpColorPicker('color');
    
    // Get the opacity value from the opacity input
    var opacityInputId = getOpacityInputId('lps_box_shadow_color');
    var opacity = opacityInputId ? $('#' + opacityInputId).val() : 1;
    opacity = Math.min(Math.max(opacity, 0), 1); // Ensure opacity is between 0 and 1

    // Convert the base color to RGBA with the specified opacity
    var colorWithOpacity = rgba(baseColor, opacity);
    var boxShadowValue = `${horizontal}px ${vertical}px ${blur}px ${spread}px ${colorWithOpacity}`;

    applyBoxShadowToTarget(boxShadowValue);
}

// Helper function to apply box shadow to target elements
function applyBoxShadowToTarget(boxShadowValue) {
    var iframe = $('#lps-iframe')[0];
    var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
    var $iframeContent = jQuery(iframeContent);

    // Update only the specific target elements
    $iframeContent.find('.login form').each(function() {
        this.style.setProperty('box-shadow', boxShadowValue, 'important');
    });
}

// Helper function to convert color to RGBA
function rgba(color, opacity) {
    var r, g, b;
    if (color.startsWith('#')) {
        var hex = color.slice(1);
        r = parseInt(hex.substring(0, 2), 16);
        g = parseInt(hex.substring(2, 4), 16);
        b = parseInt(hex.substring(4, 6), 16);
    } else {
        throw new Error('Unsupported color format');
    }
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

// Initialize box shadow inputs and sliders
function initializeBoxShadowInputs() {
    // Initialize range sliders
    $('#lps_box_shadow_horizontal, #lps_box_shadow_vertical, #lps_box_shadow_blur, #lps_box_shadow_spread').on('input.boxshadow', function() {
        updateBoxShadowSettings();
    });

    // Initialize number inputs
    $('#amountInputBSH, #amountInputBSV, #amountInputBSBlur, #amountInputBSSpread').on('input.boxshadow', function() {
        var value = $(this).val();
        $('#' + this.id.replace('amountInput', 'lps_box_shadow_')).val(value);
        updateBoxShadowSettings();
    });

    // Initialize color picker
    $('#lps_box_shadow_color').wpColorPicker({
        change: function () {
            updateBoxShadowSettings();
        }
    });

    // Initialize opacity input if exists
    var opacityInputId = getOpacityInputId('lps_box_shadow_color');
    if (opacityInputId) {
        $('#' + opacityInputId).on('input', function () {
            updateBoxShadowSettings();
        });
    }

    updateBoxShadowSettings(); // Initial settings setup
}

// Use jQuery's document ready function
jQuery(document).ready(function ($) {
    initializeBoxShadowInputs();
});




});
