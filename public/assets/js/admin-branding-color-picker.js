(function () {
    var activeTarget = null;
    var originalValue = null;
    var colorPicker = null;

    function normalizeHex(value, fallback) {
        if (typeof value === 'string' && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value.trim())) {
            return value.trim();
        }

        return fallback || '#000000';
    }

    function clampAngle(value) {
        var angle = parseInt(value, 10);

        if (isNaN(angle)) {
            return 180;
        }

        return Math.max(0, Math.min(360, angle));
    }

    function updateSwatch(swatch, color) {
        if (!swatch) {
            return;
        }

        swatch.style.background = color || 'transparent';
    }

    function applyColor(target, color) {
        if (!target || !target.input) {
            return;
        }

        var hex = color || '';

        target.input.value = hex;
        updateSwatch(target.swatch, hex);
        refreshGradientPreviews();
    }

    function refreshGradientPreviews() {
        document.querySelectorAll('.js-gradient-block').forEach(function (block) {
            var preview = block.querySelector('.js-gradient-preview');
            var startInput = document.getElementById(block.dataset.startInput);
            var endInput = document.getElementById(block.dataset.endInput);
            var angleInput = document.getElementById(block.dataset.angleInput);
            var defaultStart = block.dataset.defaultStart || '#000000';
            var start = normalizeHex(startInput ? startInput.value : '', defaultStart);
            var endValue = endInput ? endInput.value.trim() : '';
            var hasEnd = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(endValue);
            var angle = clampAngle(angleInput ? angleInput.value : 180);

            if (!preview) {
                return;
            }

            if (hasEnd) {
                preview.style.background = 'linear-gradient(' + angle + 'deg, ' + start + ' 0%, ' + endValue + ' 100%)';
                preview.classList.remove('is-solid');
            } else {
                preview.style.background = start;
                preview.classList.add('is-solid');
            }
        });
    }

    function syncAngleInputs(sourceInput) {
        var targetId = sourceInput.dataset.targetInput;
        var numberInput = document.getElementById(targetId);
        var rangeInput = document.getElementById(targetId + '_range');

        if (!numberInput) {
            return;
        }

        var angle = clampAngle(sourceInput.value);
        numberInput.value = angle;

        if (rangeInput) {
            rangeInput.value = angle;
        }

        refreshGradientPreviews();
    }

    function updatePreview(color) {
        var preview = document.getElementById('brandingColorModalPreview');

        if (preview) {
            preview.style.background = color.hexString;
        }
    }

    function addSliderLabels() {
        var labels = ['Hue', 'Saturation', 'Value'];
        var sliders = document.querySelectorAll('#brandingColorModalPicker .IroSlider');

        sliders.forEach(function (slider, index) {
            if (slider.previousElementSibling && slider.previousElementSibling.classList.contains('branding-slider-label')) {
                return;
            }

            var label = document.createElement('label');
            label.className = 'branding-slider-label';
            label.textContent = labels[index] || '';
            slider.parentNode.insertBefore(label, slider);
        });
    }

    function openModal(trigger) {
        var input = document.getElementById(trigger.dataset.targetInput);
        var swatch = document.getElementById(trigger.dataset.targetSwatch);
        var modalEl = document.getElementById('brandingColorModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var fallback = trigger.dataset.fallbackColor || '#000000';
        var current = input && input.value ? normalizeHex(input.value, fallback) : fallback;

        originalValue = input ? input.value : '';
        activeTarget = {
            input: input,
            swatch: swatch,
        };

        if (!colorPicker) {
            colorPicker = new iro.ColorPicker('#brandingColorModalPicker', {
                color: current,
                layout: [
                    { component: iro.ui.Slider, options: { sliderType: 'hue' } },
                    { component: iro.ui.Slider, options: { sliderType: 'saturation' } },
                    { component: iro.ui.Slider, options: { sliderType: 'value' } },
                ],
            });

            colorPicker.on('color:change', function (color) {
                updatePreview(color);
            });

            addSliderLabels();
        } else {
            colorPicker.color.hexString = current;
        }

        updatePreview(colorPicker.color);
        modal.show();
    }

    function commitSelection() {
        if (activeTarget && colorPicker) {
            applyColor(activeTarget, colorPicker.color.hexString);
        }

        activeTarget = null;
        originalValue = null;
        bootstrap.Modal.getInstance(document.getElementById('brandingColorModal'))?.hide();
    }

    function cancelSelection() {
        if (activeTarget) {
            applyColor(activeTarget, originalValue);
        }

        activeTarget = null;
        originalValue = null;
        bootstrap.Modal.getInstance(document.getElementById('brandingColorModal'))?.hide();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-open-color-modal').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                openModal(trigger);
            });
        });

        document.getElementById('brandingColorModalSet')?.addEventListener('click', commitSelection);
        document.getElementById('brandingColorModalCancel')?.addEventListener('click', cancelSelection);

        document.getElementById('brandingColorModal')?.addEventListener('hidden.bs.modal', function () {
            if (activeTarget) {
                applyColor(activeTarget, originalValue);
                activeTarget = null;
                originalValue = null;
            }
        });

        document.querySelectorAll('.js-color-hex').forEach(function (input) {
            input.addEventListener('input', function () {
                var swatch = document.getElementById(input.dataset.swatchTarget);

                if (swatch && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(input.value.trim())) {
                    updateSwatch(swatch, input.value.trim());
                }

                refreshGradientPreviews();
            });
        });

        document.querySelectorAll('.js-gradient-angle-range, .js-gradient-angle-input').forEach(function (input) {
            input.addEventListener('input', function () {
                syncAngleInputs(input);
            });
        });

        document.querySelectorAll('.js-gradient-clear').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.targetInput);
                var swatch = document.getElementById(button.dataset.targetSwatch);
                var angleInput = document.getElementById(button.dataset.targetAngle);
                var angleRange = angleInput ? document.getElementById(angleInput.id + '_range') : null;

                applyColor({ input: input, swatch: swatch }, '');

                if (angleInput) {
                    angleInput.value = 180;
                }

                if (angleRange) {
                    angleRange.value = 180;
                }

                refreshGradientPreviews();
            });
        });

        document.querySelectorAll('.js-color-swatch').forEach(function (swatch) {
            var input = document.getElementById(swatch.dataset.inputTarget);

            if (input && input.value) {
                updateSwatch(swatch, normalizeHex(input.value, swatch.dataset.fallbackColor));
            }
        });

        refreshGradientPreviews();
    });
})();
