/**
 * Vendor Discover marketplace — filter + browse-more feed.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-vendor-discover]');
  if (!root) return;

  var grid = root.querySelector('[data-discover-grid]');
  var pager = root.querySelector('[data-discover-pager]');
  var moreBtn = root.querySelector('[data-discover-more]');
  var totalEl = root.querySelector('[data-discover-total]');
  var feedUrl = root.getAttribute('data-feed-url') || '';
  var csrf = root.getAttribute('data-csrf') || '';
  var labels = {};

  try {
    labels = JSON.parse(root.getAttribute('data-labels') || '{}');
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

  function cardHtml(card) {
    var addOns = Array.isArray(card.add_ons) ? card.add_ons : [];
    var addOnHtml = addOns.length
      ? '<div class="vendor-discover-card__addons">' +
        addOns
          .map(function (item) {
            return '<span class="vendor-discover-card__addon">' + escapeHtml(item.name) + '</span>';
          })
          .join('') +
        '</div>'
      : '';

    var notesHtml = card.notes
      ? '<p class="vendor-discover-card__notes">' + escapeHtml(String(card.notes).slice(0, 110)) + '</p>'
      : '';

    return (
      '<article class="vendor-discover-card" data-order-id="' +
      escapeHtml(card.order_id) +
      '">' +
      '<div class="vendor-discover-card__top">' +
      '<div class="vendor-discover-card__type">' +
      escapeHtml(card.document_type || '—') +
      '</div>' +
      '<div class="vendor-discover-card__amount">' +
      (card.amount_html || escapeHtml(String(card.amount || '0'))) +
      '</div></div>' +
      '<div class="vendor-discover-card__id">' +
      escapeHtml(card.order_id || '') +
      '</div>' +
      '<div class="vendor-discover-card__meta">' +
      '<div><i class="ti ti-language-hiragana"></i><span>' +
      escapeHtml(card.language_pair || '—') +
      '</span></div>' +
      '<div><i class="ti ti-truck-delivery"></i><span>' +
      escapeHtml(card.delivery_label || '—') +
      '</span></div>' +
      '<div><i class="ti ti-file-text"></i><span>' +
      escapeHtml(card.pages_label || String(card.pages || 0)) +
      '</span></div>' +
      '<div><i class="ti ti-alphabet-latin"></i><span>' +
      escapeHtml(card.words_label || String(card.words || 0)) +
      '</span></div></div>' +
      addOnHtml +
      notesHtml +
      '<div class="vendor-discover-card__footer">' +
      '<span class="vendor-discover-card__time" dir="ltr">' +
      escapeHtml(card.posted_at || '') +
      '</span>' +
      '<div class="vendor-discover-card__actions">' +
      '<button type="button" class="btn btn-sm btn-outline-primary" data-vendor-order-view data-view-url="' +
      escapeHtml(card.view_url || '') +
      '">' +
      escapeHtml(t('view', 'View')) +
      '</button>' +
      '<form method="POST" action="' +
      escapeHtml(card.accept_url || '') +
      '">' +
      '<input type="hidden" name="_token" value="' +
      escapeHtml(csrf) +
      '">' +
      '<button type="submit" class="btn btn-sm btn-primary">' +
      escapeHtml(t('accept', 'Accept')) +
      '</button></form></div></div></article>'
    );
  }

  function setPager(hasMore, nextPage) {
    if (!pager) return;

    if (hasMore && nextPage) {
      pager.innerHTML =
        '<button type="button" class="btn btn-outline-primary vendor-discover__browse" data-discover-more data-next-page="' +
        escapeHtml(String(nextPage)) +
        '">' +
        escapeHtml(t('browse_more', 'Browse more')) +
        '</button>';
      moreBtn = pager.querySelector('[data-discover-more]');
      return;
    }

    if (grid && grid.querySelector('.vendor-discover-card')) {
      pager.innerHTML =
        '<p class="vendor-discover__no-more mb-0" data-discover-no-more>' +
        escapeHtml(t('no_more', 'No more orders')) +
        '</p>';
    } else {
      pager.innerHTML = '';
    }
    moreBtn = null;
  }

  function loadMore(page) {
    if (!feedUrl || !page) return;

    var btn = root.querySelector('[data-discover-more]');
    if (btn) {
      btn.disabled = true;
      btn.textContent = t('loading', 'Loading…');
    }

    var url = new URL(feedUrl, window.location.origin);
    var current = new URLSearchParams(window.location.search);
    current.forEach(function (value, key) {
      url.searchParams.set(key, value);
    });
    url.searchParams.set('page', String(page));

    fetch(url.toString(), {
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
      .then(function (payload) {
        var data = payload.data || {};
        var orders = Array.isArray(data.orders) ? data.orders : [];
        var meta = data.meta || {};

        if (totalEl && typeof meta.total !== 'undefined') {
          totalEl.textContent = String(meta.total);
        }

        if (!grid) return;

        var empty = grid.querySelector('[data-discover-empty]');
        if (empty) empty.remove();

        orders.forEach(function (order) {
          if (grid.querySelector('[data-order-id="' + String(order.order_id || '').replace(/"/g, '\\"') + '"]')) {
            return;
          }
          grid.insertAdjacentHTML('beforeend', cardHtml(order));
        });

        setPager(!!meta.has_more, meta.next_page || null);
      })
      .catch(function () {
        if (btn) {
          btn.disabled = false;
          btn.textContent = t('browse_more', 'Browse more');
        }
        if (window.toastr) {
          toastr.error(t('load_failed', 'Could not load more orders.'));
        }
      });
  }

  root.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-discover-more]');
    if (!btn || !root.contains(btn)) return;
    e.preventDefault();
    loadMore(btn.getAttribute('data-next-page'));
  });
  // Changing filters submits immediately (search still uses Apply / Enter).
  var form = root.querySelector('[data-discover-filters]');
  if (form) {
    form.querySelectorAll('select').forEach(function (select) {
      select.addEventListener('change', function () {
        form.requestSubmit ? form.requestSubmit() : form.submit();
      });
    });
  }
})();
