/**
 * CMS section editor: live iframe preview via postMessage (unsaved drafts).
 * Public website only updates after Save (API).
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-cms-editor]');
  if (!root) return;

  var form = root.querySelector('[data-cms-form]');
  var iframe = root.querySelector('[data-cms-preview-iframe]');
  var frameWrap = root.querySelector('[data-cms-preview-frame]');
  var openPreview = root.querySelector('[data-cms-open-preview]');
  var localeInput = root.querySelector('[data-cms-preview-locale-input]');
  var sectionType = root.getAttribute('data-section-type') || '';
  var pageSlug = root.getAttribute('data-page-slug') || '';
  var frontendOrigin = root.getAttribute('data-frontend-origin') || '*';
  var previewBase = root.getAttribute('data-preview-base') || '';
  var locale = root.getAttribute('data-locale') || 'en';
  var debounceTimer = null;
  /** @type {Record<string, string>} data URLs for newly picked files (blob: URLs break cross-origin iframes) */
  var uploadPreviewUrls = {};

  function uploadKeyFromInput(input) {
    return (input.getAttribute('name') || '').replace(/^uploads\[/, '').replace(/\]$/, '');
  }

  function readFileAsDataUrl(file) {
    return new Promise(function (resolve, reject) {
      var reader = new FileReader();
      reader.onload = function () {
        resolve(typeof reader.result === 'string' ? reader.result : '');
      };
      reader.onerror = function () {
        reject(reader.error || new Error('Failed to read file'));
      };
      reader.readAsDataURL(file);
    });
  }

  function updateLocalThumb(input, dataUrl) {
    var field = input.closest('.cms-field');
    if (!field) return;
    var thumb = field.querySelector('.cms-asset-thumb');
    if (!thumb && dataUrl) {
      var wrap = document.createElement('div');
      wrap.className = 'mb-2 d-flex align-items-center gap-2';
      wrap.innerHTML = '<img src="" alt="" class="cms-asset-thumb" style="width:40px;height:40px;object-fit:contain;">';
      input.parentNode.insertBefore(wrap, input);
      thumb = wrap.querySelector('.cms-asset-thumb');
    }
    if (thumb && dataUrl) {
      thumb.src = dataUrl;
    }
  }

  function formToContent(formEl) {
    var data = new FormData(formEl);
    var content = {};

    data.forEach(function (value, key) {
      if (key === 'is_enabled' || key.indexOf('_token') === 0 || key === '_method') return;
      if (key.indexOf('uploads[') === 0) return;
      if (key.indexOf('content[') !== 0) return;

      var parts = key.match(/[^\[\]]+/g) || [];
      if (parts[0] !== 'content') return;
      parts = parts.slice(1);
      if (!parts.length) return;
      setDeep(content, parts, value);
    });

    Object.keys(uploadPreviewUrls).forEach(function (dotted) {
      if (!uploadPreviewUrls[dotted]) return;
      setDeep(content, dotted.split('.'), uploadPreviewUrls[dotted]);
    });

    return content;
  }

  function setDeep(obj, parts, value) {
    var cur = obj;
    for (var i = 0; i < parts.length; i++) {
      var part = parts[i];
      var isLast = i === parts.length - 1;
      if (isLast) {
        cur[part] = value;
        return;
      }
      var nextIsIndex = /^\d+$/.test(parts[i + 1] || '');
      if (typeof cur[part] !== 'object' || cur[part] === null) {
        cur[part] = nextIsIndex ? [] : {};
      }
      cur = cur[part];
    }
  }

  function buildPreviewUrl(nextLocale) {
    var url = new URL(previewBase, window.location.origin);
    url.searchParams.set('cms_preview', '1');
    url.searchParams.set('locale', nextLocale);
    url.searchParams.set('page', pageSlug);
    if (sectionType) {
      url.searchParams.set('focus', sectionType);
      url.hash = 'cms-' + sectionType;
    }
    return url.toString();
  }

  function setLocaleButtons(groupSelector, next) {
    root.querySelectorAll(groupSelector + ' [data-locale]').forEach(function (btn) {
      btn.classList.toggle('active', btn.getAttribute('data-locale') === next);
    });
  }

  function pushPreview() {
    if (!iframe || !iframe.contentWindow) return;

    var payload = {
      source: 'tarjuman-cms',
      page: pageSlug,
      type: sectionType,
      locale: locale,
      content: form ? formToContent(form) : {},
    };

    try {
      iframe.contentWindow.postMessage(payload, frontendOrigin || '*');
    } catch (e) {
      // ignore while iframe is still loading
    }
  }

  function schedulePush() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(pushPreview, 250);
  }

  function showFormLocale(next, reloadFrame) {
    locale = next;
    root.querySelectorAll('[data-locale-pane]').forEach(function (pane) {
      pane.classList.toggle('d-none', pane.getAttribute('data-locale-pane') !== next);
    });
    setLocaleButtons('[data-cms-form-locale]', next);
    setLocaleButtons('[data-cms-preview-locale]', next);
    if (localeInput) localeInput.value = next;

    if (reloadFrame) {
      var url = buildPreviewUrl(next);
      if (iframe) iframe.src = url;
      if (openPreview) openPreview.href = url;
    } else {
      pushPreview();
    }
  }

  root.querySelectorAll('[data-cms-form-locale] [data-locale]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      showFormLocale(btn.getAttribute('data-locale') || 'en', false);
    });
  });

  root.querySelectorAll('[data-cms-preview-locale] [data-locale]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      showFormLocale(btn.getAttribute('data-locale') || 'en', false);
    });
  });

  root.querySelectorAll('[data-cms-preview-device] [data-device]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var device = btn.getAttribute('data-device') || 'desktop';
      root.querySelectorAll('[data-cms-preview-device] [data-device]').forEach(function (b) {
        b.classList.toggle('active', b === btn);
      });
      if (frameWrap) {
        frameWrap.classList.toggle('is-desktop', device === 'desktop');
        frameWrap.classList.toggle('is-mobile', device === 'mobile');
      }
    });
  });

  if (form) {
    form.addEventListener('input', schedulePush);
    form.addEventListener('change', function (event) {
      var target = event.target;
      if (target && target.matches && target.matches('input[type="file"][name^="uploads["]')) {
        var dotted = uploadKeyFromInput(target);
        if (!dotted) {
          schedulePush();
          return;
        }

        if (!target.files || !target.files[0]) {
          delete uploadPreviewUrls[dotted];
          schedulePush();
          return;
        }

        readFileAsDataUrl(target.files[0])
          .then(function (dataUrl) {
            uploadPreviewUrls[dotted] = dataUrl;
            updateLocalThumb(target, dataUrl);
            pushPreview();
          })
          .catch(function () {
            delete uploadPreviewUrls[dotted];
            schedulePush();
          });
        return;
      }

      schedulePush();
    });
  }

  if (iframe) {
    iframe.addEventListener('load', function () {
      // Give the Next.js preview listener a moment to attach.
      setTimeout(pushPreview, 150);
      setTimeout(pushPreview, 600);
    });
  }

  window.addEventListener('message', function (event) {
    if (!event.data || event.data.source !== 'tarjuman-cms-preview') return;
    if (frontendOrigin && frontendOrigin !== '*' && event.origin !== frontendOrigin) return;
    if (event.data.locale) {
      showFormLocale(String(event.data.locale), false);
    }
    if (event.data.ready) {
      pushPreview();
    }
  });

  // Repeater add/remove
  root.querySelectorAll('[data-cms-repeater]').forEach(function (repeater) {
    var list = repeater.querySelector('[data-cms-repeater-list]');
    var template = repeater.querySelector('[data-cms-repeater-template]');
    var max = parseInt(repeater.getAttribute('data-max') || '50', 10);
    var min = parseInt(repeater.getAttribute('data-min') || '0', 10);

    function rewriteIndexForRepeater(name, repeaterEl, newIndex) {
      var sample = (repeaterEl.querySelector('[data-cms-repeater-item] [name]') || {}).name || name;
      var match = sample.match(/^(.*?)\[(\d+)\]/);
      if (!match) return name;
      var prefix = match[1];
      return name.replace(prefix + '[' + extractIndex(name, prefix) + ']', prefix + '[' + newIndex + ']');
    }

    function extractIndex(name, prefix) {
      var re = new RegExp('^' + escapeRegExp(prefix) + '\\[(\\d+)\\]');
      var m = name.match(re);
      return m ? m[1] : '0';
    }

    function escapeRegExp(s) {
      return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function reindex() {
      if (!list) return;
      Array.prototype.forEach.call(list.querySelectorAll(':scope > [data-cms-repeater-item]'), function (item, index) {
        item.querySelectorAll('[name]').forEach(function (input) {
          var closestRepeater = input.closest('[data-cms-repeater]');
          if (closestRepeater === repeater) {
            input.setAttribute('name', rewriteIndexForRepeater(input.getAttribute('name') || '', repeater, index));
          }
        });
        var badge = item.querySelector('.badge');
        if (badge) badge.textContent = '#' + (index + 1);
      });
      schedulePush();
    }

    var addBtn = repeater.querySelector('[data-cms-repeater-add]');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        if (!list || !template) return;
        if (list.children.length >= max) return;
        var html = template.innerHTML
          .replace(/__INDEX__/g, String(list.children.length))
          .replace(/__INDEX1__/g, String(list.children.length + 1));
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var node = wrap.firstElementChild;
        if (node) list.appendChild(node);
        reindex();
      });
    }

    repeater.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-cms-repeater-remove]');
      if (!removeBtn) return;
      var item = removeBtn.closest('[data-cms-repeater-item]');
      if (!item || !list) return;
      if (list.querySelectorAll(':scope > [data-cms-repeater-item]').length <= min) return;
      item.remove();
      reindex();
    });
  });

  showFormLocale(locale, false);
})();
