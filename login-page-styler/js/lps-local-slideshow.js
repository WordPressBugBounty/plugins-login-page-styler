/* Login Page Styler: small dependency-free local slideshow.
 * Shared by Pro login pages and the Free/Pro admin-only live preview.
 * Does not submit settings, change authentication, or contact third-party servers.
 */
(function (global) {
  'use strict';
  function mount(doc, options) {
    if (!doc || !doc.body) return;
    var body = doc.body;
    var previous = doc.__lpsSlideshowCleanup;
    if (typeof previous === 'function') previous();
    var images = (Array.isArray(options && options.images) ? options.images : []).filter(function (url) {
      return typeof url === 'string' && /^https?:\/\//i.test(url.trim());
    }).map(function (url) { return url.trim(); });
    if (!images.length) return;
    var effect = ['fade', 'slide', 'flip', 'cube'].indexOf(options.effect) !== -1 ? options.effect : 'fade';
    var seconds = Math.max(1, Math.min(10, Number(options.seconds) || 3));
    var css = doc.createElement('style');
    css.id = 'lps-slideshow-local-css';
    // Keep the current slide flat and fully visible. Only the incoming/outgoing
    // slide receives a transform. The previous effect selector accidentally had
    // the same specificity as .is-current and overrode its flat position.
    css.textContent = 'body.login.lps-slideshow-active{background-image:none!important;isolation:isolate}' +
      'body.login.lps-slideshow-active>.lps-slideshow-local{position:fixed!important;inset:0!important;z-index:-1!important;overflow:hidden!important;pointer-events:none!important;perspective:1200px}' +
      '.lps-slideshow-local .lps-slideshow-local-slide{position:absolute;inset:0;background-position:center;background-size:cover;background-repeat:no-repeat;opacity:0;transform:scale(1);transition:opacity 750ms ease,transform 850ms ease;will-change:opacity,transform;backface-visibility:hidden}' +
      '.lps-slideshow-local[data-effect="slide"] .lps-slideshow-local-slide:not(.is-current){transform:translateX(8%)}' +
      '.lps-slideshow-local[data-effect="flip"] .lps-slideshow-local-slide:not(.is-current){transform:rotateY(85deg) scale(1.02)}' +
      '.lps-slideshow-local[data-effect="cube"] .lps-slideshow-local-slide:not(.is-current){transform:rotateY(65deg) scale(.92)}' +
      '.lps-slideshow-local .lps-slideshow-local-slide.is-current{opacity:1;transform:translateX(0) rotateY(0deg) scale(1)}' +
      '@media(prefers-reduced-motion:reduce){.lps-slideshow-local .lps-slideshow-local-slide{transition:none!important;transform:none!important}}';
    doc.head.appendChild(css);
    var holder = doc.createElement('div');
    holder.className = 'lps-slideshow-local';
    holder.setAttribute('data-effect', effect);
    holder.setAttribute('aria-hidden', 'true');
    var slides = images.map(function (url, i) {
      var slide = doc.createElement('div');
      slide.className = 'lps-slideshow-local-slide' + (i === 0 ? ' is-current' : '');
      slide.style.backgroundImage = 'url(' + JSON.stringify(url) + ')';
      holder.appendChild(slide);
      return slide;
    });
    body.insertBefore(holder, body.firstChild);
    body.classList.add('lps-slideshow-active');
    var timer = null;
    var position = 0;
    // With one image, a slideshow cannot transition; display it steadily.
    if (slides.length > 1) {
      timer = global.setInterval(function () {
        slides[position].classList.remove('is-current');
        position = (position + 1) % slides.length;
        slides[position].classList.add('is-current');
      }, seconds * 1000);
    }
    doc.__lpsSlideshowCleanup = function () {
      if (timer) global.clearInterval(timer);
      holder.remove();css.remove();body.classList.remove('lps-slideshow-active');
      doc.__lpsSlideshowCleanup = null;
    };
  }
  global.LPSBackgroundSlideshow = {mount: mount};
  if (global.lpsSlideshowConfig && global.document) {
    var start = function () { mount(global.document, global.lpsSlideshowConfig); };
    if (global.document.readyState === 'loading') global.document.addEventListener('DOMContentLoaded', start);
    else start();
  }
})(window);
