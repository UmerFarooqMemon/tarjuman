/**
 * Admin navigation layout switcher (Sidebar vs Dock).
 * Preference is stored in localStorage and applied on <html> before paint
 * via the inline head bootstrap in the admin layout.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'admin-nav-layout';
  var DEFAULT_MODE = 'dock';

  function normalize(mode) {
    return mode === 'sidebar' || mode === 'dock' ? mode : DEFAULT_MODE;
  }

  function getMode() {
    try {
      return normalize(localStorage.getItem(STORAGE_KEY));
    } catch (e) {
      return DEFAULT_MODE;
    }
  }

  function setMode(mode) {
    mode = normalize(mode);
    try {
      localStorage.setItem(STORAGE_KEY, mode);
    } catch (e) {
      // ignore quota / private mode
    }
    document.documentElement.setAttribute('data-admin-nav-layout', mode);
    return mode;
  }

  function markActiveItems(mode) {
    document.querySelectorAll('[data-set-admin-nav-layout]').forEach(function (el) {
      var itemMode = el.getAttribute('data-set-admin-nav-layout');
      var isCurrent = itemMode === mode;
      el.classList.toggle('is-active', isCurrent);
      el.classList.toggle('active', isCurrent);
      var menuItem = el.closest('.menu-item');
      if (menuItem) {
        menuItem.classList.toggle('active', isCurrent);
      }
    });
  }

  // Apply (in case head bootstrap was skipped) and sync active states
  var current = setMode(getMode());
  markActiveItems(current);

  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-set-admin-nav-layout]');
    if (!trigger) {
      return;
    }
    var mode = trigger.getAttribute('data-set-admin-nav-layout');
    if (mode !== 'sidebar' && mode !== 'dock') {
      return;
    }
    event.preventDefault();
    if (mode === getMode()) {
      return;
    }
    setMode(mode);
    // Full reload keeps Vuexy sidebar/menu measurements and dock CSS isolated cleanly
    window.location.reload();
  });

  window.AdminNavLayout = {
    get: getMode,
    set: setMode,
    key: STORAGE_KEY,
  };
})();
