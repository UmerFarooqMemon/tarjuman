<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default platform currency
    |--------------------------------------------------------------------------
    |
    | Used as a fallback when site settings / DB catalog are unavailable.
    | Runtime catalog is managed via Admin → Currencies (seeded from gcc below).
    |
    */

    'default' => 'AED',

    /*
    |--------------------------------------------------------------------------
    | Seed / fallback currency catalog
    |--------------------------------------------------------------------------
    |
    | Populated into the currencies table by CurrenciesTableSeeder.
    | Also used as a temporary fallback when the DB catalog is empty.
    |
    | icon: SVG under public/assets/img/currencies/{file}
    | symbol: Preferred Unicode glyph when fonts support it (API / text fallback).
    | symbol_native: Traditional Arabic abbreviation.
    |
    */

    'gcc' => [
        'AED' => [
            'code' => 'AED',
            'name_en' => 'UAE Dirham',
            'name_ar' => 'درهم إماراتي',
            'symbol' => "\u{20C3}", // UAE DIRHAM SIGN (Unicode 18)
            'symbol_native' => 'د.إ',
            'icon' => 'aed.svg',
            'decimals' => 2,
            'country' => 'AE',
        ],
        'SAR' => [
            'code' => 'SAR',
            'name_en' => 'Saudi Riyal',
            'name_ar' => 'ريال سعودي',
            'symbol' => "\u{20C1}", // SAUDI RIYAL SIGN (Unicode 17)
            'symbol_native' => 'ر.س',
            'icon' => 'sar.svg',
            'decimals' => 2,
            'country' => 'SA',
        ],
        'KWD' => [
            'code' => 'KWD',
            'name_en' => 'Kuwaiti Dinar',
            'name_ar' => 'دينار كويتي',
            'symbol' => 'KD',
            'symbol_native' => 'د.ك',
            'icon' => 'kwd.svg',
            'decimals' => 3,
            'country' => 'KW',
        ],
        'BHD' => [
            'code' => 'BHD',
            'name_en' => 'Bahraini Dinar',
            'name_ar' => 'دينار بحريني',
            'symbol' => 'BD',
            'symbol_native' => 'د.ب',
            'icon' => 'bhd.svg',
            'decimals' => 3,
            'country' => 'BH',
        ],
        'OMR' => [
            'code' => 'OMR',
            'name_en' => 'Omani Rial',
            'name_ar' => 'ريال عماني',
            'symbol' => 'OMR',
            'symbol_native' => 'ر.ع.',
            'icon' => 'omr.svg',
            'decimals' => 3,
            'country' => 'OM',
        ],
        'QAR' => [
            'code' => 'QAR',
            'name_en' => 'Qatari Riyal',
            'name_ar' => 'ريال قطري',
            'symbol' => 'QR',
            'symbol_native' => 'ر.ق',
            'icon' => 'qar.svg',
            'decimals' => 2,
            'country' => 'QA',
        ],
    ],

];
