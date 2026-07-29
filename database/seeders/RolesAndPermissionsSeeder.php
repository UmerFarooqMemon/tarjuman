<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permission definitions with bilingual labels (EN + AR).
     * Machine key remains {module}.{action} for Spatie / Gate checks.
     */
    protected function definitions(): array
    {
        return [
            'administrators' => [
                'module_en' => 'Administrators',
                'module_ar' => 'المسؤولون',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Administrators',
                        'name_ar' => 'عرض المسؤولين',
                    ],
                    'create' => [
                        'name_en' => 'Create Administrators',
                        'name_ar' => 'إنشاء مسؤولين',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Administrators',
                        'name_ar' => 'تعديل المسؤولين',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Administrators',
                        'name_ar' => 'حذف المسؤولين',
                    ],
                ],
            ],
            'roles' => [
                'module_en' => 'Roles & Permissions',
                'module_ar' => 'الأدوار والصلاحيات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Roles',
                        'name_ar' => 'عرض الأدوار',
                    ],
                    'create' => [
                        'name_en' => 'Create Roles',
                        'name_ar' => 'إنشاء أدوار',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Roles',
                        'name_ar' => 'تعديل الأدوار',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Roles',
                        'name_ar' => 'حذف الأدوار',
                    ],
                ],
            ],
            'site_settings' => [
                'module_en' => 'Site Settings',
                'module_ar' => 'إعدادات الموقع',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Site Settings',
                        'name_ar' => 'عرض إعدادات الموقع',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Site Settings',
                        'name_ar' => 'تعديل إعدادات الموقع',
                    ],
                ],
            ],
            'vendors' => [
                'module_en' => 'Vendors',
                'module_ar' => 'موردو الخدمة',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Vendors',
                        'name_ar' => 'عرض الموردين',
                    ],
                    'create' => [
                        'name_en' => 'Create Vendors',
                        'name_ar' => 'إنشاء موردين',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Vendors',
                        'name_ar' => 'تعديل الموردين',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Vendors',
                        'name_ar' => 'حذف الموردين',
                    ],
                ],
            ],
            'languages' => [
                'module_en' => 'Languages',
                'module_ar' => 'اللغات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Languages',
                        'name_ar' => 'عرض اللغات',
                    ],
                    'create' => [
                        'name_en' => 'Create Languages',
                        'name_ar' => 'إنشاء لغات',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Languages',
                        'name_ar' => 'تعديل اللغات',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Languages',
                        'name_ar' => 'حذف اللغات',
                    ],
                ],
            ],
            'currencies' => [
                'module_en' => 'Currencies',
                'module_ar' => 'العملات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Currencies',
                        'name_ar' => 'عرض العملات',
                    ],
                    'create' => [
                        'name_en' => 'Create Currencies',
                        'name_ar' => 'إنشاء عملات',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Currencies',
                        'name_ar' => 'تعديل العملات',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Currencies',
                        'name_ar' => 'حذف العملات',
                    ],
                ],
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('admin_permissions.guard', 'admin');
        $permissionNames = [];

        foreach ($this->definitions() as $module => $moduleData) {
            foreach ($moduleData['permissions'] as $action => $labels) {
                $name = "{$module}.{$action}";

                Permission::updateOrCreate(
                    [
                        'name' => $name,
                        'guard_name' => $guard,
                    ],
                    [
                        'module' => $module,
                        'module_en' => $moduleData['module_en'],
                        'module_ar' => $moduleData['module_ar'],
                        'name_en' => $labels['name_en'],
                        'name_ar' => $labels['name_ar'],
                    ]
                );

                $permissionNames[] = $name;
            }
        }

        $roleName = config('admin_permissions.default_role', 'Super Admin');
        $role = Role::findOrCreate($roleName, $guard);
        $role->syncPermissions($permissionNames);

        $systemAdmin = Admin::where('is_system_admin', 1)->first();

        if ($systemAdmin) {
            $systemAdmin->syncRoles([$role]);
        }
    }
}
