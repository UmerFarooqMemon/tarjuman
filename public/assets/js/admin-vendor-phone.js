/**
 * Vendor create/edit: country-code phone inputs (intl-tel-input).
 */
'use strict';

(function () {
  if (typeof window.intlTelInput !== 'function') {
    return;
  }

  var utilsUrl =
    document.documentElement.getAttribute('data-iti-utils') ||
    (typeof assetsPath !== 'undefined'
      ? assetsPath + 'vendor/libs/intl-tel-input/js/utils.js'
      : '/assets/vendor/libs/intl-tel-input/js/utils.js');

  var invalidMsg =
    document.documentElement.getAttribute('data-iti-invalid-msg') ||
    'Please enter a valid phone number for the selected country.';

  var instances = [];

  document.querySelectorAll('[data-intl-phone]').forEach(function (input) {
    var fieldWrap = input.closest('.mb-3') || input.parentElement;
    var initial = (input.getAttribute('data-initial-phone') || input.value || '').trim();

    // Must be LTR before init so separateDialCode padding is applied on the left
    input.setAttribute('dir', 'ltr');

    var iti = window.intlTelInput(input, {
      initialCountry: 'ae',
      preferredCountries: ['ae', 'sa', 'qa', 'kw', 'bh', 'om', 'eg', 'jo', 'pk', 'in', 'gb', 'us'],
      separateDialCode: true,
      nationalMode: true,
      strictMode: true,
      formatOnDisplay: true,
      autoPlaceholder: 'polite',
      searchInputClass: 'form-control',
      i18n: {
        searchPlaceholder: '',
      },
      utilsScript: utilsUrl
    });

    var container = typeof iti.getContainer === 'function' ? iti.getContainer() : input.closest('.iti');
    if (container) {
      container.setAttribute('dir', 'ltr');
    }

    var clearSearchPlaceholder = function () {
      if (!container) {
        return;
      }
      container.querySelectorAll('.iti__search-input, input[type="search"]').forEach(function (searchInput) {
        searchInput.setAttribute('placeholder', '');
        searchInput.placeholder = '';
      });
    };

    var syncPadding = function () {
      if (!container) {
        return;
      }
      var countryEl = container.querySelector('.iti__selected-country, .iti__country-container');
      if (!countryEl) {
        return;
      }
      var gutter = Math.ceil(countryEl.getBoundingClientRect().width) + 14;
      if (gutter > 14) {
        input.style.paddingLeft = gutter + 'px';
        input.style.paddingInlineStart = gutter + 'px';
      }
      clearSearchPlaceholder();
    };

    if (initial) {
      iti.setNumber(initial);
    }

    // Recalculate after layout / flag assets settle
    syncPadding();
    requestAnimationFrame(syncPadding);
    setTimeout(syncPadding, 50);
    input.addEventListener('countrychange', syncPadding);
    input.addEventListener('open:countrydropdown', clearSearchPlaceholder);
    window.addEventListener('load', syncPadding);
    clearSearchPlaceholder();

    var feedback = fieldWrap ? fieldWrap.querySelector('[data-intl-phone-error]') : null;
    if (!feedback && fieldWrap) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback d-block';
      feedback.setAttribute('data-intl-phone-error', '');
      feedback.hidden = true;
      fieldWrap.appendChild(feedback);
    }

    var clearError = function () {
      input.classList.remove('is-invalid');
      if (feedback) {
        feedback.hidden = true;
        feedback.textContent = '';
      }
    };

    var showError = function () {
      input.classList.add('is-invalid');
      if (feedback) {
        feedback.hidden = false;
        feedback.textContent = invalidMsg;
      }
    };

    input.addEventListener('input', clearError);
    input.addEventListener('countrychange', clearError);
    input.addEventListener('blur', function () {
      var raw = (input.value || '').trim();
      if (!raw) {
        clearError();
        return;
      }
      if (iti.isValidNumber()) {
        clearError();
      } else {
        showError();
      }
    });

    instances.push({ input: input, iti: iti, showError: showError, clearError: clearError });
  });

  document.querySelectorAll('form').forEach(function (form) {
    if (!form.querySelector('[data-intl-phone]')) {
      return;
    }

    form.addEventListener('submit', function (event) {
      var valid = true;

      instances.forEach(function (item) {
        if (!form.contains(item.input)) {
          return;
        }

        var raw = (item.input.value || '').trim();
        if (!raw) {
          item.input.value = '';
          item.clearError();
          return;
        }

        var number = item.iti.getNumber();
        var ok = item.iti.isValidNumber();
        if (ok === false || (ok !== true && !/^\+[1-9]\d{6,14}$/.test(number || ''))) {
          valid = false;
          item.showError();
          return;
        }

        item.input.value = number;
        item.clearError();
      });

      if (!valid) {
        event.preventDefault();
        event.stopPropagation();
        var firstInvalid = form.querySelector('[data-intl-phone].is-invalid');
        if (firstInvalid) {
          firstInvalid.focus();
        }
      }
    });
  });
})();
