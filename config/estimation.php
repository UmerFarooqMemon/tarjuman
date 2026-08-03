<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Estimation / Document Analysis
    |--------------------------------------------------------------------------
    |
    | Volume counting uses source word count (industry practice for translation
    | service providers). A standard page is defined only for page-derived
    | add-ons and last-resort fallbacks — not as an ISO 17100 mandate.
    |
    | ISO 17100 orientation here focuses on secure handling of client documents:
    | private temp storage, wipe after processing, no content logging, API auth.
    |
    */

    'words_per_standard_page' => (int) env('ESTIMATION_WORDS_PER_PAGE', 250),

    'max_files' => (int) env('ESTIMATION_MAX_FILES', 10),

    'max_file_kb' => (int) env('ESTIMATION_MAX_FILE_KB', 10240),

    'allowed_mimes' => ['pdf', 'docx', 'jpg', 'jpeg', 'png'],

    'allowed_extensions' => ['pdf', 'docx', 'jpg', 'jpeg', 'png'],

    'ocr' => [
        'enabled' => (bool) env('ESTIMATION_OCR_ENABLED', true),
    ],

    'tesseract' => [
        'binary' => env('TESSERACT_BINARY'),
        'timeout' => (int) env('TESSERACT_TIMEOUT', 60),
        'psm' => (int) env('TESSERACT_PSM', 6),
        'dpi' => (int) env('TESSERACT_DPI', 300),

        /*
         * Drop OCR word tokens below this confidence (0–100).
         * Filters dotted lines, logos, and decorative artifacts.
         */
        'min_confidence' => (int) env('TESSERACT_MIN_CONFIDENCE', 60),

        /*
         * Used only when the source language code cannot be mapped.
         * Leave empty to skip forcing a Tesseract language pack.
         */
        'fallback_language' => env('TESSERACT_FALLBACK_LANGUAGE', 'eng'),

        /*
         * Official certificates are often bilingual (EN+AR, EN+UR, …).
         * Always include these packs alongside the source pack when installed.
         */
        'always_include_languages' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TESSERACT_ALWAYS_INCLUDE', 'eng'))
        ))),

        /*
         * Extra packs to OCR with a given primary pack (if installed).
         * Example: English source docs often also contain Arabic/Urdu text.
         */
        'companion_languages' => [
            'eng' => ['ara', 'urd'],
            'ara' => ['eng'],
            'urd' => ['eng'],
        ],

        /*
         | Map languages.code (ISO 639-1 app codes) → Tesseract traineddata names.
         | Packs must be installed on the server (full Tesseract language data).
         */
        'language_map' => [
            'af' => 'afr',
            'am' => 'amh',
            'ar' => 'ara',
            'as' => 'asm',
            'az' => 'aze',
            'be' => 'bel',
            'bn' => 'ben',
            'bo' => 'bod',
            'bs' => 'bos',
            'br' => 'bre',
            'bg' => 'bul',
            'ca' => 'cat',
            'ceb' => 'ceb',
            'cs' => 'ces',
            'zh' => 'chi_sim',
            'co' => 'cos',
            'cy' => 'cym',
            'da' => 'dan',
            'de' => 'deu',
            'dv' => 'div',
            'dz' => 'dzo',
            'el' => 'ell',
            'en' => 'eng',
            'eo' => 'epo',
            'et' => 'est',
            'eu' => 'eus',
            'fa' => 'fas',
            'fi' => 'fin',
            'fr' => 'fra',
            'fy' => 'fry',
            'gd' => 'gla',
            'ga' => 'gle',
            'gl' => 'glg',
            'gu' => 'guj',
            'ht' => 'hat',
            'he' => 'heb',
            'hi' => 'hin',
            'hr' => 'hrv',
            'hu' => 'hun',
            'hy' => 'hye',
            'iu' => 'iku',
            'id' => 'ind',
            'is' => 'isl',
            'it' => 'ita',
            'jv' => 'jav',
            'ja' => 'jpn',
            'kn' => 'kan',
            'ka' => 'kat',
            'kk' => 'kaz',
            'km' => 'khm',
            'ky' => 'kir',
            'ko' => 'kor',
            'ku' => 'kur',
            'lo' => 'lao',
            'la' => 'lat',
            'lv' => 'lav',
            'lt' => 'lit',
            'lb' => 'ltz',
            'ml' => 'mal',
            'mr' => 'mar',
            'mk' => 'mkd',
            'mt' => 'mlt',
            'mn' => 'mon',
            'mi' => 'mri',
            'ms' => 'msa',
            'my' => 'mya',
            'ne' => 'nep',
            'nl' => 'nld',
            'no' => 'nor',
            'nb' => 'nor',
            'nn' => 'nor',
            'oc' => 'oci',
            'or' => 'ori',
            'pa' => 'pan',
            'pl' => 'pol',
            'pt' => 'por',
            'ps' => 'pus',
            'ro' => 'ron',
            'ru' => 'rus',
            'sa' => 'san',
            'si' => 'sin',
            'sk' => 'slk',
            'sl' => 'slv',
            'sd' => 'snd',
            'es' => 'spa',
            'sq' => 'sqi',
            'sr' => 'srp',
            'su' => 'sun',
            'sw' => 'swa',
            'sv' => 'swe',
            'syr' => 'syr',
            'ta' => 'tam',
            'tt' => 'tat',
            'te' => 'tel',
            'tg' => 'tgk',
            'tl' => 'tgl',
            'th' => 'tha',
            'ti' => 'tir',
            'to' => 'ton',
            'tr' => 'tur',
            'ug' => 'uig',
            'uk' => 'ukr',
            'ur' => 'urd',
            'uz' => 'uzb',
            'vi' => 'vie',
            'yi' => 'yid',
            'yo' => 'yor',
        ],
    ],

];
