<?php

namespace Tests\Unit\Services\Estimation;

use App\Services\Estimation\ImageDocumentReader;
use App\Services\Estimation\TesseractOcrService;
use App\Services\Estimation\WordCounter;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageDocumentReaderTest extends TestCase
{
    #[Test]
    public function it_uses_ocr_text_when_available(): void
    {
        $ocr = Mockery::mock(TesseractOcrService::class);
        $ocr->shouldReceive('isAvailable')->andReturn(true);
        $ocr->shouldReceive('recognizeWordEstimate')
            ->with(__FILE__, [], Mockery::type(WordCounter::class))
            ->andReturn([
                'text' => 'Hello world translation estimate',
                'words' => 4,
            ]);

        $reader = new ImageDocumentReader(new WordCounter, $ocr);
        $result = $reader->analyze(__FILE__);

        $this->assertSame('ocr', $result['method']);
        $this->assertSame(1, $result['pages']);
        $this->assertSame(4, $result['words']);
        $this->assertSame([], $result['warnings']);
    }

    #[Test]
    public function it_falls_back_when_ocr_unavailable(): void
    {
        config(['estimation.words_per_standard_page' => 250]);

        $ocr = Mockery::mock(TesseractOcrService::class);
        $ocr->shouldReceive('isAvailable')->andReturn(false);
        $ocr->shouldNotReceive('recognizeWordEstimate');

        $reader = new ImageDocumentReader(new WordCounter, $ocr);
        $result = $reader->analyze(__FILE__);

        $this->assertSame('fallback', $result['method']);
        $this->assertSame(1, $result['pages']);
        $this->assertSame(250, $result['words']);
        $this->assertContains('ocr_unavailable', $result['warnings']);
    }

    #[Test]
    public function it_falls_back_when_ocr_returns_empty_text(): void
    {
        config(['estimation.words_per_standard_page' => 250]);

        $ocr = Mockery::mock(TesseractOcrService::class);
        $ocr->shouldReceive('isAvailable')->andReturn(true);
        $ocr->shouldReceive('recognizeWordEstimate')
            ->with(__FILE__, [], Mockery::type(WordCounter::class))
            ->andReturn([
                'text' => '',
                'words' => 0,
            ]);

        $reader = new ImageDocumentReader(new WordCounter, $ocr);
        $result = $reader->analyze(__FILE__);

        $this->assertSame('fallback', $result['method']);
        $this->assertSame(250, $result['words']);
        $this->assertContains('image_ocr_no_text', $result['warnings']);
    }

    #[Test]
    public function it_falls_back_when_ocr_invocation_fails(): void
    {
        config(['estimation.words_per_standard_page' => 250]);

        $ocr = Mockery::mock(TesseractOcrService::class);
        $ocr->shouldReceive('isAvailable')->andReturn(true);
        $ocr->shouldReceive('recognizeWordEstimate')
            ->with(__FILE__, [], Mockery::type(WordCounter::class))
            ->andReturn(null);

        $reader = new ImageDocumentReader(new WordCounter, $ocr);
        $result = $reader->analyze(__FILE__);

        $this->assertSame('fallback', $result['method']);
        $this->assertSame(250, $result['words']);
        $this->assertContains('image_ocr_failed', $result['warnings']);
    }
}
