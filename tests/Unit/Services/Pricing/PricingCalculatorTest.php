<?php

namespace Tests\Unit\Services\Pricing;

use App\Models\PricingRule;
use App\Services\Pricing\PricingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PricingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_charges_per_word_when_page_count_is_within_short_range(): void
    {
        $this->seedPlatformRules();

        $quote = app(PricingCalculator::class)->quote(
            pageCount: 1,
            wordCount: 250,
        );

        $this->assertSame(PricingRule::BILLING_UNIT_WORD, $quote->billingUnit);
        $this->assertSame(250, $quote->quantity);
        $this->assertSame('125.0000', $quote->totalAmount);
    }

    public function test_charges_per_page_when_page_count_exceeds_short_range(): void
    {
        $this->seedPlatformRules();

        $quote = app(PricingCalculator::class)->quote(
            pageCount: 3,
            wordCount: 900,
        );

        $this->assertSame(PricingRule::BILLING_UNIT_PAGE, $quote->billingUnit);
        $this->assertSame(3, $quote->quantity);
        $this->assertSame('150.0000', $quote->totalAmount);
    }

    public function test_throws_when_no_rule_matches(): void
    {
        $this->seedPlatformRules(includeLongRule: false);

        $this->expectException(InvalidArgumentException::class);

        app(PricingCalculator::class)->quote(
            pageCount: 5,
            wordCount: 100,
        );
    }

    protected function seedPlatformRules(bool $includeLongRule = true): void
    {
        PricingRule::create([
            'name' => 'Short docs',
            'min_pages' => null,
            'max_pages' => 1,
            'billing_unit' => PricingRule::BILLING_UNIT_WORD,
            'rate_amount' => 0.5,
            'currency' => 'AED',
            'priority' => 10,
            'is_active' => true,
        ]);

        if ($includeLongRule) {
            PricingRule::create([
                'name' => 'Long docs',
                'min_pages' => 2,
                'max_pages' => null,
                'billing_unit' => PricingRule::BILLING_UNIT_PAGE,
                'rate_amount' => 50,
                'currency' => 'AED',
                'priority' => 10,
                'is_active' => true,
            ]);
        }
    }
}
