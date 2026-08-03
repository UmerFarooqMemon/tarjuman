<?php

namespace App\Services\Estimation;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\Text as TextElement;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Throwable;

class DocxDocumentReader
{
    public function __construct(
        protected WordCounter $wordCounter,
    ) {}

    /**
     * @return array{pages: int, words: int, text: string, method: string, warnings: list<string>}
     */
    public function analyze(string $path): array
    {
        $warnings = [];
        $text = '';

        try {
            $phpWord = IOFactory::load($path);
            $chunks = [];

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $chunks[] = $this->extractElementText($element);
                }
            }

            $text = trim(implode("\n", array_filter($chunks)));
        } catch (Throwable $e) {
            Log::warning('DOCX native parse failed', ['error' => $e->getMessage()]);
            $warnings[] = 'docx_native_parse_failed';
        }

        $words = $this->wordCounter->count($text);
        $perPage = max(1, (int) config('estimation.words_per_standard_page', 250));

        if ($words > 0) {
            return [
                'pages' => max(1, (int) ceil($words / $perPage)),
                'words' => $words,
                'text' => $text,
                'method' => 'native',
                'warnings' => $warnings,
            ];
        }

        $warnings[] = 'docx_fallback_standard_page_words';

        return [
            'pages' => 1,
            'words' => $perPage,
            'text' => '',
            'method' => 'fallback',
            'warnings' => $warnings,
        ];
    }

    protected function extractElementText(mixed $element): string
    {
        if ($element instanceof TextElement) {
            return (string) $element->getText();
        }

        if ($element instanceof TextRun) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $parts[] = $this->extractElementText($child);
            }

            return implode('', $parts);
        }

        if (method_exists($element, 'getText')) {
            try {
                $value = $element->getText();

                return is_string($value) ? $value : '';
            } catch (Throwable) {
                return '';
            }
        }

        if (method_exists($element, 'getElements')) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $parts[] = $this->extractElementText($child);
            }

            return implode(' ', $parts);
        }

        return '';
    }
}
