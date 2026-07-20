@php
    $value = old($name, $records->{$name} ?: $default);
@endphp

<div class="mb-3 col-md-3 col-sm-6">
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    <div class="input-group">
        <button type="button"
                class="btn btn-outline-secondary px-2 js-open-color-modal"
                data-target-input="{{ $name }}"
                data-target-swatch="{{ $name }}_swatch"
                data-fallback-color="{{ $default }}">
            <span id="{{ $name }}_swatch"
                  class="js-color-swatch d-inline-block rounded border"
                  data-input-target="{{ $name }}"
                  data-fallback-color="{{ $default }}"
                  style="width: 1.25rem; height: 1.25rem; vertical-align: middle; background: {{ $value }};"></span>
        </button>
        <input type="text"
               class="form-control js-color-hex"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $value }}"
               maxlength="7"
               data-swatch-target="{{ $name }}_swatch"
               placeholder="{{ $default }}">
    </div>
</div>
