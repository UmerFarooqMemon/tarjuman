<?php

namespace Tests\Unit\Services\Estimation;

use App\Services\Estimation\TesseractLanguageMapper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TesseractLanguageMapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'estimation.tesseract.fallback_language' => 'eng',
            'estimation.tesseract.always_include_languages' => ['eng'],
            'estimation.tesseract.companion_languages' => [
                'eng' => ['ara', 'urd'],
                'ara' => ['eng'],
                'urd' => ['eng'],
            ],
        ]);
    }

    #[Test]
    public function it_maps_english_source_with_arabic_and_urdu_companions(): void
    {
        $this->assertSame(
            ['eng', 'ara', 'urd'],
            (new TesseractLanguageMapper)->forSource('en')
        );
    }

    #[Test]
    public function it_maps_arabic_source_with_english_companion(): void
    {
        $this->assertSame(
            ['ara', 'eng'],
            (new TesseractLanguageMapper)->forSource('ar')
        );
    }

    #[Test]
    public function it_maps_urdu_source_with_english_companion(): void
    {
        $this->assertSame(
            ['urd', 'eng'],
            (new TesseractLanguageMapper)->forSource('ur')
        );
    }

    #[Test]
    public function it_still_includes_english_for_other_source_languages(): void
    {
        $this->assertSame(
            ['fra', 'eng'],
            (new TesseractLanguageMapper)->forSource('fr')
        );
    }

    #[Test]
    public function it_uses_fallback_when_code_is_unknown(): void
    {
        $this->assertSame(
            ['eng', 'ara', 'urd'],
            (new TesseractLanguageMapper)->forSource('zz')
        );
    }

    #[Test]
    public function it_maps_additional_common_language_codes(): void
    {
        $mapper = new TesseractLanguageMapper;

        $this->assertSame(['heb', 'eng'], $mapper->forSource('he'));
        $this->assertSame(['chi_sim', 'eng'], $mapper->forSource('zh'));
    }
}
