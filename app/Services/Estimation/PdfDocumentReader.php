<?php

namespace App\Services\Estimation;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class PdfDocumentReader
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
        $pages = 0;
        $text = '';

        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($path);
            $pdfPages = $pdf->getPages();
            $pages = max(1, count($pdfPages));

            $chunks = [];
            foreach ($pdfPages as $page) {
                try {
                    $chunks[] = (string) $page->getText();
                } catch (Throwable) {
                    // Continue with other pages.
                }
            }

            $text = trim(implode("\n", $chunks));
        } catch (Throwable $e) {
            Log::warning('PDF native parse failed', ['error' => $e->getMessage()]);
            $warnings[] = 'pdf_native_parse_failed';
            $pages = max(1, $pages);
        }

        $words = $this->wordCounter->count($text);

        if ($words > 0) {
            return [
                'pages' => max(1, $pages),
                'words' => $words,
                'text' => $text,
                'method' => 'native',
                'warnings' => $warnings,
            ];
        }

        $ocrText = $this->ocrPdfPages($path, max(1, $pages), $ocrLanguages, $warnings);
        $ocrWords = $this->wordCounter->count((string) $ocrText);

        if ($ocrWords > 0) {
            return [
                'pages' => max(1, $pages),
                'words' => $ocrWords,
                'text' => (string) $ocrText,
                'method' => 'ocr',
                'warnings' => $warnings,
            ];
        }

        $pages = max(1, $pages);
        $fallbackWords = $pages * (int) config('estimation.words_per_standard_page', 250);
        $warnings[] = 'pdf_fallback_standard_page_words';

        return [
            'pages' => $pages,
            'words' => $fallbackWords,
            'text' => '',
            'method' => 'fallback',
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  list<string>  $ocrLanguages
     * @param  list<string>  $warnings
     */
    protected function ocrPdfPages(string $path, int $pageCount, array $ocrLanguages, array &$warnings): ?string
    {
        if (! $this->ocr->isAvailable()) {
            $warnings[] = 'ocr_unavailable';

            return null;
        }

        if (! extension_loaded('imagick')) {
            $warnings[] = 'imagick_unavailable_for_pdf_ocr';

            return null;
        }

        $chunks = [];
        $tempDir = dirname($path).DIRECTORY_SEPARATOR.'pdf_ocr_'.uniqid('', true);
        if (! mkdir($tempDir, 0700, true) && ! is_dir($tempDir)) {
            $warnings[] = 'pdf_ocr_temp_failed';

            return null;
        }

        try {
            $maxPages = min($pageCount, 50);

            for ($i = 0; $i < $maxPages; $i++) {
                try {
                    $imagick = new \Imagick;
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($path.'['.$i.']');
                    $imagick->setImageFormat('png');
                    $imagePath = $tempDir.DIRECTORY_SEPARATOR.'page_'.$i.'.png';
                    $imagick->writeImage($imagePath);
                    $imagick->clear();
                    $imagick->destroy();

                    $estimate = $this->ocr->recognizeWordEstimate($imagePath, $ocrLanguages, $this->wordCounter);
                    if (is_array($estimate) && $estimate['words'] > 0) {
                        $chunks[] = $estimate['text'];
                    }
                } catch (Throwable $e) {
                    Log::warning('PDF page OCR failed', [
                        'page_index' => $i,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            foreach (glob($tempDir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($tempDir);
        }

        if ($chunks === []) {
            $warnings[] = 'pdf_ocr_no_text';

            return null;
        }

        return implode("\n", $chunks);
    }
}
