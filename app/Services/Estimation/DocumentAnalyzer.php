<?php

namespace App\Services\Estimation;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DocumentAnalyzer
{
    public function __construct(
        protected PdfDocumentReader $pdfReader,
        protected DocxDocumentReader $docxReader,
        protected ImageDocumentReader $imageReader,
    ) {}

    /**
     * Analyze uploaded documents securely and wipe temp storage afterwards.
     *
     * @param  list<\Illuminate\Http\UploadedFile>  $files
     * @param  list<string>  $ocrLanguages  Tesseract packs from source language
     * @return array{documents: list<DocumentMetrics>, totals: array{pages: int, words: int}, request_id: string}
     */
    public function analyze(array $files, array $ocrLanguages = []): array
    {
        $requestId = (string) Str::uuid();
        $tempRoot = storage_path('app/tmp/estimates/'.$requestId);

        File::ensureDirectoryExists($tempRoot, 0700);

        $documents = [];
        $totalPages = 0;
        $totalWords = 0;

        try {
            foreach ($files as $index => $file) {
                $metrics = $this->analyzeOne($file, $tempRoot, $index, $ocrLanguages);
                $documents[] = $metrics;
                $totalPages += $metrics->pages;
                $totalWords += $metrics->words;

                Log::info('Estimate document analyzed', [
                    'request_id' => $requestId,
                    'filename' => $metrics->filename,
                    'extension' => $metrics->extension,
                    'pages' => $metrics->pages,
                    'words' => $metrics->words,
                    'method' => $metrics->method,
                    'used_fallback' => $metrics->usedFallback,
                    'warnings' => $metrics->warnings,
                    // Never log extracted/OCR text (ISO 17100 confidentiality).
                ]);
            }
        } finally {
            $this->wipeDirectory($tempRoot);
        }

        return [
            'documents' => $documents,
            'totals' => [
                'pages' => $totalPages,
                'words' => $totalWords,
            ],
            'request_id' => $requestId,
        ];
    }

    /**
     * @param  list<string>  $ocrLanguages
     */
    protected function analyzeOne(UploadedFile $file, string $tempRoot, int $index, array $ocrLanguages): DocumentMetrics
    {
        $originalName = (string) $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $safeName = sprintf('%d_%s.%s', $index, Str::random(16), $extension !== '' ? $extension : 'bin');
        $path = $tempRoot.DIRECTORY_SEPARATOR.$safeName;

        try {
            $file->move($tempRoot, $safeName);

            $result = match ($extension) {
                'pdf' => $this->pdfReader->analyze($path, $ocrLanguages),
                'docx' => $this->docxReader->analyze($path),
                'jpg', 'jpeg', 'png' => $this->imageReader->analyze($path, $ocrLanguages),
                default => $this->lastResort($extension),
            };

            return new DocumentMetrics(
                filename: $originalName,
                extension: $extension,
                pages: max(1, (int) $result['pages']),
                words: max(0, (int) $result['words']),
                method: (string) $result['method'],
                warnings: $result['warnings'],
                usedFallback: ($result['method'] ?? '') === 'fallback',
            );
        } catch (Throwable $e) {
            Log::warning('Document analysis failed; using last-resort estimate', [
                'filename' => $originalName,
                'error' => $e->getMessage(),
            ]);

            $fallback = $this->lastResort($extension);

            return new DocumentMetrics(
                filename: $originalName,
                extension: $extension,
                pages: $fallback['pages'],
                words: $fallback['words'],
                method: 'fallback',
                warnings: array_merge($fallback['warnings'], ['analysis_exception']),
                usedFallback: true,
            );
        }
    }

    /**
     * @return array{pages: int, words: int, text: string, method: string, warnings: list<string>}
     */
    protected function lastResort(string $extension): array
    {
        $perPage = max(1, (int) config('estimation.words_per_standard_page', 250));

        return [
            'pages' => 1,
            'words' => $perPage,
            'text' => '',
            'method' => 'fallback',
            'warnings' => ['last_resort_standard_page_estimate', 'extension:'.$extension],
        ];
    }

    protected function wipeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        try {
            File::deleteDirectory($directory);
        } catch (Throwable $e) {
            Log::warning('Failed to wipe estimate temp directory', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
