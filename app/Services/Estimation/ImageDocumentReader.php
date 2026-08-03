<?php

namespace App\Services\Estimation;

class ImageDocumentReader
{
    public function __construct(
        protected WordCounter $wordCounter,
        protected TesseractOcrService $ocr,
    ) {}

    /**
     * @param  list<string>  $ocrLanguages  Tesseract packs derived from source language
     * @return array{pages: int, words: int, text: string, method: string, warnings: list<string>}
     */
    public function analyze(string $path, array $ocrLanguages = []): array
    {
        $warnings = [];
        $perPage = max(1, (int) config('estimation.words_per_standard_page', 250));

        if (! $this->ocr->isAvailable()) {
            $warnings[] = 'ocr_unavailable';
            $warnings[] = 'image_fallback_standard_page_words';

            return [
                'pages' => 1,
                'words' => $perPage,
                'text' => '',
                'method' => 'fallback',
                'warnings' => $warnings,
            ];
        }

        $estimate = $this->ocr->recognizeWordEstimate($path, $ocrLanguages, $this->wordCounter);

        if ($estimate === null) {
            $warnings[] = 'image_ocr_failed';
            $warnings[] = 'image_fallback_standard_page_words';

            return [
                'pages' => 1,
                'words' => $perPage,
                'text' => '',
                'method' => 'fallback',
                'warnings' => $warnings,
            ];
        }

        if ($estimate['words'] > 0) {
            return [
                'pages' => 1,
                'words' => $estimate['words'],
                'text' => $estimate['text'],
                'method' => 'ocr',
                'warnings' => $warnings,
            ];
        }

        $warnings[] = 'image_ocr_no_text';
        $warnings[] = 'image_fallback_standard_page_words';

        return [
            'pages' => 1,
            'words' => $perPage,
            'text' => '',
            'method' => 'fallback',
            'warnings' => $warnings,
        ];
    }
}
