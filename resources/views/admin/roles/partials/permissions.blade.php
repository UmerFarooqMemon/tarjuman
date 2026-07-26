@foreach ($groupedPermissions as $module => $permissions)
<div class="col-12 mb-4">
    <div class="border rounded p-3 permission-module" data-module="{{ $module }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ $permissions->first()?->module_label ?? moduleLabel($module) }}</h6>
            <div class="d-flex align-items-center gap-2">
                <label class="switch switch-success mb-0 permission-switch" style="font-size: 15px !important">
                    <input type="checkbox" class="switch-input module-select-all" data-module="{{ $module }}" id="module_{{ $module }}">
                    <span class="switch-toggle-slider">
                        <span class="switch-on"><i class="ti ti-check"></i></span>
                        <span class="switch-off"><i class="ti ti-x"></i></span>
                    </span>
                </label>
                <span class="small text-muted">{!! __('general.select_all') !!}</span>
            </div>
        </div>
        <div class="row g-3">
            @foreach ($permissions as $permission)
            @php
                $action = \Illuminate\Support\Str::afterLast($permission->name, '.');
                $permId = 'perm_'.\Illuminate\Support\Str::slug($permission->name);
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="permission-row d-flex align-items-center gap-3">
                    <label class="switch switch-success mb-0 permission-switch flex-shrink-0" style="font-size: 15px !important" for="{{ $permId }}">
                        <input
                            class="switch-input permission-toggle"
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->name }}"
                            data-module="{{ $module }}"
                            data-action="{{ $action }}"
                            id="{{ $permId }}"
                            {{ in_array($permission->name, $selected ?? [], true) ? 'checked' : '' }}>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="ti ti-check"></i></span>
                            <span class="switch-off"><i class="ti ti-x"></i></span>
                        </span>
                    </label>
                    <span class="permission-row-label flex-grow-1">{{ $permission->label }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

<style>
    .permission-row {
        min-height: 38px;
    }

    .permission-row-label {
        line-height: 1.4;
        word-break: break-word;
    }

    .permission-switch {
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
        width: 3rem;
        min-width: 3rem;
    }

    .permission-switch .switch-toggle-slider {
        margin: 0;
    }
</style>
