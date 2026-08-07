/**
 * Vendor My Orders — expandable filters + AJAX DataTable.
 */
(function ($) {
  'use strict';

  var tableEl = document.querySelector('[data-orders-table]');
  if (!tableEl || typeof $ === 'undefined' || !$.fn.DataTable) {
    return;
  }

  var form = document.querySelector('[data-orders-filters]');
  var panel = document.querySelector('[data-orders-filters-panel]');
  var toggle = document.querySelector('[data-orders-filters-toggle]');
  var resetBtn = document.querySelector('[data-orders-filters-reset]');
  var badgeEl = document.querySelector('[data-orders-filters-badge]');
  var dataUrl = tableEl.getAttribute('data-orders-url') || '';
  var searchTimer = null;

  function filterParams() {
    var params = {};
    if (!form) return params;
    new FormData(form).forEach(function (value, key) {
      if (value !== null && String(value).trim() !== '') {
        params[key] = value;
      }
    });
    return params;
  }

  function activeFilterCount() {
    var params = filterParams();
    var count = 0;
    Object.keys(params).forEach(function (key) {
      if (key === 'sort') {
        if (params[key] !== 'newest') count += 1;
        return;
      }
      count += 1;
    });
    return count;
  }

  function hasActiveFilters() {
    return activeFilterCount() > 0;
  }

  function syncToggleState() {
    if (!toggle || !panel) return;
    var open = panel.classList.contains('show');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.classList.toggle('active', open);
  }

  function syncFilterUi() {
    var count = activeFilterCount();

    if (badgeEl) {
      if (count > 0) {
        badgeEl.textContent = String(count);
        badgeEl.hidden = false;
      } else {
        badgeEl.textContent = '';
        badgeEl.hidden = true;
      }
    }

    if (resetBtn) {
      resetBtn.classList.toggle('d-none', count === 0);
    }

    syncToggleState();
  }

  function openFiltersIfNeeded() {
    if (!panel || !hasActiveFilters()) return;
    if (window.bootstrap && bootstrap.Collapse) {
      bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false }).show();
    } else {
      panel.classList.add('show');
    }
    syncToggleState();
  }

  var table = $(tableEl).DataTable({
    language: typeof langUrl !== 'undefined' ? { url: langUrl } : undefined,
    processing: true,
    serverSide: false,
    searching: false,
    pageLength: 25,
    order: [],
    scrollX: true,
    ajax: {
      url: dataUrl,
      data: function (d) {
        var params = filterParams();
        Object.keys(params).forEach(function (key) {
          d[key] = params[key];
        });
      },
      dataSrc: 'data',
    },
    columns: [
      { data: 'order_id' },
      { data: 'document_type' },
      {
        data: 'status',
        orderable: false,
        render: function (data) {
          return data || '—';
        },
      },
      {
        data: 'payment_status',
        orderable: false,
        render: function (data) {
          return data || '—';
        },
      },
      {
        data: 'amount_html',
        orderable: false,
        render: function (data) {
          return data || '—';
        },
      },
      {
        data: 'created_at',
        render: function (data) {
          return '<span dir="ltr">' + (data || '—') + '</span>';
        },
      },
      {
        data: 'action_html',
        orderable: false,
        searchable: false,
        render: function (data) {
          return data || '';
        },
      },
    ],
    columnDefs: [{ orderable: false, targets: [-1] }],
  });

  function reload() {
    syncFilterUi();
    table.ajax.reload(null, true);
  }

  if (toggle && panel) {
    toggle.addEventListener('click', function () {
      if (window.bootstrap && bootstrap.Collapse) {
        bootstrap.Collapse.getOrCreateInstance(panel).toggle();
      } else {
        panel.classList.toggle('show');
        syncToggleState();
      }
    });

    panel.addEventListener('shown.bs.collapse', syncToggleState);
    panel.addEventListener('hidden.bs.collapse', syncToggleState);
  }

  if (form) {
    form.querySelectorAll('select[data-orders-filter-input]').forEach(function (select) {
      select.addEventListener('change', reload);
    });

    var searchInput = form.querySelector('input[name="q"]');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(reload, 350);
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      reload();
    });
  }

  if (resetBtn && form) {
    resetBtn.addEventListener('click', function () {
      form.reset();
      form.querySelectorAll('select').forEach(function (select) {
        if (select.name === 'sort') {
          select.value = 'newest';
        } else {
          select.value = '';
        }
      });
      var searchInput = form.querySelector('input[name="q"]');
      if (searchInput) searchInput.value = '';
      reload();
    });
  }

  openFiltersIfNeeded();
  syncFilterUi();
})(window.jQuery);
