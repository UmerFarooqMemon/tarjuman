/**
 * Admin appearance settings (dock mode + card style).
 */
(function () {
  'use strict';

  var CARD_STYLE_KEY = 'admin-card-style';
  var DEFAULT_CARD_STYLE = 'classic';
  var modalEl = document.getElementById('adminAppearanceModal');

  if (!modalEl) {
    return;
  }

  function normalizeCardStyle(style) {
    return style === 'glass' ? 'glass' : 'classic';
  }

  function getCardStyle() {
    try {
      return normalizeCardStyle(localStorage.getItem(CARD_STYLE_KEY));
    } catch (e) {
      return DEFAULT_CARD_STYLE;
    }
  }

  function setCardStyle(style) {
    style = normalizeCardStyle(style);
    try {
      localStorage.setItem(CARD_STYLE_KEY, style);
    } catch (e) {
      // ignore
    }
    document.documentElement.setAttribute('data-admin-card-style', style);
    return style;
  }

  function getNavLayoutApi() {
    return window.AdminNavLayout || null;
  }

  function syncFormFromStorage() {
    var dockToggle = modalEl.querySelector('[data-appearance-dock-mode]');
    var navApi = getNavLayoutApi();
    var currentNav = navApi ? navApi.get() : 'dock';

    if (dockToggle) {
      dockToggle.checked = currentNav === 'dock';
    }

    var currentCardStyle = getCardStyle();
    modalEl.querySelectorAll('[data-appearance-card-style]').forEach(function (input) {
      input.checked = input.value === currentCardStyle;
    });
  }

  function closeDockUi() {
    document.querySelectorAll('[data-dock-panel]').forEach(function (panel) {
      panel.hidden = true;
    });
    document.querySelectorAll('[data-dock-toggle][aria-expanded="true"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });

    var drawer = document.getElementById('adminDockDrawer');
    if (drawer && !drawer.hidden) {
      drawer.hidden = true;
      drawer.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('dock-drawer-open');
    }
  }

  function closeSidebarMenu() {
    if (!document.documentElement.classList.contains('layout-menu-expanded')) {
      return;
    }

    var overlay = document.querySelector('.layout-overlay');
    if (overlay) {
      overlay.click();
      return;
    }

    document.documentElement.classList.remove('layout-menu-expanded');
  }

  function openModal() {
    syncFormFromStorage();
    closeDockUi();
    closeSidebarMenu();

    if (window.bootstrap && window.bootstrap.Modal) {
      var instance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modalEl.addEventListener(
        'shown.bs.modal',
        function markAppearanceBackdrop() {
          var backdrops = document.querySelectorAll('.modal-backdrop.show');
          var backdrop = backdrops[backdrops.length - 1];
          if (backdrop) {
            backdrop.classList.add('admin-appearance-backdrop');
          }
        },
        { once: true }
      );
      instance.show();
    }
  }

  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-open-admin-appearance]');
    if (!trigger) {
      return;
    }

    event.preventDefault();
    openModal();
  });

  modalEl.addEventListener('show.bs.modal', syncFormFromStorage);

  var applyBtn = modalEl.querySelector('[data-appearance-apply]');
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      var navApi = getNavLayoutApi();
      var dockToggle = modalEl.querySelector('[data-appearance-dock-mode]');
      var selectedCardStyleInput = modalEl.querySelector('[data-appearance-card-style]:checked');
      var nextNav = dockToggle && dockToggle.checked ? 'dock' : 'sidebar';
      var nextCardStyle = selectedCardStyleInput ? selectedCardStyleInput.value : DEFAULT_CARD_STYLE;
      var currentNav = navApi ? navApi.get() : nextNav;
      var navChanged = currentNav !== nextNav;

      if (navApi) {
        navApi.set(nextNav);
      }

      setCardStyle(nextCardStyle);

      if (navChanged) {
        window.location.reload();
        return;
      }

      if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      }
    });
  }

  window.AdminAppearance = {
    getCardStyle: getCardStyle,
    setCardStyle: setCardStyle,
    key: CARD_STYLE_KEY,
  };
})();
