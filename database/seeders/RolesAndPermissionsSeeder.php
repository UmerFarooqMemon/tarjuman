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
            'platform_settings' => [
                'module_en' => 'Platform Settings',
                'module_ar' => 'إعدادات المنصة',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Platform Settings',
                        'name_ar' => 'عرض إعدادات المنصة',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Platform Settings',
                        'name_ar' => 'تعديل إعدادات المنصة',
                    ],
                ],
            ],
            'cms_pages' => [
                'module_en' => 'CMS Pages',
                'module_ar' => 'صفحات المحتوى',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View CMS Pages',
                        'name_ar' => 'عرض صفحات المحتوى',
                    ],
                    'edit' => [
                        'name_en' => 'Edit CMS Pages',
                        'name_ar' => 'تعديل صفحات المحتوى',
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
            'document_types' => [
                'module_en' => 'Document Types',
                'module_ar' => 'أنواع المستندات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Document Types',
                        'name_ar' => 'عرض أنواع المستندات',
                    ],
                    'create' => [
                        'name_en' => 'Create Document Types',
                        'name_ar' => 'إنشاء أنواع مستندات',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Document Types',
                        'name_ar' => 'تعديل أنواع المستندات',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Document Types',
                        'name_ar' => 'حذف أنواع المستندات',
                    ],
                ],
            ],
            'authorities' => [
                'module_en' => 'Authorities',
                'module_ar' => 'الجهات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Authorities',
                        'name_ar' => 'عرض الجهات',
                    ],
                    'create' => [
                        'name_en' => 'Create Authorities',
                        'name_ar' => 'إنشاء جهات',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Authorities',
                        'name_ar' => 'تعديل الجهات',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Authorities',
                        'name_ar' => 'حذف الجهات',
                    ],
                ],
            ],
            'add_ons' => [
                'module_en' => 'Add-Ons',
                'module_ar' => 'الإضافات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Add-Ons',
                        'name_ar' => 'عرض الإضافات',
                    ],
                    'create' => [
                        'name_en' => 'Create Add-Ons',
                        'name_ar' => 'إنشاء إضافات',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Add-Ons',
                        'name_ar' => 'تعديل الإضافات',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Add-Ons',
                        'name_ar' => 'حذف الإضافات',
                    ],
                ],
            ],
            'delivery_speeds' => [
                'module_en' => 'Delivery Speeds',
                'module_ar' => 'سرعات التسليم',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Delivery Speeds',
                        'name_ar' => 'عرض سرعات التسليم',
                    ],
                    'create' => [
                        'name_en' => 'Create Delivery Speeds',
                        'name_ar' => 'إنشاء سرعات تسليم',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Delivery Speeds',
                        'name_ar' => 'تعديل سرعات التسليم',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Delivery Speeds',
                        'name_ar' => 'حذف سرعات التسليم',
                    ],
                ],
            ],
            'plans' => [
                'module_en' => 'Enterprise Plans',
                'module_ar' => 'خطط الشركات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Plans',
                        'name_ar' => 'عرض الخطط',
                    ],
                    'create' => [
                        'name_en' => 'Create Plans',
                        'name_ar' => 'إنشاء خطط',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Plans',
                        'name_ar' => 'تعديل الخطط',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Plans',
                        'name_ar' => 'حذف الخطط',
                    ],
                ],
            ],
            'orders' => [
                'module_en' => 'Orders',
                'module_ar' => 'الطلبات',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Orders',
                        'name_ar' => 'عرض الطلبات',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Orders',
                        'name_ar' => 'تعديل الطلبات',
                    ],
                ],
            ],
            'pricing_rules' => [
                'module_en' => 'Pricing Rules',
                'module_ar' => 'قواعد التسعير',
                'permissions' => [
                    'view' => [
                        'name_en' => 'View Pricing Rules',
                        'name_ar' => 'عرض قواعد التسعير',
                    ],
                    'create' => [
                        'name_en' => 'Create Pricing Rules',
                        'name_ar' => 'إنشاء قواعد تسعير',
                    ],
                    'edit' => [
                        'name_en' => 'Edit Pricing Rules',
                        'name_ar' => 'تعديل قواعد التسعير',
                    ],
                    'delete' => [
                        'name_en' => 'Delete Pricing Rules',
                        'name_ar' => 'حذف قواعد التسعير',
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
