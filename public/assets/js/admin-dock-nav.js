/**
 * Admin Dock Nav
 * Vanilla JS (Vuexy/Bootstrap compatible) — dropdowns, overflow "More",
 * mobile drawer with swipe-to-dismiss, a11y, body scroll lock.
 */
(function () {
  'use strict';

  var root = document.getElementById('adminDock');
  if (!root) {
    return;
  }

  var drawer = document.getElementById('adminDockDrawer');
  var sheet = drawer ? drawer.querySelector('[data-dock-sheet]') : null;
  var scroller = root.querySelector('[data-dock-scroller]');
  var moreWrap = root.querySelector('[data-dock-more]');
  var moreList = root.querySelector('[data-dock-more-list]');
  var openPanelId = null;
  var swipeState = null;
  var tipEl = null;
  var tipHideTimer = null;
  var tipTarget = null;

  function qsAll(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function clearPanelPosition(panel) {
    panel.style.left = '';
    panel.style.top = '';
    panel.style.right = '';
    panel.style.bottom = '';
    panel.style.visibility = '';
  }

  function restorePanelHome(panel) {
    var home = panel._dockHome;
    if (home && home.parent && panel.parentNode !== home.parent) {
      if (home.nextSibling && home.nextSibling.parentNode === home.parent) {
        home.parent.insertBefore(panel, home.nextSibling);
      } else {
        home.parent.appendChild(panel);
      }
    }
    panel._dockHome = null;
  }

  function movePanelToBody(panel) {
    if (panel.parentNode === document.body) {
      return;
    }
    panel._dockHome = {
      parent: panel.parentNode,
      nextSibling: panel.nextSibling,
    };
    document.body.appendChild(panel);
  }

  function closeAllPanels(exceptId) {
    qsAll('[data-dock-panel]', root)
      .concat(qsAll('[data-dock-panel]', drawer || document))
      .concat(qsAll('[data-dock-panel]', document.body))
      .forEach(function (panel) {
        var id = panel.getAttribute('data-dock-panel');
        if (exceptId && id === exceptId) {
          return;
        }
        panel.hidden = true;
        clearPanelPosition(panel);
        restorePanelHome(panel);
        qsAll('[data-dock-toggle="' + id + '"]').forEach(function (btn) {
          btn.setAttribute('aria-expanded', 'false');
        });
      });
    if (!exceptId || openPanelId !== exceptId) {
      openPanelId = null;
    }
  }

  /**
   * Position dock menus with position:fixed on document.body so they escape
   * the dock glass (backdrop-filter creates a containing block that would
   * otherwise clip / mis-position menus). Profile menus align to the trigger's
   * right edge; others center above the trigger.
   */
  function positionPanel(toggle, panel) {
    var rect = toggle.getBoundingClientRect();
    var gap = 10;
    var margin = 8;
    var alignEnd =
      panel.classList.contains('admin-dock__dropdown--profile') ||
      panel.classList.contains('admin-dock__dropdown--mobile-profile') ||
      panel.classList.contains('admin-dock__dropdown--theme') ||
      panel.getAttribute('data-dock-panel') === 'profile' ||
      panel.getAttribute('data-dock-panel') === 'profile-mobile' ||
      panel.getAttribute('data-dock-panel') === 'theme' ||
      panel.getAttribute('data-dock-panel') === 'theme-mobile';

    movePanelToBody(panel);
    panel.hidden = false;
    panel.style.visibility = 'hidden';
    panel.style.left = '0px';
    panel.style.top = '0px';

    var panelRect = panel.getBoundingClientRect();
    var left;

    if (alignEnd) {
      left = rect.right - panelRect.width;
    } else {
      left = rect.left + rect.width / 2 - panelRect.width / 2;
    }

    left = Math.max(margin, Math.min(left, window.innerWidth - panelRect.width - margin));

    var top = rect.top - panelRect.height - gap;
    if (top < margin) {
      top = rect.bottom + gap;
    }

    panel.style.left = left + 'px';
    panel.style.top = top + 'px';
    panel.style.visibility = '';
  }

  function openPanel(id, toggle) {
    var panel = document.querySelector('[data-dock-panel="' + id + '"]');
    if (!panel || !toggle) {
      return;
    }

    hideDockTip();
    closeAllPanels(id);
    positionPanel(toggle, panel);
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    openPanelId = id;

    // Reposition after paint with real dimensions
    requestAnimationFrame(function () {
      positionPanel(toggle, panel);
      panel.hidden = false;
    });
  }

  function togglePanel(id, toggle) {
    var panel = document.querySelector('[data-dock-panel="' + id + '"]');
    if (!panel) {
      return;
    }
    if (!panel.hidden && openPanelId === id) {
      closeAllPanels();
      return;
    }
    openPanel(id, toggle);
  }

  function setDrawerOpen(open) {
    if (!drawer) {
      return;
    }

    var openBtn = root.querySelector('[data-dock-open-drawer]');
    if (open) {
      drawer.hidden = false;
      drawer.setAttribute('aria-hidden', 'false');
      document.body.classList.add('dock-drawer-open');
      if (openBtn) {
        openBtn.setAttribute('aria-expanded', 'true');
      }
      closeAllPanels();
      closeMobileFolders();
    } else {
      drawer.hidden = true;
      drawer.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('dock-drawer-open');
      if (openBtn) {
        openBtn.setAttribute('aria-expanded', 'false');
      }
      if (sheet) {
        sheet.style.transform = '';
      }
      closeMobileFolders();
    }
  }

  function makeMorePlainItem(label, href, isActive) {
    var li = document.createElement('li');
    var a = document.createElement('a');
    a.className = 'admin-dock__dropdown-item' + (isActive ? ' is-active' : '');
    a.setAttribute('role', 'menuitem');
    a.href = href || '#';
    a.textContent = label;
    li.appendChild(a);
    return li;
  }

  function makeMorePlainAction(label, layout, isActive) {
    var li = document.createElement('li');
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'admin-dock__dropdown-item' + (isActive ? ' is-active' : '');
    btn.setAttribute('role', 'menuitem');
    btn.setAttribute('data-set-admin-nav-layout', layout);
    btn.textContent = label;
    li.appendChild(btn);
    return li;
  }

  function appendMorePlainEntries(item, list) {
    var childItems = qsAll('[data-dock-panel] .admin-dock__dropdown-item', item);
    if (childItems.length) {
      childItems.forEach(function (child) {
        var label = (child.textContent || '').replace(/\s+/g, ' ').trim();
        var layout = child.getAttribute('data-set-admin-nav-layout');
        if (layout) {
          list.appendChild(makeMorePlainAction(label, layout, child.classList.contains('is-active')));
          return;
        }
        var href = child.getAttribute('href');
        if (href) {
          list.appendChild(
            makeMorePlainItem(label, href, child.classList.contains('is-active'))
          );
        }
      });
      return;
    }

    var link = item.querySelector('a.admin-dock__btn[href]');
    var labelEl = item.querySelector('.admin-dock__label');
    var label =
      item.getAttribute('data-dock-label') ||
      (labelEl ? labelEl.textContent : '') ||
      (link ? link.getAttribute('aria-label') : '') ||
      '';
    label = String(label).replace(/\s+/g, ' ').trim();
    if (!label) {
      return;
    }
    list.appendChild(
      makeMorePlainItem(
        label,
        link ? link.getAttribute('href') : '#',
        item.classList.contains('is-active')
      )
    );
  }

  function collapseOverflow() {
    if (!scroller || !moreWrap || !moreList || window.innerWidth < 768) {
      return;
    }

    var primary = root.querySelector('[data-dock-primary]');
    if (!primary) {
      return;
    }

    // Restore overflowed dock icons, clear plain More list
    qsAll('[data-dock-item].is-dock-overflowed', primary).forEach(function (item) {
      item.classList.remove('is-dock-overflowed');
    });
    moreList.innerHTML = '';
    moreWrap.classList.add('d-none');

    var items = qsAll('[data-dock-item]', primary).sort(function (a, b) {
      return Number(b.getAttribute('data-priority') || 99) - Number(a.getAttribute('data-priority') || 99);
    });

    // Overflow lowest-priority items into a plain-text More menu
    var guard = 0;
    while (scroller.scrollWidth > scroller.clientWidth + 2 && items.length > 1 && guard < 20) {
      var victim = items.shift();
      if (!victim) {
        break;
      }
      victim.classList.add('is-dock-overflowed');
      appendMorePlainEntries(victim, moreList);
      moreWrap.classList.remove('d-none');
      guard += 1;
    }
  }

  // Toggle buttons (desktop + mobile profile)
  root.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-dock-toggle]');
    if (toggle && root.contains(toggle)) {
      event.preventDefault();
      event.stopPropagation();
      togglePanel(toggle.getAttribute('data-dock-toggle'), toggle);
      return;
    }

    var openDrawer = event.target.closest('[data-dock-open-drawer]');
    if (openDrawer) {
      event.preventDefault();
      setDrawerOpen(true);
    }
  });

  if (drawer) {
    drawer.addEventListener('click', function (event) {
      if (event.target.closest('[data-dock-close-drawer]')) {
        closeMobileFolders();
        setDrawerOpen(false);
        return;
      }

      var folderBack = event.target.closest('[data-dock-mobile-folder-back]');
      if (folderBack) {
        event.preventDefault();
        closeMobileFolders();
        return;
      }

      var folderBtn = event.target.closest('[data-dock-mobile-folder]');
      if (folderBtn) {
        event.preventDefault();
        var item = folderBtn.closest('.admin-dock-drawer__item');
        var panel = item ? item.querySelector('[data-dock-folder-panel]') : null;
        if (!panel) {
          return;
        }
        var opening = panel.hidden;
        closeMobileFolders();
        if (opening) {
          panel.hidden = false;
          folderBtn.setAttribute('aria-expanded', 'true');
          drawer.classList.add('is-folder-open');
        }
        return;
      }

      // Close drawer after following a real navigation link
      var link = event.target.closest('a[href]');
      if (link && link.hasAttribute('data-dock-close-drawer')) {
        closeMobileFolders();
        setDrawerOpen(false);
      }
    });
  }

  function closeMobileFolders() {
    if (!drawer) {
      return;
    }
    qsAll('[data-dock-folder-panel]', drawer).forEach(function (panel) {
      panel.hidden = true;
    });
    qsAll('[data-dock-mobile-folder]', drawer).forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
    drawer.classList.remove('is-folder-open');
  }

  // Click outside closes panels (panels may be portaled to document.body)
  document.addEventListener('click', function (event) {
    if (
      !event.target.closest('#adminDock') &&
      !event.target.closest('[data-dock-panel]')
    ) {
      closeAllPanels();
    }
  });

  // Escape closes panel or drawer
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }
    if (drawer && !drawer.hidden) {
      if (drawer.classList.contains('is-folder-open')) {
        closeMobileFolders();
        return;
      }
      setDrawerOpen(false);
      return;
    }
    closeAllPanels();
  });

  // Keyboard: Enter/Space already activate buttons; Arrow support for open menus
  root.addEventListener('keydown', function (event) {
    if (!openPanelId) {
      return;
    }
    if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
      return;
    }
    var panel = document.querySelector('[data-dock-panel="' + openPanelId + '"]');
    if (!panel || panel.hidden) {
      return;
    }
    var items = qsAll('[role="menuitem"]', panel);
    if (!items.length) {
      return;
    }
    event.preventDefault();
    var index = items.indexOf(document.activeElement);
    if (event.key === 'ArrowDown') {
      index = index < items.length - 1 ? index + 1 : 0;
    } else {
      index = index > 0 ? index - 1 : items.length - 1;
    }
    items[index].focus();
  });

  // Swipe-down to dismiss bottom sheet
  if (sheet) {
    sheet.addEventListener('touchstart', function (event) {
      if (!event.touches || !event.touches.length) {
        return;
      }
      swipeState = {
        startY: event.touches[0].clientY,
        currentY: event.touches[0].clientY,
      };
      drawer.classList.add('is-dragging');
    }, { passive: true });

    sheet.addEventListener('touchmove', function (event) {
      if (!swipeState || !event.touches || !event.touches.length) {
        return;
      }
      swipeState.currentY = event.touches[0].clientY;
      var delta = Math.max(0, swipeState.currentY - swipeState.startY);
      sheet.style.transform = 'translateY(' + delta + 'px)';
    }, { passive: true });

    sheet.addEventListener('touchend', function () {
      if (!swipeState) {
        return;
      }
      var delta = Math.max(0, swipeState.currentY - swipeState.startY);
      drawer.classList.remove('is-dragging');
      swipeState = null;
      if (delta > 90) {
        setDrawerOpen(false);
      } else {
        sheet.style.transform = '';
      }
    });
  }

  var resizeTimer = null;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      closeAllPanels();
      collapseOverflow();
    }, 120);
  });

  // Theme switcher (desktop + mobile)
  function themeStorageKey() {
    var name = window.templateName || document.documentElement.getAttribute('data-template') || 'vertical-menu-template';
    return 'templateCustomizer-' + name + '--Style';
  }

  function currentThemeStyle() {
    return localStorage.getItem(themeStorageKey()) || 'light';
  }

  function syncThemeIcons(style) {
    var iconClass =
      style === 'dark' ? 'ti ti-moon' : style === 'system' ? 'ti ti-device-desktop' : 'ti ti-sun';
    qsAll('[data-dock-theme-icon]', root).forEach(function (icon) {
      icon.className = iconClass;
    });
  }

  function applyTheme(style) {
    if (!style) {
      return;
    }
    if (window.templateCustomizer && typeof window.templateCustomizer.setStyle === 'function') {
      window.templateCustomizer.setStyle(style);
    }
    syncThemeIcons(style);
    closeAllPanels();
  }

  document.addEventListener('click', function (event) {
    var themeBtn = event.target.closest('[data-dock-theme]');
    if (!themeBtn) {
      return;
    }
    event.preventDefault();
    applyTheme(themeBtn.getAttribute('data-dock-theme'));
  });

  syncThemeIcons(currentThemeStyle());

  // macOS-style hover tooltip (portaled to body so glass blur can't clip it)
  tipEl = document.createElement('div');
  tipEl.className = 'admin-dock-tip';
  tipEl.setAttribute('role', 'tooltip');
  tipEl.hidden = true;
  document.body.appendChild(tipEl);

  function hideDockTip() {
    if (!tipEl) {
      return;
    }
    tipTarget = null;
    tipEl.classList.remove('is-visible');
    tipHideTimer = setTimeout(function () {
      tipEl.hidden = true;
      tipEl.textContent = '';
    }, 120);
  }

  function showDockTip(target) {
    var text = target.getAttribute('data-dock-tip');
    if (!text || !tipEl || window.innerWidth < 768) {
      return;
    }

    clearTimeout(tipHideTimer);
    tipTarget = target;
    tipEl.textContent = text;
    tipEl.hidden = false;

    var rect = target.getBoundingClientRect();
    tipEl.style.left = '0px';
    tipEl.style.top = '0px';

    var tipRect = tipEl.getBoundingClientRect();
    var left = rect.left + rect.width / 2 - tipRect.width / 2;
    left = Math.max(8, Math.min(left, window.innerWidth - tipRect.width - 8));
    var top = rect.top - tipRect.height - 10;
    if (top < 8) {
      top = rect.bottom + 10;
    }

    tipEl.style.left = left + 'px';
    tipEl.style.top = top + 'px';

    requestAnimationFrame(function () {
      if (tipTarget === target) {
        tipEl.classList.add('is-visible');
      }
    });
  }

  root.addEventListener('mouseover', function (event) {
    var target = event.target.closest('[data-dock-tip]');
    if (!target || !root.contains(target) || tipTarget === target) {
      return;
    }
    showDockTip(target);
  });

  root.addEventListener('mouseout', function (event) {
    var target = event.target.closest('[data-dock-tip]');
    if (!target || !tipTarget) {
      return;
    }
    var related = event.relatedTarget;
    if (related && target.contains(related)) {
      return;
    }
    if (related && related.closest && related.closest('[data-dock-tip]') === tipTarget) {
      return;
    }
    hideDockTip();
  });

  root.addEventListener('focusin', function (event) {
    var target = event.target.closest('[data-dock-tip]');
    if (target && root.contains(target)) {
      showDockTip(target);
    }
  });

  root.addEventListener('focusout', function (event) {
    var related = event.relatedTarget;
    if (!related || !related.closest || !related.closest('[data-dock-tip]')) {
      hideDockTip();
    }
  });

  window.addEventListener('scroll', hideDockTip, true);

  // Init
  collapseOverflow();
})();
