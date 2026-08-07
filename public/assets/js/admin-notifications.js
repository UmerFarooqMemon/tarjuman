(function (window, document) {
  'use strict';

  var audioCtx = null;
  var audioUnlocked = false;

  function getAudioContext() {
    var Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    if (!audioCtx) audioCtx = new Ctx();
    return audioCtx;
  }

  function unlockAudio() {
    var ctx = getAudioContext();
    if (!ctx) return;

    if (ctx.state === 'suspended') {
      ctx.resume().then(function () {
        audioUnlocked = true;
      }).catch(function () {});
    } else {
      audioUnlocked = true;
    }
  }

  function playBellTone(ctx, frequency, startAt, duration, gainValue, type) {
    var osc = ctx.createOscillator();
    var gain = ctx.createGain();

    osc.type = type || 'sine';
    osc.frequency.setValueAtTime(frequency, startAt);

    gain.gain.setValueAtTime(0.0001, startAt);
    gain.gain.exponentialRampToValueAtTime(gainValue, startAt + 0.015);
    gain.gain.exponentialRampToValueAtTime(gainValue * 0.55, startAt + duration * 0.35);
    gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);

    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(startAt);
    osc.stop(startAt + duration + 0.05);
  }

  var lastBellAt = 0;
  // Shared across dock desktop/mobile widgets so badge sync can't suppress the chime.
  var lastKnownUnreadForSound = null;

  function playNotificationBell() {
    var now = Date.now();
    if (now - lastBellAt < 2500) return;
    lastBellAt = now;

    unlockAudio();

    var ctx = getAudioContext();
    if (!ctx) return;

    var start = function () {
      var t = ctx.currentTime;
      // Medium ring: two clear strikes with harmonics (~1.6s)
      playBellTone(ctx, 784, t, 0.95, 0.28);
      playBellTone(ctx, 1568, t, 0.75, 0.14);
      playBellTone(ctx, 2349, t, 0.55, 0.07, 'triangle');
      playBellTone(ctx, 988, t + 0.38, 1.15, 0.26);
      playBellTone(ctx, 1976, t + 0.38, 0.9, 0.12);
      playBellTone(ctx, 2960, t + 0.38, 0.65, 0.06, 'triangle');
    };

    if (ctx.state === 'suspended') {
      ctx.resume().then(start).catch(function () {});
    } else {
      start();
    }
  }

  function announceUnreadCount(unreadCount, allowSound) {
    if (
      allowSound &&
      lastKnownUnreadForSound !== null &&
      unreadCount > lastKnownUnreadForSound
    ) {
      playNotificationBell();
      if (typeof toastr !== 'undefined') {
        toastr.info(window.__notificationsNewLabel || 'New notification');
      }
    }
    lastKnownUnreadForSound = unreadCount;
  }

  document.addEventListener('pointerdown', unlockAudio, { passive: true });
  document.addEventListener('keydown', unlockAudio);

  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function fillTemplate(template, id) {
    return String(template || '').replace('__ID__', id);
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function i18n(key, fallback) {
    var bag = window.__notificationsI18n || {};
    return bag[key] || fallback;
  }

  function withStatus(url, status, limit) {
    var base = String(url || '');
    if (!base) return base;
    var sep = base.indexOf('?') === -1 ? '?' : '&';
    return (
      base +
      sep +
      'status=' +
      encodeURIComponent(status || 'unread') +
      '&limit=' +
      encodeURIComponent(String(limit || 50))
    );
  }

  function slideOutAndRemove(el, done) {
    if (!el) {
      if (done) done();
      return;
    }

    var height = el.offsetHeight;
    el.style.maxHeight = height + 'px';
    el.style.overflow = 'hidden';
    // Force layout so the transition starts from the measured height.
    void el.offsetHeight;
    el.classList.add('is-dismissing');

    var finished = false;
    var finish = function () {
      if (finished) return;
      finished = true;
      if (el.parentNode) el.parentNode.removeChild(el);
      scheduleDockPanelReposition();
      if (done) done();
    };

    el.addEventListener('transitionend', finish);
    window.setTimeout(finish, 340);
  }

  function scheduleDockPanelReposition() {
    var run = function () {
      if (window.AdminDock && typeof window.AdminDock.repositionOpenPanel === 'function') {
        window.AdminDock.repositionOpenPanel();
      }
    };
    // Re-anchor above the dock after list height collapses (mark-all / mark-read).
    requestAnimationFrame(function () {
      run();
      requestAnimationFrame(run);
    });
    window.setTimeout(run, 360);
  }

  function resolveVendorDetailsUrl(notification, viewAction) {
    if (notification && notification.details_url) {
      return notification.details_url;
    }

    if (viewAction && viewAction.url) {
      return viewAction.url;
    }

    var url = String((notification && notification.url) || '');
    if (!url || url === 'javascript:void(0)') {
      return null;
    }

    // /vendor/orders/{id} → /vendor/orders/{id}/details (skip discover/open board / already details)
    if (
      /\/orders\/(?:open|discover)(?:\/|\?|$)/.test(url) ||
      /\/details(?:\/|\?|$)/.test(url)
    ) {
      return null;
    }

    var match = url.match(/^(.*\/orders\/[^/?#]+)\/?(?:[?#].*)?$/);
    if (match) {
      return match[1] + '/details';
    }

    return null;
  }

  function openVendorOrderModal(viewUrl) {
    if (!viewUrl) return false;
    if (window.VendorOrderModal && typeof window.VendorOrderModal.open === 'function') {
      window.VendorOrderModal.open(viewUrl);
      return true;
    }
    return false;
  }

  function createItem(notification, options) {
    options = options || {};
    var isVendor = options.guard === 'vendor';
    var isModal = options.variant === 'modal';
    var allowMarkRead = options.allowMarkRead !== false && !notification.read_at;

    var li = document.createElement('li');
    li.className =
      'list-group-item list-group-item-action dropdown-notifications-item' +
      (isModal ? ' notifications-modal-item' : '');
    if (notification.read_at) {
      li.classList.add('marked-as-read');
    }
    li.setAttribute('data-notification-id', notification.id);

    var url = notification.url || 'javascript:void(0)';
    var icon = notification.icon || 'ti ti-bell';
    var actions = Array.isArray(notification.actions) ? notification.actions : [];
    var acceptAction = null;
    var viewAction = null;

    actions.forEach(function (action) {
      if (!action || !action.url) return;
      if (action.type === 'accept') {
        acceptAction = action;
      } else if (action.type === 'view') {
        viewAction = action;
      }
    });

    var vendorDetailsUrl = resolveVendorDetailsUrl(notification, viewAction);
    if (vendorDetailsUrl) {
      li.setAttribute('data-view-url', vendorDetailsUrl);
      // Modal owns this interaction — never navigate to open-orders list.
      url = 'javascript:void(0)';
    }

    var footerHtml = '';
    if (isVendor) {
      var actionButtons = '';
      var orderState = notification.order_state || null;

      if (orderState === 'mine') {
        actionButtons =
          '<span class="badge bg-label-success d-inline-flex align-items-center gap-1">' +
          '<i class="ti ti-check"></i>' +
          escapeHtml(notification.order_state_label || 'Accepted') +
          '</span>';
      } else if (orderState === 'taken') {
        actionButtons =
          '<span class="badge bg-label-secondary d-inline-flex align-items-center gap-1">' +
          '<i class="ti ti-user-x"></i>' +
          escapeHtml(notification.order_state_label || 'Taken') +
          '</span>';
      } else {
        // View/Accept only while the order is still open.
        if (viewAction) {
          actionButtons +=
            '<button type="button" class="btn btn-sm btn-outline-primary" data-notification-view data-view-url="' +
            escapeHtml(viewAction.url || vendorDetailsUrl) +
            '">' +
            escapeHtml(viewAction.label || 'View') +
            '</button>';
        }
        if (acceptAction) {
          actionButtons +=
            '<button type="button" class="btn btn-sm btn-primary" data-notification-accept data-accept-url="' +
            escapeHtml(acceptAction.url) +
            '">' +
            escapeHtml(acceptAction.label || 'Accept') +
            '</button>';
        }
      }

      footerHtml =
        '<div class="d-flex align-items-center justify-content-between gap-2 mt-2 position-relative" style="z-index:2">' +
        '<div class="d-flex flex-wrap gap-2">' +
        (actionButtons || '<span></span>') +
        '</div>' +
        '<small class="text-muted ms-auto">' +
        escapeHtml(notification.created_at_human || '') +
        '</small>' +
        '</div>';
    } else {
      footerHtml =
        '<small class="text-muted d-block mt-2">' +
        escapeHtml(notification.created_at_human || '') +
        '</small>';
    }

    var sideActionsHtml = allowMarkRead
      ? '<div class="flex-shrink-0 dropdown-notifications-actions">' +
        '<a href="javascript:void(0)" class="dropdown-notifications-read text-body position-relative" style="z-index:2" data-notification-read title="' +
        escapeHtml(i18n('markAsRead', 'Mark as read')) +
        '">' +
        '<i class="ti ti-mail-opened ti-sm"></i></a>' +
        '</div>'
      : '';

    var titleInner =
      escapeHtml(notification.title || '') +
      (notification.order_id
        ? ' <span class="text-primary">(' + escapeHtml(notification.order_id) + ')</span>'
        : '');

    // Modal: no stretched-link overlay (it stacks on body/actions and makes text hard to read).
    var titleHtml = isModal
      ? '<h6 class="mb-1 notifications-modal-item__title">' +
        (url && url !== 'javascript:void(0)'
          ? '<a href="' + escapeHtml(url) + '" class="text-body" data-notification-link>' + titleInner + '</a>'
          : '<span data-notification-link>' + titleInner + '</span>') +
        '</h6>'
      : '<a href="' +
        escapeHtml(url) +
        '" class="stretched-link text-body" data-notification-link>' +
        '<h6 class="mb-1">' +
        titleInner +
        '</h6></a>';

    li.innerHTML =
      '<div class="d-flex align-items-start">' +
      '<div class="flex-shrink-0 me-3">' +
      '<div class="avatar"><span class="avatar-initial rounded-circle bg-label-primary"><i class="' +
      icon +
      '"></i></span></div>' +
      '</div>' +
      '<div class="flex-grow-1 notifications-item-body">' +
      titleHtml +
      '<p class="mb-0 notifications-item-text">' +
      escapeHtml(notification.body || '') +
      '</p>' +
      footerHtml +
      '</div>' +
      sideActionsHtml +
      '</div>';

    return li;
  }

  function NotificationsWidget(root) {
    this.root = root;
    this.indexUrl = root.getAttribute('data-notifications-index-url');
    this.guard =
      root.getAttribute('data-notifications-guard') ||
      (this.indexUrl && this.indexUrl.indexOf('/vendor/') !== -1 ? 'vendor' : 'admin');
    this.markAllUrl = root.getAttribute('data-notifications-mark-all-url');
    this.markReadTemplate = root.getAttribute('data-notifications-mark-read-url-template');
    this.destroyTemplate = root.getAttribute('data-notifications-destroy-url-template');
    this.broadcastAuthUrl = root.getAttribute('data-broadcast-auth-url');
    this.broadcastChannel = root.getAttribute('data-broadcast-channel');
    this.resolveDom();
    root._notificationsWidget = this;
    this.refresh();
    this.startPolling();
    this.startEcho();
  }

  NotificationsWidget.prototype.resolveDom = function () {
    var scope = this.getPanelScope() || this.root;
    this.list = scope.querySelector('[data-notifications-list]') || this.root.querySelector('[data-notifications-list]');
    this.empty = scope.querySelector('[data-notifications-empty]') || this.root.querySelector('[data-notifications-empty]');
    this.badge = this.root.querySelector('[data-notifications-badge]');
  };

  NotificationsWidget.prototype.getPanelScope = function () {
    var toggle = this.root.querySelector('[data-dock-toggle]');
    if (!toggle) return null;
    var panelId = toggle.getAttribute('data-dock-toggle');
    if (!panelId) return null;
    return document.querySelector('[data-dock-panel="' + panelId + '"]');
  };

  NotificationsWidget.prototype.applyUnreadCount = function (unreadCount) {
    if (!this.badge) {
      this.badge = this.root.querySelector('[data-notifications-badge]');
    }
    if (!this.badge) return;

    if (unreadCount > 0) {
      this.badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
      this.badge.classList.remove('d-none');
    } else {
      this.badge.textContent = '0';
      this.badge.classList.add('d-none');
    }

    updateModalUnreadBadge(unreadCount);
  };

  NotificationsWidget.prototype.request = function (url, method, body) {
    var options = {
      method: method || 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
      },
      credentials: 'same-origin',
    };

    if (body) {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    return fetch(url, options).then(function (response) {
      if (!response.ok) throw new Error('Request failed');
      var type = response.headers.get('content-type') || '';
      if (type.indexOf('application/json') !== -1) {
        return response.json();
      }
      return {};
    });
  };

  NotificationsWidget.prototype.refresh = function () {
    var self = this;
    if (!this.indexUrl) return;
    this.resolveDom();

    this.request(withStatus(this.indexUrl, 'unread', 20))
      .then(function (payload) {
        var data = payload.data || {};
        var items = (data.notifications || []).filter(function (n) {
          return !n.read_at;
        });
        self.render(items, data.unread_count || 0);
      })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('Notifications refresh failed', err);
        }
      });
  };

  NotificationsWidget.prototype.render = function (notifications, unreadCount) {
    this.resolveDom();
    announceUnreadCount(unreadCount, true);
    this.applyUnreadCount(unreadCount);
    syncAllBadges(unreadCount, this);

    if (!this.list) return;

    Array.prototype.slice.call(this.list.querySelectorAll('[data-notification-id]')).forEach(function (el) {
      el.remove();
    });

    if (!notifications.length) {
      if (this.empty) this.empty.classList.remove('d-none');
      return;
    }

    if (this.empty) this.empty.classList.add('d-none');

    var self = this;
    notifications.forEach(function (notification) {
      self.list.appendChild(createItem(notification, { guard: self.guard, allowMarkRead: true }));
    });
    scheduleDockPanelReposition();
  };

  NotificationsWidget.prototype.syncEmptyState = function () {
    this.resolveDom();
    if (!this.list || !this.empty) return;
    var remaining = this.list.querySelectorAll('[data-notification-id]:not(.is-dismissing)');
    if (remaining.length) {
      this.empty.classList.add('d-none');
    } else {
      this.empty.classList.remove('d-none');
    }
    scheduleDockPanelReposition();
  };

  NotificationsWidget.prototype.dismissById = function (id) {
    var self = this;
    var nodes = document.querySelectorAll('[data-notification-id="' + id + '"]');
    Array.prototype.slice.call(nodes).forEach(function (item) {
      if (item.classList.contains('is-dismissing')) return;
      slideOutAndRemove(item, function () {
        self.syncEmptyState();
        syncModalEmptyStates();
      });
    });
  };

  NotificationsWidget.prototype.dismissAllInPanel = function () {
    var self = this;
    this.resolveDom();
    if (!this.list) return;

    var items = Array.prototype.slice.call(this.list.querySelectorAll('[data-notification-id]'));
    if (!items.length) {
      this.syncEmptyState();
      return;
    }

    var remaining = items.length;
    items.forEach(function (item) {
      slideOutAndRemove(item, function () {
        remaining -= 1;
        if (remaining <= 0) {
          self.syncEmptyState();
        }
      });
    });
  };

  NotificationsWidget.prototype.markRead = function (id) {
    var self = this;
    var url = fillTemplate(this.markReadTemplate, id);
    if (!url) return;

    this.request(url, 'POST')
      .then(function (payload) {
        var count = payload && payload.data ? payload.data.unread_count : null;
        if (typeof count === 'number') {
          announceUnreadCount(count, false);
          self.applyUnreadCount(count);
          syncAllBadges(count, self);
        }

        self.dismissById(id);

        // Keep the “Previously read” tab fresh if the modal is open.
        if (isNotificationsModalOpen()) {
          loadModalTab(self, 'read', true);
        }
      })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('Mark notification read failed', err);
        }
      });
  };

  NotificationsWidget.prototype.markAllRead = function () {
    var self = this;
    if (!this.markAllUrl) return;

    this.request(this.markAllUrl, 'POST')
      .then(function (payload) {
        var count = payload && payload.data ? payload.data.unread_count : 0;
        announceUnreadCount(count, false);
        self.applyUnreadCount(count);
        syncAllBadges(count, self);
        self.dismissAllInPanel();

        // Also clear unread items inside the modal.
        var modalUnread = document.querySelector('[data-notifications-modal-list="unread"]');
        if (modalUnread) {
          Array.prototype.slice
            .call(modalUnread.querySelectorAll('[data-notification-id]'))
            .forEach(function (item) {
              slideOutAndRemove(item, syncModalEmptyStates);
            });
        }

        if (isNotificationsModalOpen()) {
          loadModalTab(self, 'read', true);
        }
      })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('Mark all notifications read failed', err);
        }
      });
  };

  NotificationsWidget.prototype.destroy = function (id) {
    var self = this;
    var url = fillTemplate(this.destroyTemplate, id);
    if (!url) return;

    this.request(url, 'DELETE')
      .then(function (payload) {
        var count = payload && payload.data ? payload.data.unread_count : null;
        if (typeof count === 'number') {
          announceUnreadCount(count, false);
          self.applyUnreadCount(count);
          syncAllBadges(count, self);
        }
        self.dismissById(id);
      })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('Destroy notification failed', err);
        }
      });
  };

  NotificationsWidget.prototype.acceptOrder = function (url, notificationId, button) {
    if (!url) return;

    if (button) {
      button.disabled = true;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    var token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = csrfToken();
    form.appendChild(token);

    document.body.appendChild(form);

    if (notificationId) {
      this.markRead(notificationId);
    }

    form.submit();
  };

  NotificationsWidget.prototype.openSeeAll = function () {
    openNotificationsModal(this);
  };

  NotificationsWidget.prototype.startPolling = function () {
    var self = this;
    window.setInterval(function () {
      self.refresh();
      if (isNotificationsModalOpen()) {
        var activeTab = getActiveModalTab();
        if (activeTab) loadModalTab(self, activeTab, true);
      }
    }, 8000);
  };

  NotificationsWidget.prototype.startEcho = function () {
    var cfg = window.__notificationsBroadcast || {};
    if (!cfg.key || !this.broadcastChannel || typeof window.Echo === 'undefined') {
      return;
    }

    var self = this;
    try {
      window.Echo.private(this.broadcastChannel).notification(function () {
        self.refresh();
        if (isNotificationsModalOpen()) {
          loadModalTab(self, 'unread', true);
          loadModalTab(self, 'read', true);
        }
      });
    } catch (e) {}
  };

  var widgets = [];

  function syncAllBadges(unreadCount, except) {
    widgets.forEach(function (widget) {
      if (widget === except) return;
      widget.applyUnreadCount(unreadCount);
    });
  }

  function findWidgetFromEventTarget(target) {
    var root = target.closest('[data-notifications-root]');
    if (root && root._notificationsWidget) {
      return root._notificationsWidget;
    }

    var panel = target.closest('[data-dock-panel]');
    if (panel) {
      var panelId = panel.getAttribute('data-dock-panel');
      var toggle = document.querySelector('[data-dock-toggle="' + panelId + '"]');
      if (toggle) {
        root = toggle.closest('[data-notifications-root]');
        if (root && root._notificationsWidget) {
          return root._notificationsWidget;
        }
      }
    }

    var modal = target.closest('[data-notifications-modal]');
    if (modal && modal._notificationsWidget) {
      return modal._notificationsWidget;
    }

    return widgets.length ? widgets[0] : null;
  }

  function getModalEl() {
    return document.querySelector('[data-notifications-modal]');
  }

  function isNotificationsModalOpen() {
    var modal = getModalEl();
    return !!(modal && modal.classList.contains('show'));
  }

  function getActiveModalTab() {
    var active = document.querySelector('[data-notifications-tab].active');
    return active ? active.getAttribute('data-notifications-tab') : 'unread';
  }

  function updateModalUnreadBadge(count) {
    var badge = document.querySelector('[data-notifications-modal-unread-badge]');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.classList.remove('d-none');
    } else {
      badge.textContent = '0';
      badge.classList.add('d-none');
    }
  }

  function syncModalEmptyStates() {
    ['unread', 'read'].forEach(function (status) {
      var list = document.querySelector('[data-notifications-modal-list="' + status + '"]');
      var empty = document.querySelector('[data-notifications-modal-empty="' + status + '"]');
      if (!list || !empty) return;
      var remaining = list.querySelectorAll('[data-notification-id]:not(.is-dismissing)');
      if (remaining.length) {
        empty.classList.add('d-none');
      } else {
        empty.classList.remove('d-none');
      }
    });
  }

  function renderModalList(widget, status, notifications) {
    var list = document.querySelector('[data-notifications-modal-list="' + status + '"]');
    var empty = document.querySelector('[data-notifications-modal-empty="' + status + '"]');
    if (!list) return;

    Array.prototype.slice.call(list.querySelectorAll('[data-notification-id]')).forEach(function (el) {
      el.remove();
    });

    if (!notifications.length) {
      if (empty) empty.classList.remove('d-none');
      return;
    }

    if (empty) empty.classList.add('d-none');

    notifications.forEach(function (notification) {
      list.appendChild(
        createItem(notification, {
          guard: widget.guard,
          allowMarkRead: status === 'unread',
          variant: 'modal',
        })
      );
    });
  }

  function loadModalTab(widget, status, silent) {
    if (!widget || !widget.indexUrl) return;

    widget
      .request(withStatus(widget.indexUrl, status, 50))
      .then(function (payload) {
        var data = payload.data || {};
        if (typeof data.unread_count === 'number') {
          announceUnreadCount(data.unread_count, !silent);
          widget.applyUnreadCount(data.unread_count);
          syncAllBadges(data.unread_count, widget);
        }
        renderModalList(widget, status, data.notifications || []);
      })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('Notifications modal load failed', err);
        }
      });
  }

  function closeNotificationPanels() {
    if (window.AdminDock && typeof window.AdminDock.closePanels === 'function') {
      window.AdminDock.closePanels();
    }

    if (!window.bootstrap || !bootstrap.Dropdown) return;

    document
      .querySelectorAll('[data-notifications-root] [data-bs-toggle="dropdown"]')
      .forEach(function (toggle) {
        var instance = bootstrap.Dropdown.getInstance(toggle);
        if (instance) {
          instance.hide();
        }
      });
  }

  function openNotificationsModal(widget) {
    var modalEl = getModalEl();
    if (!modalEl || !window.bootstrap || !bootstrap.Modal) return;

    closeNotificationPanels();

    modalEl._notificationsWidget = widget;
    loadModalTab(widget, 'unread', true);
    loadModalTab(widget, 'read', true);

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  // Capture phase so we beat default link navigation / other handlers.
  document.addEventListener(
    'click',
    function (e) {
      var widget;

      var seeAllBtn = e.target.closest('[data-notifications-see-all]');
      if (seeAllBtn) {
        widget = findWidgetFromEventTarget(seeAllBtn);
        if (!widget) return;
        e.preventDefault();
        e.stopPropagation();
        widget.openSeeAll();
        return;
      }

      var viewBtn = e.target.closest('[data-notification-view]');
      if (viewBtn) {
        e.preventDefault();
        e.stopPropagation();
        var viewUrl = viewBtn.getAttribute('data-view-url');
        if (!openVendorOrderModal(viewUrl) && viewUrl) {
          window.location.href = viewUrl.replace(/\/details\/?$/, '');
        }
        return;
      }

      var acceptBtn = e.target.closest('[data-notification-accept]');
      if (acceptBtn) {
        widget = findWidgetFromEventTarget(acceptBtn);
        if (!widget) return;
        e.preventDefault();
        e.stopPropagation();
        var acceptItem = acceptBtn.closest('[data-notification-id]');
        widget.acceptOrder(
          acceptBtn.getAttribute('data-accept-url'),
          acceptItem ? acceptItem.getAttribute('data-notification-id') : null,
          acceptBtn
        );
        return;
      }

      var readBtn = e.target.closest('[data-notification-read]');
      if (readBtn) {
        widget = findWidgetFromEventTarget(readBtn);
        if (!widget) return;
        e.preventDefault();
        e.stopPropagation();
        var item = readBtn.closest('[data-notification-id]');
        if (item) widget.markRead(item.getAttribute('data-notification-id'));
        return;
      }

      var archiveBtn = e.target.closest('[data-notification-archive]');
      if (archiveBtn) {
        widget = findWidgetFromEventTarget(archiveBtn);
        if (!widget) return;
        e.preventDefault();
        e.stopPropagation();
        var archiveItem = archiveBtn.closest('[data-notification-id]');
        if (archiveItem) widget.destroy(archiveItem.getAttribute('data-notification-id'));
        return;
      }

      var markAll = e.target.closest('[data-notifications-mark-all]');
      if (markAll) {
        widget = findWidgetFromEventTarget(markAll);
        if (!widget) return;
        e.preventDefault();
        e.stopPropagation();
        widget.markAllRead();
        return;
      }

      var refreshBtn = e.target.closest('[data-notifications-refresh]');
      if (refreshBtn) {
        widget = findWidgetFromEventTarget(refreshBtn);
        if (!widget) return;
        e.preventDefault();
        e.stopPropagation();
        widget.refresh();
        return;
      }

      // Whole notification row (stretched-link clicks land here too).
      var notificationItem = e.target.closest('[data-notification-id]');
      if (!notificationItem) return;

      // Ignore clicks on controls that sit above the stretched link.
      if (
        e.target.closest('[data-notification-view]') ||
        e.target.closest('[data-notification-accept]') ||
        e.target.closest('[data-notification-read]') ||
        e.target.closest('[data-notification-archive]') ||
        e.target.closest('button') ||
        e.target.closest('a:not([data-notification-link])')
      ) {
        return;
      }

      var itemViewUrl = notificationItem.getAttribute('data-view-url');
      if (!itemViewUrl) return;

      e.preventDefault();
      e.stopPropagation();

      widget = findWidgetFromEventTarget(notificationItem);
      if (widget && !notificationItem.classList.contains('marked-as-read')) {
        widget.markRead(notificationItem.getAttribute('data-notification-id'));
      }

      openVendorOrderModal(itemViewUrl);
    },
    true
  );

  document.addEventListener('DOMContentLoaded', function () {
    var roots = Array.prototype.slice.call(document.querySelectorAll('[data-notifications-root]'));
    if (!roots.length) return;

    var layout = document.documentElement.getAttribute('data-admin-nav-layout');
    var preferred = roots;

    if (layout === 'dock') {
      preferred = roots.filter(function (root) {
        return root.closest('.admin-dock, .admin-dock__mobile');
      });
    } else if (layout === 'sidebar') {
      preferred = roots.filter(function (root) {
        return !root.closest('.admin-dock, .admin-dock__mobile, .admin-dock-drawer');
      });
    } else {
      preferred = roots.filter(function (root) {
        return !root.closest('.admin-dock, .admin-dock__mobile, .admin-dock-drawer');
      });
    }

    (preferred.length ? preferred : roots.slice(0, 1)).forEach(function (root) {
      widgets.push(new NotificationsWidget(root));
    });
  });
})(window, document);
