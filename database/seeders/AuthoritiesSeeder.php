<?php

namespace Database\Seeders;

use App\Models\Authority;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class AuthoritiesSeeder extends Seeder
{
    public function run(): void
    {
        $authorities = [
            ['name_en' => 'Ministry of Justice (MOJ)', 'name_ar' => 'وزارة العدل'],
            ['name_en' => 'Ministry of Foreign Affairs (MOFA)', 'name_ar' => 'وزارة الخارجية'],
            ['name_en' => 'ICP (Visa & Residency)', 'name_ar' => 'الهوية والجنسية (الإقامة والتأشيرات)'],
            ['name_en' => 'Dubai Courts', 'name_ar' => 'محاكم دبي'],
            ['name_en' => 'Abu Dhabi Judicial Department', 'name_ar' => 'دائرة القضاء – أبوظبي'],
            ['name_en' => 'MOHRE', 'name_ar' => 'وزارة الموارد البشرية والتوطين'],
            ['name_en' => 'Dubai Land Department', 'name_ar' => 'دائرة الأراضي والأملاك بدبي'],
            ['name_en' => 'Dubai Economy (DET)', 'name_ar' => 'اقتصادية دبي'],
            ['name_en' => 'Federal Tax Authority', 'name_ar' => 'الهيئة الاتحادية للضرائب'],
            ['name_en' => 'Ministry of Education', 'name_ar' => 'وزارة التربية والتعليم'],
            ['name_en' => 'KHDA', 'name_ar' => 'هيئة المعرفة والتنمية البشرية'],
            ['name_en' => 'DHA', 'name_ar' => 'هيئة الصحة بدبي'],
            ['name_en' => 'Department of Health – Abu Dhabi', 'name_ar' => 'دائرة الصحة – أبوظبي'],
            ['name_en' => 'Dubai Police', 'name_ar' => 'شرطة دبي'],
            ['name_en' => 'Dubai Customs', 'name_ar' => 'جمارك دبي'],
            ['name_en' => 'Free Zone Authority', 'name_ar' => 'سلطة المنطقة الحرة'],
            ['name_en' => 'Embassy / Consulate', 'name_ar' => 'سفارة / قنصلية'],
            ['name_en' => 'University', 'name_ar' => 'جامعة'],
            ['name_en' => 'Bank', 'name_ar' => 'بنك'],
            ['name_en' => 'Insurance Company', 'name_ar' => 'شركة تأمين'],
            ['name_en' => 'Notary Public', 'name_ar' => 'كاتب العدل'],
            ['name_en' => 'Other', 'name_ar' => 'أخرى'],
        ];

        foreach ($authorities as $sort => $data) {
            $authority = Authority::whereTranslation('name', $data['name_en'], 'en')->first()
                ?? Authority::query()->where('sort_order', $sort)->first()
                ?? new Authority;

            $authority->fill([
                'sort_order' => $sort,
                'is_active' => true,
            ]);
            $authority->save();

            syncModelTranslations($authority, [
                'en' => ['name' => $data['name_en']],
                'ar' => ['name' => $data['name_ar']],
            ]);
        }

        CatalogCache::flushAuthorities();
    }
}
