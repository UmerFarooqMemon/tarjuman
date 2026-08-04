<?php

namespace App\Cms\Schemas;

use App\Cms\Contracts\SectionSchema;

class SupportedDocumentsSchema implements SectionSchema
{
    public function type(): string
    {
        return 'supported_documents';
    }

    public function label(): string
    {
        return __('general.cms_section_supported_documents');
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'eyebrow',
                'type' => 'bilingual_string',
                'label' => __('general.cms_field_eyebrow'),
                'rules' => ['max:120'],
            ],
            [
                'name' => 'title',
                'type' => 'bilingual_string',
                'label' => __('general.cms_field_title'),
                'rules' => ['max:190'],
            ],
            [
                'name' => 'description',
                'type' => 'bilingual_text',
                'label' => __('general.cms_field_description'),
                'rules' => ['max:1000'],
            ],
            [
                'name' => 'categories',
                'type' => 'repeater',
                'label' => __('general.cms_field_categories'),
                'min' => 1,
                'max' => 4,
                'fields' => [
                    [
                        'name' => 'icon',
                        'type' => 'icon',
                        'label' => __('general.cms_field_icon'),
                    ],
                    [
                        'name' => 'title',
                        'type' => 'bilingual_string',
                        'label' => __('general.cms_field_title'),
                        'rules' => ['max:120'],
                    ],
                    [
                        'name' => 'items',
                        'type' => 'repeater',
                        'label' => __('general.cms_field_items'),
                        'min' => 0,
                        'max' => 8,
                        'fields' => [
                            [
                                'name' => 'label',
                                'type' => 'bilingual_string',
                                'label' => __('general.cms_field_label'),
                                'rules' => ['max:120'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'eyebrow' => [
                'en' => 'Document Types',
                'ar' => 'أنواع المستندات',
            ],
            'title' => [
                'en' => 'Supported Documents',
                'ar' => 'المستندات المدعومة',
            ],
            'description' => [
                'en' => 'We handle a wide variety of official documents.',
                'ar' => 'نتعامل مع مجموعة واسعة من المستندات الرسمية.',
            ],
            'categories' => [
                [
                    'icon' => '/images/icons/person.svg',
                    'title' => ['en' => 'Personal', 'ar' => 'شخصية'],
                    'items' => [
                        ['label' => ['en' => 'Passport', 'ar' => 'جواز سفر']],
                        ['label' => ['en' => 'Birth Certificate', 'ar' => 'شهادة ميلاد']],
                        ['label' => ['en' => 'Marriage Certificate', 'ar' => 'شهادة زواج']],
                    ],
                ],
                [
                    'icon' => '/images/icons/education.svg',
                    'title' => ['en' => 'Education', 'ar' => 'تعليم'],
                    'items' => [
                        ['label' => ['en' => 'Degree', 'ar' => 'شهادة جامعية']],
                        ['label' => ['en' => 'Transcript', 'ar' => 'كشف درجات']],
                    ],
                ],
                [
                    'icon' => '/images/icons/legal.svg',
                    'title' => ['en' => 'Legal', 'ar' => 'قانونية'],
                    'items' => [
                        ['label' => ['en' => 'Legal Contract', 'ar' => 'عقد قانوني']],
                        ['label' => ['en' => 'Visa Documents', 'ar' => 'مستندات التأشيرة']],
                    ],
                ],
                [
                    'icon' => '/images/icons/medical.svg',
                    'title' => ['en' => 'Medical', 'ar' => 'طبية'],
                    'items' => [
                        ['label' => ['en' => 'Medical Report', 'ar' => 'تقرير طبي']],
                    ],
                ],
            ],
        ];
    }

    public function maxInstancesPerPage(): ?int
    {
        return 1;
    }
}
