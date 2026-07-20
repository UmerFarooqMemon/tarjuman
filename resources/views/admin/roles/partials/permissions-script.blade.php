<script>
    $(function () {
        var dependentActions = ['create', 'edit', 'delete'];

        function viewToggle(module) {
            return $('.permission-toggle[data-module="' + module + '"][data-action="view"]');
        }

        function moduleToggles(module) {
            return $('.permission-toggle[data-module="' + module + '"]');
        }

        function syncModuleToggle(module) {
            var $toggles = moduleToggles(module);
            var total = $toggles.length;
            var checked = $toggles.filter(':checked').length;
            $('#module_' + module).prop('checked', total > 0 && total === checked);
        }

        function ensureViewEnabled(module) {
            var $view = viewToggle(module);
            if ($view.length && !$view.is(':checked')) {
                $view.prop('checked', true);
            }
        }

        function disableDependentsWhenViewOff(module) {
            dependentActions.forEach(function (action) {
                $('.permission-toggle[data-module="' + module + '"][data-action="' + action + '"]')
                    .prop('checked', false);
            });
        }

        $('.module-select-all').each(function () {
            syncModuleToggle($(this).data('module'));
        });

        $(document).on('change', '.module-select-all', function () {
            var module = $(this).data('module');
            var checked = $(this).is(':checked');
            moduleToggles(module).prop('checked', checked);
        });

        $(document).on('change', '.permission-toggle', function () {
            var $toggle = $(this);
            var module = $toggle.data('module');
            var action = $toggle.data('action');
            var enabled = $toggle.is(':checked');

            if (enabled && dependentActions.indexOf(action) !== -1) {
                ensureViewEnabled(module);
            }

            if (!enabled && action === 'view') {
                disableDependentsWhenViewOff(module);
            }

            syncModuleToggle(module);
        });
    });
</script>
