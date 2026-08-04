<?php

namespace App\Cms\Schemas;

use App\Cms\Contracts\SectionSchema;

class StatsTrustSchema implements SectionSchema
{
    public function type(): string
    {
        return 'stats_trust';
    }

    public function label(): string
    {
        return __('general.cms_section_stats_trust');
    }

    public function fields(): array
    {
        return [
            [
                'name' => 'heading',
                'type' => 'bilingual_string',
                'label' => __('general.cms_field_heading'),
                'rules' => ['max:255'],
            ],
            [
                'name' => 'stats',
                'type' => 'repeater',
                'label' => __('general.cms_field_stats'),
                'min' => 1,
                'max' => 4,
                'fields' => [
                    [
                        'name' => 'icon',
                        'type' => 'icon',
                        'label' => __('general.cms_field_icon'),
                    ],
                    [
                        'name' => 'value',
                        'type' => 'bilingual_string',
                        'label' => __('general.cms_field_value'),
                        'rules' => ['max:80'],
                    ],
                    [
                        'name' => 'label',
                        'type' => 'bilingual_string',
                        'label' => __('general.cms_field_label'),
                        'rules' => ['max:120'],
                    ],
                ],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'heading' => [
                'en' => 'Trusted by Individuals & Business Worldwide',
                'ar' => 'موثوق به من قبل الأفراد والشركات حول العالم',
            ],
            'stats' => [
                [
                    'icon' => '/images/icons/world.svg',
                    'value' => ['en' => '12,000+', 'ar' => '+12,000'],
                    'label' => ['en' => 'Documents Translated', 'ar' => 'مستند مترجم'],
                ],
                [
                    'icon' => '/images/icons/people.svg',
                    'value' => ['en' => '150+', 'ar' => '+150'],
                    'label' => ['en' => 'Language Pairs', 'ar' => 'زوج لغوي'],
                ],
                [
                    'icon' => '/images/icons/secured-filled.svg',
                    'value' => ['en' => '99%', 'ar' => '99%'],
                    'label' => ['en' => 'Acceptance Rate', 'ar' => 'نسبة القبول'],
                ],
                [
                    'icon' => '/images/icons/clock.svg',
                    'value' => ['en' => '24 Hours', 'ar' => '24 ساعة'],
                    'label' => ['en' => 'Average Delivery', 'ar' => 'متوسط التسليم'],
                ],
            ],
        ];
    }

    public function maxInstancesPerPage(): ?int
    {
        return 1;
    }
}
