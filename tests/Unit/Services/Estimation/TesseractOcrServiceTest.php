<?php

namespace Tests\Unit\Services\Estimation;

use App\Services\Estimation\TesseractOcrService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TesseractOcrServiceTest extends TestCase
{
    #[Test]
    public function it_reports_unavailable_when_binary_cannot_be_resolved(): void
    {
        config([
            'estimation.ocr.enabled' => true,
            'estimation.tesseract.binary' => 'D:\\definitely-missing-tesseract-binary.exe',
        ]);

        $service = new TesseractOcrService;

        $this->assertFalse($service->isAvailable());
        $this->assertNull($service->resolveBinary());
    }

    #[Test]
    public function it_reports_unavailable_when_ocr_disabled(): void
    {
        config(['estimation.ocr.enabled' => false]);

        $this->assertFalse((new TesseractOcrService)->isAvailable());
    }

    #[Test]
    public function it_keeps_only_confident_countable_tsv_words(): void
    {
        $tsv = implode("\n", [
            "level\tpage_num\tblock_num\tpar_num\tline_num\tword_num\tleft\ttop\twidth\theight\tconf\ttext",
            "5\t1\t1\t1\t1\t1\t0\t0\t10\t10\t92.5\tBirth",
            "5\t1\t1\t1\t1\t2\t0\t0\t10\t10\t88.0\tCertificate",
            "5\t1\t1\t1\t1\t3\t0\t0\t10\t10\t12.0\t......",
            "5\t1\t1\t1\t1\t4\t0\t0\t10\t10\t70.0\t----",
            "5\t1\t1\t1\t1\t5\t0\t0\t10\t10\t40.0\tNoise",
            "5\t1\t1\t1\t1\t6\t0\t0\t10\t10\t91.0\tJohn",
        ]);

        $text = (new TesseractOcrService)->textFromConfidentTsv($tsv, 60);

        $this->assertSame('Birth Certificate John', $text);
    }
}
