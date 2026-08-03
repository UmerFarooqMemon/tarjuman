<?php

namespace App\Services\Estimation;

/**
 * Maps application language codes (languages.code) to Tesseract traineddata names.
 *
 * OCR should use the document's source language. Translation target language
 * is unrelated to OCR and is not applied here.
 *
 * Bilingual certificates (EN+AR, EN+UR, …) also pull companion packs so mixed
 * text is counted instead of only one language side.
 */
class TesseractLanguageMapper
{
    /**
     * @param  list<string>  $languageCodes  e.g. ['en', 'ar']
     * @return list<string>                  e.g. ['eng', 'ara']
     */
    public function map(array $languageCodes): array
    {
        $map = config('estimation.tesseract.language_map', []);
        $resolved = [];

        foreach ($languageCodes as $code) {
            $code = strtolower(trim((string) $code));
            if ($code === '') {
                continue;
            }

            $tesseract = $map[$code] ?? null;

            // Already a 3+ letter Tesseract-style code.
            if ($tesseract === null && strlen($code) >= 3) {
                $tesseract = $code;
            }

            if (is_string($tesseract) && $tesseract !== '') {
                $resolved[] = $tesseract;
            }
        }

        return array_values(array_unique($resolved));
    }

    /**
     * Source language pack plus bilingual companions (e.g. eng+ara, eng+urd).
     *
     * @return list<string>
     */
    public function forSource(?string $sourceLanguageCode): array
    {
        $primary = $this->map(array_filter([(string) $sourceLanguageCode]));

        if ($primary === []) {
            $fallback = trim((string) config('estimation.tesseract.fallback_language', ''));
            $primary = $fallback !== '' ? [$fallback] : [];
        }

        if ($primary === []) {
            return [];
        }

        return $this->withBilingualPacks($primary);
    }

    /**
     * Expand primary OCR packs with always-include + companion languages.
     *
     * @param  list<string>  $primary
     * @return list<string>
     */
    public function withBilingualPacks(array $primary): array
    {
        $packs = $primary;

        foreach (config('estimation.tesseract.always_include_languages', ['eng']) as $lang) {
            $lang = trim((string) $lang);
            if ($lang !== '') {
                $packs[] = $lang;
            }
        }

        $companions = config('estimation.tesseract.companion_languages', []);

        foreach ($primary as $pack) {
            foreach ($companions[$pack] ?? [] as $companion) {
                $companion = trim((string) $companion);
                if ($companion !== '') {
                    $packs[] = $companion;
                }
            }
        }

        return array_values(array_unique($packs));
    }
}
