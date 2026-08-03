<?php

namespace App\Services\Estimation;

readonly class DocumentMetrics
{
    /**
     * @param  list<string>  $warnings
     */
    public function __construct(
        public string $filename,
        public string $extension,
        public int $pages,
        public int $words,
        public string $method,
        public array $warnings = [],
        public bool $usedFallback = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'extension' => $this->extension,
            'pages' => $this->pages,
            'words' => $this->words,
            'method' => $this->method,
            'used_fallback' => $this->usedFallback,
            'warnings' => $this->warnings,
        ];
    }
}
