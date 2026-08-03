<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class DocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $documentTypes = [
            [
                'name_en' => 'Birth Certificate',
                'name_ar' => 'شهادة ميلاد',
            ],
            [
                'name_en' => 'Marriage Certificate',
                'name_ar' => 'شهادة زواج',
            ],
            [
                'name_en' => 'Degree / Diploma',
                'name_ar' => 'شهادة جامعية',
            ],
            [
                'name_en' => 'Passport',
                'name_ar' => 'جواز سفر',
            ],
            [
                'name_en' => 'Trade License',
                'name_ar' => 'رخصة تجارية',
            ],
            [
                'name_en' => 'General Document',
                'name_ar' => 'مستند عام',
            ],
        ];

        foreach ($documentTypes as $sort => $data) {
            $type = DocumentType::whereTranslation('name', $data['name_en'], 'en')->first()
                ?? DocumentType::query()->where('sort_order', $sort)->first()
                ?? new DocumentType;

            $type->fill([
                'sort_order' => $sort,
                'is_active' => true,
            ]);
            $type->save();

            syncModelTranslations($type, [
                'en' => ['name' => $data['name_en']],
                'ar' => ['name' => $data['name_ar']],
            ]);
        }

        CatalogCache::flushDocumentTypes();
    }
}
