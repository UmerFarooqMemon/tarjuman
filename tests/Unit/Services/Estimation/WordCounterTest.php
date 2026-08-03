<?php

namespace Tests\Unit\Services\Estimation;

use App\Services\Estimation\WordCounter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WordCounterTest extends TestCase
{
    #[Test]
    public function it_counts_ascii_words(): void
    {
        $this->assertSame(4, (new WordCounter)->count("Hello world from Tarjuman"));
    }

    #[Test]
    public function it_counts_unicode_words(): void
    {
        $this->assertSame(3, (new WordCounter)->count("مرحبا بالعالم العربي"));
    }

    #[Test]
    public function it_returns_zero_for_empty_text(): void
    {
        $this->assertSame(0, (new WordCounter)->count('   '));
        $this->assertSame(0, (new WordCounter)->count(''));
    }

    #[Test]
    public function it_collapses_whitespace(): void
    {
        $this->assertSame(3, (new WordCounter)->count("one   \n\t two\r\nthree"));
    }

    #[Test]
    public function it_counts_latin_script_words_and_ignores_arabic_noise(): void
    {
        $text = 'Hello world مرحبا بالعالم';

        $this->assertSame(2, (new WordCounter)->countScript($text, 'latin'));
        $this->assertSame(2, (new WordCounter)->countScript($text, 'arabic'));
    }

    #[Test]
    public function it_ignores_dotted_lines_and_punctuation_noise(): void
    {
        $counter = new WordCounter;

        $this->assertFalse($counter->isCountableToken('......'));
        $this->assertFalse($counter->isCountableToken('----'));
        $this->assertFalse($counter->isCountableToken('____'));
        $this->assertFalse($counter->isCountableToken('•'));
        $this->assertTrue($counter->isCountableToken('Certificate'));
        $this->assertSame(
            3,
            $counter->count("Name ...... John ---- Doe ____")
        );
    }
}
