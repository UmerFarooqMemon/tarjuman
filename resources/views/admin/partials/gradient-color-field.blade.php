@php
    $startValue = old($startName, $records->{$startName} ?: $defaultStart);
    $endValue = old($endName, $records->{$endName} ?: '');
    $angleValue = (int) old($angleName, $records->{$angleName} ?? 135);
@endphp

<div class="mb-4 col-md-6 js-gradient-block"
     data-start-input="{{ $startName }}"
     data-end-input="{{ $endName }}"
     data-angle-input="{{ $angleName }}"
     data-default-start="{{ $defaultStart }}">
    <label class="form-label d-block">{{ $label }}</label>
    <div class="js-gradient-preview rounded border mb-3" style="height: 48px;"></div>

    <div class="row g-2 align-items-end">
        <div class="col-sm-4">
            <label class="form-label small mb-1" for="{{ $startName }}">{!! __('general.gradient_start') !!}</label>
            <div class="input-group">
                <button type="button"
                        class="btn btn-outline-secondary px-2 js-open-color-modal"
                        data-target-input="{{ $startName }}"
                        data-target-swatch="{{ $startName }}_swatch"
                        data-fallback-color="{{ $defaultStart }}">
                    <span id="{{ $startName }}_swatch"
                          class="js-color-swatch d-inline-block rounded border"
                          data-input-target="{{ $startName }}"
                          data-fallback-color="{{ $defaultStart }}"
                          style="width: 1.25rem; height: 1.25rem; vertical-align: middle;"></span>
                </button>
                <input type="text"
                       class="form-control js-color-hex"
                       id="{{ $startName }}"
                       name="{{ $startName }}"
                       value="{{ $startValue }}"
                       maxlength="7"
                       data-swatch-target="{{ $startName }}_swatch"
                       placeholder="#000000">
            </div>
        </div>

        <div class="col-sm-4">
            <label class="form-label small mb-1" for="{{ $endName }}">{!! __('general.gradient_end') !!}</label>
            <div class="input-group">
                <button type="button"
                        class="btn btn-outline-secondary px-2 js-open-color-modal"
                        data-target-input="{{ $endName }}"
                        data-target-swatch="{{ $endName }}_swatch"
                        data-fallback-color="{{ $defaultEnd }}">
                    <span id="{{ $endName }}_swatch"
                          class="js-color-swatch d-inline-block rounded border"
                          data-input-target="{{ $endName }}"
                          data-fallback-color="{{ $defaultEnd }}"
                          style="width: 1.25rem; height: 1.25rem; vertical-align: middle;"></span>
                </button>
                <input type="text"
                       class="form-control js-color-hex"
                       id="{{ $endName }}"
                       name="{{ $endName }}"
                       value="{{ $endValue }}"
                       maxlength="7"
                       data-swatch-target="{{ $endName }}_swatch"
                       placeholder="#9E95F5">
                <button type="button"
                        class="btn btn-outline-secondary js-gradient-clear"
                        title="{!! __('general.clear') !!}"
                        data-target-input="{{ $endName }}"
                        data-target-swatch="{{ $endName }}_swatch"
                        data-target-angle="{{ $angleName }}">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        </div>

        <div class="col-sm-4">
            <label class="form-label small mb-1" for="{{ $angleName }}">{!! __('general.gradient_angle') !!}</label>
            <div class="d-flex align-items-center gap-2">
                <input type="range"
                       class="form-range flex-grow-1 mb-0 js-gradient-angle-range"
                       id="{{ $angleName }}_range"
                       min="0"
                       max="360"
                       value="{{ $angleValue }}"
                       data-target-input="{{ $angleName }}">
                <input type="number"
                       class="form-control js-gradient-angle-input"
                       id="{{ $angleName }}"
                       name="{{ $angleName }}"
                       min="0"
                       max="360"
                       value="{{ $angleValue }}"
                       style="max-width: 5rem;"
                       data-target-input="{{ $angleName }}">
            </div>
        </div>
    </div>
</div>
