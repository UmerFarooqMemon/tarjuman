<?php

namespace App\Services\Estimation;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class TesseractOcrService
{
    public function isAvailable(): bool
    {
        if (! config('estimation.ocr.enabled', true)) {
            return false;
        }

        $binary = $this->resolveBinary();

        if ($binary === null) {
            return false;
        }

        try {
            $process = new Process([$binary, '--version']);
            $process->setTimeout(10);
            $process->run();

            if (! $process->isSuccessful()) {
                return false;
            }

            $output = strtolower($process->getOutput().$process->getErrorOutput());

            return str_contains($output, 'tesseract');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Extract text from an image path. Returns null on failure (never throws).
     *
     * Uses a single Tesseract pack per call. For bilingual estimates prefer
     * {@see recognizeWordEstimate()}.
     *
     * @param  list<string>  $tesseractLanguages  e.g. ['eng'] — only the first resolved pack is used
     */
    public function recognize(string $imagePath, array $tesseractLanguages = []): ?string
    {
        $langs = $this->resolveLanguages($tesseractLanguages);

        return $this->recognizeWithPacks($imagePath, $langs === [] ? [] : [$langs[0]]);
    }

    /**
     * OCR bilingual/multi-script documents without double-counting.
     *
     * Runs one pack per writing script, then counts only tokens of that script
     * (Latin from eng, Arabic from ara/urd, …) and sums the results.
     *
     * @param  list<string>  $tesseractLanguages
     * @return array{text: string, words: int}|null
     */
    public function recognizeWordEstimate(string $imagePath, array $tesseractLanguages, WordCounter $wordCounter): ?array
    {
        if (! config('estimation.ocr.enabled', true) || ! is_file($imagePath)) {
            return null;
        }

        $langs = $this->resolveLanguages($tesseractLanguages);

        if ($langs === []) {
            return null;
        }

        $byScript = $this->groupLanguagesByScript($langs);
        $baseConfidence = (float) config('estimation.tesseract.min_confidence', 60);

        // Single script: one OCR pass, full whitespace word count.
        if (count($byScript) === 1) {
            $text = $this->recognizeWithPacks($imagePath, [reset($byScript)], $baseConfidence);

            if ($text === null) {
                return null;
            }

            return [
                'text' => $text,
                'words' => $wordCounter->count($text),
            ];
        }

        $chunks = [];
        $words = 0;
        $includeDigits = true;
        $anySuccess = false;
        $primaryScript = array_key_first($byScript);

        foreach ($byScript as $script => $pack) {
            // Secondary scripts (companions) need higher confidence to avoid
            // counting logos / dotted lines as Arabic/Urdu "words".
            $minConfidence = $script === $primaryScript
                ? $baseConfidence
                : $baseConfidence + 15;

            $text = $this->recognizeWithPacks($imagePath, [$pack], $minConfidence);

            if ($text === null) {
                continue;
            }

            $anySuccess = true;
            $scriptWords = $wordCounter->countScript($text, $script, $includeDigits);
            $includeDigits = false;

            if ($scriptWords > 0) {
                $words += $scriptWords;
                $chunks[] = $text;
            }
        }

        if (! $anySuccess) {
            return null;
        }

        return [
            'text' => implode("\n", $chunks),
            'words' => $words,
        ];
    }

    /**
     * @param  list<string>  $packs  Already-resolved Tesseract packs (usually one)
     */
    protected function recognizeWithPacks(string $imagePath, array $packs, ?float $minConfidence = null): ?string
    {
        if (! config('estimation.ocr.enabled', true)) {
            return null;
        }

        if (! is_file($imagePath)) {
            return null;
        }

        $binary = $this->resolveBinary();

        if ($binary === null) {
            Log::warning('Tesseract OCR failed', [
                'error' => 'Tesseract binary not found',
                'languages' => $packs,
            ]);

            return null;
        }

        $preparedPath = $this->prepareImageForOcr($imagePath);
        $outputBase = tempnam(sys_get_temp_dir(), 'ocr');
        $minConfidence ??= (float) config('estimation.tesseract.min_confidence', 60);

        if ($outputBase === false) {
            $this->cleanupPreparedImage($preparedPath, $imagePath);

            return null;
        }

        // tempnam creates an empty file; Tesseract writes to {$outputBase}.tsv
        @unlink($outputBase);

        $command = [$binary, $preparedPath, $outputBase];

        if ($packs !== []) {
            $command[] = '-l';
            $command[] = implode('+', $packs);
        }

        // Assume a single uniform block of text — better for scanned/WhatsApp docs.
        $command[] = '--psm';
        $command[] = (string) config('estimation.tesseract.psm', 6);

        $dpi = (int) config('estimation.tesseract.dpi', 300);
        if ($dpi > 0) {
            $command[] = '--dpi';
            $command[] = (string) $dpi;
        }

        // TSV includes per-word confidence so we can drop line/logo artifacts.
        $command[] = 'tsv';

        $timeout = (int) config('estimation.tesseract.timeout', 60);
        $tsvFile = $outputBase.'.tsv';

        try {
            $process = new Process($command);
            $process->setTimeout($timeout > 0 ? $timeout : null);
            $process->run();

            if (! is_file($tsvFile)) {
                Log::warning('Tesseract OCR failed', [
                    'error' => trim($process->getErrorOutput()) ?: 'No OCR TSV output produced',
                    'exit_code' => $process->getExitCode(),
                    'languages' => $packs,
                ]);

                return null;
            }

            $tsv = file_get_contents($tsvFile);

            if (! is_string($tsv)) {
                return null;
            }

            $text = $this->textFromConfidentTsv($tsv, $minConfidence);

            if ($text === '' && ! $process->isSuccessful()) {
                Log::warning('Tesseract OCR failed', [
                    'error' => trim($process->getErrorOutput()) ?: 'OCR produced empty output',
                    'exit_code' => $process->getExitCode(),
                    'languages' => $packs,
                ]);

                return null;
            }

            return $text;
        } catch (Throwable $e) {
            Log::warning('Tesseract OCR failed', [
                'error' => $e->getMessage(),
                'languages' => $packs,
                // Do not log image path contents or OCR text (ISO 17100 confidentiality).
            ]);

            return null;
        } finally {
            if (is_file($tsvFile)) {
                @unlink($tsvFile);
            }
            if (is_file($outputBase)) {
                @unlink($outputBase);
            }
            $this->cleanupPreparedImage($preparedPath, $imagePath);
        }
    }

    /**
     * Keep only high-confidence word tokens from Tesseract TSV output.
     */
    public function textFromConfidentTsv(string $tsv, float $minConfidence = 60.0): string
    {
        $lines = preg_split('/\R/', $tsv) ?: [];
        $words = [];
        $wordCounter = new WordCounter;

        foreach ($lines as $index => $line) {
            if ($index === 0 || trim($line) === '') {
                continue;
            }

            $cols = str_getcsv($line, "\t");

            // level page block par line word left top width height conf text
            if (count($cols) < 12) {
                continue;
            }

            $level = (int) $cols[0];
            if ($level !== 5) {
                continue;
            }

            $confidence = (float) $cols[10];
            if ($confidence < $minConfidence) {
                continue;
            }

            $token = trim((string) $cols[11]);
            if (! $wordCounter->isCountableToken($token)) {
                continue;
            }

            $words[] = $token;
        }

        return implode(' ', $words);
    }

    /**
     * Light grayscale normalize to reduce logo/line noise before OCR.
     */
    protected function prepareImageForOcr(string $imagePath): string
    {
        if (! extension_loaded('gd')) {
            return $imagePath;
        }

        $info = @getimagesize($imagePath);
        if ($info === false) {
            return $imagePath;
        }

        $image = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => @imagecreatefrompng($imagePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($imagePath) : false,
            default => false,
        };

        if ($image === false) {
            return $imagePath;
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagefilter($image, IMG_FILTER_CONTRAST, -15);

        $temp = tempnam(sys_get_temp_dir(), 'ocrimg');
        if ($temp === false) {
            imagedestroy($image);

            return $imagePath;
        }

        $pngPath = $temp.'.png';
        @unlink($temp);

        if (! @imagepng($image, $pngPath, 6)) {
            imagedestroy($image);
            @unlink($pngPath);

            return $imagePath;
        }

        imagedestroy($image);

        return $pngPath;
    }

    protected function cleanupPreparedImage(string $preparedPath, string $originalPath): void
    {
        if ($preparedPath !== $originalPath && is_file($preparedPath)) {
            @unlink($preparedPath);
        }
    }

    /**
     * One pack per writing script (first wins — source language stays preferred).
     *
     * @param  list<string>  $packs
     * @return array<string, string> script => pack
     */
    public function groupLanguagesByScript(array $packs): array
    {
        $grouped = [];

        foreach ($packs as $pack) {
            $script = $this->scriptForPack($pack);
            if (! isset($grouped[$script])) {
                $grouped[$script] = $pack;
            }
        }

        return $grouped;
    }

    public function scriptForPack(string $pack): string
    {
        $pack = strtolower(trim($pack));

        return match (true) {
            in_array($pack, ['ara', 'urd', 'fas', 'uig', 'snd', 'pus', 'kur', 'div'], true) => 'arabic',
            in_array($pack, ['heb', 'yid'], true) => 'hebrew',
            in_array($pack, ['chi_sim', 'chi_tra', 'jpn', 'kor'], true) => 'cjk',
            default => 'latin',
        };
    }

    /**
     * Keep only installed Tesseract packs; fall back to configured default when needed.
     *
     * @param  list<string>  $requested
     * @return list<string>
     */
    public function resolveLanguages(array $requested): array
    {
        $requested = array_values(array_unique(array_filter(array_map(
            static fn ($lang) => trim((string) $lang),
            $requested
        ))));

        $installedList = $this->availableLanguages();

        // If packs cannot be probed (OCR off / binary missing), keep the requested set.
        if ($installedList === []) {
            if ($requested !== []) {
                return $requested;
            }

            $fallback = trim((string) config('estimation.tesseract.fallback_language', 'eng'));

            return $fallback !== '' ? [$fallback] : [];
        }

        $installed = array_fill_keys($installedList, true);

        $langs = [];
        foreach ($requested as $lang) {
            if (isset($installed[$lang])) {
                $langs[] = $lang;
            }
        }

        if ($langs !== []) {
            return $langs;
        }

        $fallback = trim((string) config('estimation.tesseract.fallback_language', 'eng'));

        if ($fallback !== '' && isset($installed[$fallback])) {
            return [$fallback];
        }

        return [];
    }

    /**
     * Installed traineddata names (excludes osd / script packs).
     *
     * @return list<string>
     */
    public function availableLanguages(): array
    {
        $binary = $this->resolveBinary();

        if ($binary === null) {
            return [];
        }

        try {
            $process = new Process([$binary, '--list-langs']);
            $process->setTimeout(15);
            $process->run();

            if (! $process->isSuccessful()) {
                return [];
            }

            $lines = preg_split('/\R/', $process->getOutput().$process->getErrorOutput()) ?: [];
            $langs = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_contains(strtolower($line), 'list of available')) {
                    continue;
                }
                // Skip orientation/script detectors (osd, script/Arabic, …).
                if ($line === 'osd' || str_contains($line, '\\') || str_contains($line, '/')) {
                    continue;
                }
                $langs[] = $line;
            }

            sort($langs);

            return array_values(array_unique($langs));
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Resolve the Tesseract executable: config override, PATH, then common Windows installs.
     */
    public function resolveBinary(): ?string
    {
        $configured = config('estimation.tesseract.binary');

        if (is_string($configured) && $configured !== '') {
            return is_file($configured) || $this->isCallableBinary($configured)
                ? $configured
                : null;
        }

        if ($this->isCallableBinary('tesseract')) {
            return 'tesseract';
        }

        foreach ($this->commonBinaryCandidates() as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function commonBinaryCandidates(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [
                '/usr/bin/tesseract',
                '/usr/local/bin/tesseract',
                '/opt/homebrew/bin/tesseract',
            ];
        }

        $roots = array_filter([
            getenv('ProgramFiles') ?: 'C:\\Program Files',
            getenv('ProgramFiles(x86)') ?: 'C:\\Program Files (x86)',
            getenv('LOCALAPPDATA') ?: null,
        ]);

        $candidates = [];

        foreach ($roots as $root) {
            $candidates[] = rtrim($root, '\\/').'\\Tesseract-OCR\\tesseract.exe';
        }

        return $candidates;
    }

    protected function isCallableBinary(string $binary): bool
    {
        try {
            $process = new Process([$binary, '--version']);
            $process->setTimeout(10);
            $process->run();

            if (! $process->isSuccessful()) {
                return false;
            }

            $output = strtolower($process->getOutput().$process->getErrorOutput());

            return str_contains($output, 'tesseract');
        } catch (Throwable) {
            return false;
        }
    }
}
