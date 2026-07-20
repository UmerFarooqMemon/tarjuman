<?php

if (! function_exists('uploadsDir')) {
    /**
     * Relative public uploads path for a module (trailing slash).
     */
    function uploadsDir(string $module = 'front'): string
    {
        $path = public_path('uploads/'.$module);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return 'uploads/'.$module.'/';
    }
}

if (! function_exists('permissionLabel')) {
    /**
     * Locale-aware permission label from DB.
     *
     * @param  \App\Models\Permission|string  $permission
     */
    function permissionLabel(\App\Models\Permission|string $permission): string
    {
        if (is_string($permission)) {
            $name = $permission;
            $permission = \App\Models\Permission::query()
                ->where('name', $name)
                ->where('guard_name', config('admin_permissions.guard', 'admin'))
                ->first();

            if (! $permission) {
                return $name;
            }
        }

        return $permission->label;
    }
}

if (! function_exists('moduleLabel')) {
    /**
     * Locale-aware module label from DB.
     */
    function moduleLabel(string $module): string
    {
        $permission = \App\Models\Permission::query()
            ->where('module', $module)
            ->where('guard_name', config('admin_permissions.guard', 'admin'))
            ->first();

        if ($permission) {
            return $permission->module_label;
        }

        return str_replace('_', ' ', ucfirst($module));
    }
}
