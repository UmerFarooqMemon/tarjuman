/**
 * Vendor order details modal (View from notifications / open board).
 */
(function () {
  'use strict';

  var modalEl = document.getElementById('vendorOrderDetailsModal');
  if (!modalEl) {
    return;
  }

  var bodyEl = modalEl.querySelector('[data-order-modal-body]');
  var titleEl = modalEl.querySelector('[data-order-modal-title]');
  var labels = {};
  try {
    labels = JSON.parse(modalEl.getAttribute('data-labels') || '{}');
  } catch (e) {
    labels = {};
  }

  function t(key, fallback) {
    return labels[key] || fallback || key;
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function moneyHtml(amount, currencyHtml, forceMinus) {
    var value = Number(amount);
    if (!isFinite(value)) value = 0;
    var negative = !!forceMinus || value < 0;
    value = Math.abs(value);
    var formatted = (Math.round(value * 100) / 100).toFixed(2);

    return (
      '<span class="d-inline-flex align-items-center gap-1 vendor-order-money' +
      (negative ? ' text-danger' : '') +
      '">' +
      (negative ? '<span>-</span>' : '') +
      (currencyHtml || '') +
      '<span>' +
      formatted +
      '</span></span>'
    );
  }

  function infoCell(icon, value, isLang) {
    return (
      '<div class="vendor-order-detail__info">' +
      '<span class="vendor-order-detail__info-icon' +
      (isLang ? ' vendor-order-detail__info-icon--lang' : '') +
      '"><i class="' +
      icon +
      '"></i></span>' +
      '<div class="fw-medium text-break">' +
      escapeHtml(value || '—') +
      '</div></div>'
    );
  }

  function summaryRow(label, html, strong) {
    return (
      '<div class="d-flex justify-content-between align-items-center gap-2 mb-2' +
      (strong ? ' fw-semibold' : '') +
      '">' +
      '<span class="text-muted flex-shrink-0">' +
      escapeHtml(label) +
      '</span>' +
      '<span class="text-end min-w-0">' +
      html +
      '</span></div>'
    );
  }

  function documentsHtml(documents) {
    if (!documents || !documents.length) {
      return (
        '<div class="vendor-order-detail__section">' +
        '<div class="fw-semibold mb-2">' +
        escapeHtml(t('order_documents', 'Order documents')) +
        '</div>' +
        '<div class="text-muted small">' +
        escapeHtml(t('no_documents', 'No documents uploaded for this order.')) +
        '</div></div>'
      );
    }

    var rows = documents
      .map(function (doc) {
        var actions =
          '<div class="vendor-order-doc__actions">' +
          (doc.preview_url
            ? '<a href="' +
              escapeHtml(doc.preview_url) +
              '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">' +
              escapeHtml(t('preview', 'Preview')) +
              '</a>'
            : '') +
          (doc.download_url
            ? '<a href="' +
              escapeHtml(doc.download_url) +
              '" class="btn btn-sm btn-primary">' +
              escapeHtml(t('download', 'Download')) +
              '</a>'
            : '') +
          '</div>';

        return (
          '<div class="vendor-order-doc">' +
          '<div class="vendor-order-doc__main">' +
          '<div class="vendor-order-doc__icon"><i class="ti ti-file"></i></div>' +
          '<div class="min-w-0">' +
          '<div class="vendor-order-doc__name">' +
          escapeHtml(doc.name || '—') +
          '</div>' +
          '<div class="vendor-order-doc__meta">' +
          escapeHtml(t('pages', 'Pages')) +
          ': ' +
          escapeHtml(String(doc.pages ?? 0)) +
          ' · ' +
          escapeHtml(t('words', 'Words')) +
          ': ' +
          escapeHtml(String(doc.words ?? 0)) +
          '</div></div></div>' +
          actions +
          '</div>'
        );
      })
      .join('');

    return (
      '<div class="vendor-order-detail__section">' +
      '<div class="fw-semibold mb-2">' +
      escapeHtml(t('order_documents', 'Order documents')) +
      '</div>' +
      '<div class="vendor-order-docs">' +
      rows +
      '</div></div>'
    );
  }

  function detailMainHtml(data) {
    var addOnBadges = (data.add_ons || [])
      .map(function (item) {
        return '<span class="badge vendor-order-addon-badge">' + escapeHtml(item.name) + '</span>';
      })
      .join('');

    return (
      '<div class="vendor-order-detail">' +
      '<div class="vendor-order-detail__hero d-flex align-items-start gap-3">' +
      '<div class="vendor-order-detail__thumb"><i class="ti ti-file-certificate"></i></div>' +
      '<div class="min-w-0">' +
      '<h4 class="mb-0 text-break">' +
      escapeHtml(data.document_type || '—') +
      '</h4>' +
      (data.status_badge_html || data.payment_status_badge_html
        ? '<div class="d-flex flex-wrap align-items-center gap-1 mt-2">' +
          (data.status_badge_html || '') +
          (data.payment_status_badge_html || '') +
          '</div>'
        : data.status_label
          ? '<div class="text-muted small mt-1">' + escapeHtml(data.status_label) + '</div>'
          : '') +
      '</div></div>' +
      (data.notes
        ? '<div class="vendor-order-detail__notes">' +
          '<div class="fw-semibold mb-1">' +
          escapeHtml(t('order_notes', 'Order Notes')) +
          '</div>' +
          '<div class="text-break">' +
          escapeHtml(data.notes) +
          '</div></div>'
        : '') +
      '<div class="row g-3 mt-1">' +
      '<div class="col-md-6 min-w-0">' +
      '<div class="vendor-order-detail__meta">' +
      '<i class="ti ti-calendar-event"></i>' +
      '<span class="fw-medium vendor-order-datetime" dir="ltr">' +
      escapeHtml(data.posted_at || '—') +
      '</span></div></div>' +
      '<div class="col-md-6 min-w-0">' +
      '<div class="vendor-order-detail__meta">' +
      '<i class="ti ti-truck-delivery"></i>' +
      '<div class="fw-medium text-break">' +
      escapeHtml(data.delivery_label || data.delivery_name || '—') +
      '</div></div></div></div>' +
      '<div class="vendor-order-detail__grid vendor-order-detail__grid--triple">' +
      infoCell('ti ti-language-hiragana', data.language_pair, true) +
      infoCell('ti ti-file-text', data.pages_label || String(data.pages ?? 0)) +
      infoCell('ti ti-alphabet-latin', data.words_label || String(data.words ?? 0)) +
      '</div>' +
      (addOnBadges
        ? '<div class="vendor-order-detail__section">' +
          '<div class="fw-semibold mb-2">' +
          escapeHtml(t('add_ons', 'Add-Ons')) +
          '</div>' +
          '<div class="vendor-order-addon-list">' +
          addOnBadges +
          '</div></div>'
        : '') +
      '</div>'
    );
  }

  function summaryHtml(data) {
    var currencyHtml = data.currency_html || '';
    var amounts = data.amounts || {};
    var actions = '';

    if (data.can_accept) {
      actions =
        '<button type="button" class="btn btn-primary w-100" data-order-modal-accept data-accept-url="' +
        escapeHtml(data.accept_url || '') +
        '">' +
        escapeHtml(t('accept', 'Accept')) +
        '</button>';
    } else if (data.show_url) {
      actions =
        '<a href="' +
        escapeHtml(data.show_url) +
        '" class="btn btn-outline-primary w-100">' +
        escapeHtml(t('open_order', 'Open order')) +
        '</a>';
    }

    return (
      '<div class="vendor-order-summary card border shadow-none h-100">' +
      '<div class="card-body">' +
      '<h5 class="mb-3">' +
      escapeHtml(t('order_summary', 'Order Summary')) +
      '</h5>' +
      summaryRow(t('order_amount', 'Order Price'), moneyHtml(amounts.order, currencyHtml)) +
      summaryRow(t('delivery_amount', 'Delivery'), moneyHtml(amounts.delivery, currencyHtml)) +
      summaryRow(t('add_ons_amount', 'Add-Ons'), moneyHtml(amounts.add_ons, currencyHtml)) +
      summaryRow(
        t('platform_fee', 'Platform Charges'),
        moneyHtml(amounts.platform_fee, currencyHtml, true)
      ) +
      '<hr>' +
      summaryRow(t('order_total', 'Total'), moneyHtml(amounts.total, currencyHtml), true) +
      (actions ? '<div class="mt-4">' + actions + '</div>' : '') +
      '</div></div>'
    );
  }

  function render(data) {
    var mode = data.view_mode || (data.can_accept ? 'open' : data.show_url ? 'mine' : 'taken');

    if (mode === 'taken') {
      bodyEl.innerHTML =
        '<div class="vendor-order-modal__row">' +
        '<div class="alert alert-warning mb-4">' +
        '<div class="fw-semibold mb-1">' +
        escapeHtml(data.status_label || t('already_taken_title', 'Already accepted')) +
        '</div>' +
        '<div>' +
        escapeHtml(
          data.status_message ||
            t('already_taken', 'This order was already accepted by another vendor.')
        ) +
        '</div></div>' +
        detailMainHtml(data) +
        '</div>';
      return;
    }

    if (mode === 'mine') {
      bodyEl.innerHTML =
        '<div class="row g-4 vendor-order-modal__row">' +
        '<div class="col-lg-8 min-w-0">' +
        detailMainHtml(data) +
        documentsHtml(data.documents || []) +
        '</div>' +
        '<div class="col-lg-4 min-w-0">' +
        (data.show_summary !== false ? summaryHtml(data) : '') +
        '</div></div>';
      return;
    }

    // open
    bodyEl.innerHTML =
      '<div class="row g-4 vendor-order-modal__row">' +
      '<div class="col-lg-8 min-w-0">' +
      detailMainHtml(data) +
      '</div>' +
      '<div class="col-lg-4 min-w-0">' +
      summaryHtml(data) +
      '</div></div>';
  }

  function closeNotificationPanels() {
    if (window.AdminDock && typeof window.AdminDock.closePanels === 'function') {
      window.AdminDock.closePanels();
    }
    document.querySelectorAll('[data-dock-panel]').forEach(function (panel) {
      if (!panel.hidden) {
        panel.hidden = true;
        panel.setAttribute('aria-hidden', 'true');
      }
    });
    document.querySelectorAll('[data-dock-toggle][aria-expanded="true"]').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
  }

  function setLoading() {
    if (titleEl) titleEl.textContent = t('loading', 'Loading…');
    bodyEl.innerHTML =
      '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div></div>';
  }

  function setError(message) {
    if (titleEl) titleEl.textContent = t('order_details', 'Order details');
    bodyEl.innerHTML =
      '<div class="alert alert-danger mb-0">' +
      escapeHtml(message || t('load_failed', 'Could not load order details.')) +
      '</div>';
  }

  function open(url) {
    if (!url) return;
    closeNotificationPanels();
    setLoading();
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    fetch(url, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (data) {
        if (titleEl) {
          titleEl.textContent = data.order_id || t('order_details', 'Order details');
        }
        render(data);
      })
      .catch(function () {
        setError();
      });
  }

  modalEl.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-order-modal-accept]');
    if (!btn) return;
    var url = btn.getAttribute('data-accept-url');
    if (!url) return;
    btn.disabled = true;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';
    var csrf = document.querySelector('meta[name="csrf-token"]');
    if (csrf) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = '_token';
      input.value = csrf.getAttribute('content') || '';
      form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
  });

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-vendor-order-view]');
    if (!trigger) return;
    e.preventDefault();
    open(trigger.getAttribute('data-view-url') || trigger.getAttribute('href'));
  });

  window.VendorOrderModal = {
    open: open,
  };
})();
