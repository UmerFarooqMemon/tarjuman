/**
 * Cycle EN/AR phrase pairs on the auth translation cover.
 */
'use strict';

(function () {
  var root = document.querySelector('[data-auth-exchange]');
  if (!root) {
    return;
  }

  var enEl = root.querySelector('[data-auth-en]');
  var arEl = root.querySelector('[data-auth-ar]');
  if (!enEl || !arEl) {
    return;
  }

  var pairs = [
    { en: 'Welcome', ar: 'مرحباً' },
    { en: 'Official documents', ar: 'وثائق رسمية' },
    { en: 'Certified translation', ar: 'ترجمة معتمدة' },
    { en: 'Fast estimates', ar: 'تقديرات سريعة' },
    { en: 'Trusted partners', ar: 'شركاء موثوقون' }
  ];

  var index = 0;

  function swapText(el, next) {
    el.classList.remove('is-enter');
    el.classList.add('is-leave');
    window.setTimeout(function () {
      el.textContent = next;
      el.classList.remove('is-leave');
      el.classList.add('is-enter');
      window.requestAnimationFrame(function () {
        window.setTimeout(function () {
          el.classList.remove('is-enter');
        }, 30);
      });
    }, 280);
  }

  window.setInterval(function () {
    index = (index + 1) % pairs.length;
    swapText(enEl, pairs[index].en);
    swapText(arEl, pairs[index].ar);
  }, 3200);
})();
