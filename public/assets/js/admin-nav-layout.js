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

  // Apply (in case head bootstrap was skipped)
  setMode(getMode());

  window.AdminNavLayout = {
    get: getMode,
    set: setMode,
    key: STORAGE_KEY,
  };
})();
