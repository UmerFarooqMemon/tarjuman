<?php

namespace App\Cms\Schemas;

use App\Cms\Contracts\SectionSchema;

class WhyChooseUsSchema implements SectionSchema
{
    public function type(): string
    {
        return 'why_choose_us';
    }

    public function label(): string
    {
        return __('general.cms_section_why_choose_us');
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
                'type' => 'group',
                'label' => __('general.cms_field_title'),
                'fields' => [
                    [
                        'name' => 'faint',
                        'type' => 'bilingual_string',
                        'label' => __('general.cms_field_title_faint'),
                        'rules' => ['max:80'],
                    ],
                    [
                        'name' => 'emphasis',
                        'type' => 'bilingual_string',
                        'label' => __('general.cms_field_title_emphasis'),
                        'rules' => ['max:120'],
                    ],
                ],
            ],
            [
                'name' => 'description',
                'type' => 'bilingual_text',
                'label' => __('general.cms_field_description'),
                'rules' => ['max:1000'],
            ],
            [
                'name' => 'features',
                'type' => 'repeater',
                'label' => __('general.cms_field_features'),
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
                        'name' => 'subtitle',
                        'type' => 'bilingual_string',
                        'label' => __('general.cms_field_subtitle'),
                        'rules' => ['max:255'],
                    ],
                ],
            ],
            [
                'name' => 'side_image',
                'type' => 'image',
                'label' => __('general.cms_field_side_image'),
            ],
            [
                'name' => 'guarantee',
                'type' => 'group',
                'label' => __('general.cms_field_guarantee'),
                'fields' => [
                    [
                        'name' => 'icon',
                        'type' => 'icon',
                        'label' => __('general.cms_field_icon'),
                    ],
                    [
                        'name' => 'title_lines',
                        'type' => 'repeater',
                        'label' => __('general.cms_field_title_lines'),
                        'min' => 1,
                        'max' => 3,
                        'fields' => [
                            [
                                'name' => 'text',
                                'type' => 'bilingual_string',
                                'label' => __('general.cms_field_line'),
                                'rules' => ['max:80'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'body',
                        'type' => 'bilingual_text',
                        'label' => __('general.cms_field_body'),
                        'rules' => ['max:1000'],
                    ],
                ],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'eyebrow' => [
                'en' => 'Why Tarjuman',
                'ar' => 'لماذا ترجمان',
            ],
            'title' => [
                'faint' => ['en' => 'Why', 'ar' => 'لماذا'],
                'emphasis' => ['en' => 'Choose Us', 'ar' => 'تختارنا'],
            ],
            'description' => [
                'en' => 'We streamline the certified translation process, offering speed without compromising accuracy or official acceptance.',
                'ar' => 'نبسّط عملية الترجمة المعتمدة، ونوفّر السرعة دون المساس بالدقة أو القبول الرسمي.',
            ],
            'features' => [
                [
                    'icon' => '/images/icons/flash.svg',
                    'title' => ['en' => 'Fast & Reliable', 'ar' => 'سريع وموثوق'],
                    'subtitle' => ['en' => 'Instant pricing and quick turnaround', 'ar' => 'تسعير فوري وتسليم سريع'],
                ],
                [
                    'icon' => '/images/icons/certified.svg',
                    'title' => ['en' => 'Certified & Trusted', 'ar' => 'معتمد وموثوق'],
                    'subtitle' => ['en' => 'All translators are certified and professionally vetted', 'ar' => 'جميع المترجمين معتمدون وخاضعون للتدقيق المهني'],
                ],
                [
                    'icon' => '/images/icons/secure.svg',
                    'title' => ['en' => 'Secure & Private', 'ar' => 'آمن وخاص'],
                    'subtitle' => ['en' => 'Your documents are 100% secure and confidential', 'ar' => 'مستنداتك آمنة وسرية بنسبة 100%'],
                ],
                [
                    'icon' => '/images/icons/web-check.svg',
                    'title' => ['en' => 'Government Accepted', 'ar' => 'مقبول حكومياً'],
                    'subtitle' => ['en' => 'Accepted by government entities worldwide', 'ar' => 'مقبول لدى الجهات الحكومية حول العالم'],
                ],
            ],
            'side_image' => '/images/why-choose-texture.jpg',
            'guarantee' => [
                'icon' => '/images/icons/guarantee-shield.svg',
                'title_lines' => [
                    ['text' => ['en' => '100% Accepted', 'ar' => 'مقبول 100%']],
                    ['text' => ['en' => 'Guarantee', 'ar' => 'ضمان']],
                ],
                'body' => [
                    'en' => 'Our translations are certified and guaranteed to be accepted by USCIS and major global authorities.',
                    'ar' => 'ترجماتنا معتمدة ومضمونة القبول لدى USCIS والجهات العالمية الرئيسية.',
                ],
            ],
        ];
    }

    public function maxInstancesPerPage(): ?int
    {
        return 1;
    }
}
