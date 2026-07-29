<?php

namespace Tests\Unit\Services\Pricing;

use App\Models\Language;
use App\Models\Vendor;
use App\Models\VendorLanguagePair;
use App\Models\VendorPricingRule;
use App\Services\Pricing\PricingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PricingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_charges_per_word_when_page_count_is_within_short_range(): void
    {
        [$vendor, $pair] = $this->seedVendorPairWithRules();

        $quote = app(PricingCalculator::class)->quote(
            vendor: $vendor,
            languagePair: $pair,
            pageCount: 1,
            wordCount: 250,
        );

        $this->assertSame(VendorPricingRule::BILLING_UNIT_WORD, $quote->billingUnit);
        $this->assertSame(250, $quote->quantity);
        $this->assertSame('125.0000', $quote->totalAmount);
    }

    public function test_charges_per_page_when_page_count_exceeds_short_range(): void
    {
        [$vendor, $pair] = $this->seedVendorPairWithRules();

        $quote = app(PricingCalculator::class)->quote(
            vendor: $vendor,
            languagePair: $pair,
            pageCount: 3,
            wordCount: 900,
        );

        $this->assertSame(VendorPricingRule::BILLING_UNIT_PAGE, $quote->billingUnit);
        $this->assertSame(3, $quote->quantity);
        $this->assertSame('150.0000', $quote->totalAmount);
    }

    public function test_throws_when_no_rule_matches(): void
    {
        [$vendor, $pair] = $this->seedVendorPairWithRules(includeLongRule: false);

        $this->expectException(InvalidArgumentException::class);

        app(PricingCalculator::class)->quote(
            vendor: $vendor,
            languagePair: $pair,
            pageCount: 5,
            wordCount: 100,
        );
    }

    /**
     * @return array{0: Vendor, 1: VendorLanguagePair}
     */
    protected function seedVendorPairWithRules(bool $includeLongRule = true): array
    {
        $source = Language::create([
            'code' => 'en',
            'native_name' => 'English',
            'direction' => 'ltr',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $source->translateOrNew('en')->name = 'English';
        $source->translateOrNew('ar')->name = 'الإنجليزية';
        $source->save();

        $target = Language::create([
            'code' => 'ar',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $target->translateOrNew('en')->name = 'Arabic';
        $target->translateOrNew('ar')->name = 'العربية';
        $target->save();

        $vendor = Vendor::create([
            'slug' => 'test-vendor',
            'trn' => '100000000000003',
            'trade_license_no' => 'TL-1',
            'moj_registration_no' => 'MOJ-1',
            'email' => 'vendor@example.com',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $vendor->translateOrNew('en')->fill([
            'legal_name' => 'Test Vendor LLC',
            'business_name' => 'Test Vendor',
        ]);
        $vendor->translateOrNew('ar')->fill([
            'legal_name' => 'شركة اختبار',
            'business_name' => 'اختبار',
        ]);
        $vendor->save();

        $pair = VendorLanguagePair::create([
            'vendor_id' => $vendor->id,
            'source_language_id' => $source->id,
            'target_language_id' => $target->id,
            'is_active' => true,
        ]);

        VendorPricingRule::create([
            'vendor_id' => $vendor->id,
            'vendor_language_pair_id' => $pair->id,
            'name' => 'Short docs',
            'min_pages' => null,
            'max_pages' => 1,
            'billing_unit' => VendorPricingRule::BILLING_UNIT_WORD,
            'rate_amount' => 0.5,
            'currency' => 'AED',
            'priority' => 10,
            'is_active' => true,
        ]);

        if ($includeLongRule) {
            VendorPricingRule::create([
                'vendor_id' => $vendor->id,
                'vendor_language_pair_id' => $pair->id,
                'name' => 'Long docs',
                'min_pages' => 2,
                'max_pages' => null,
                'billing_unit' => VendorPricingRule::BILLING_UNIT_PAGE,
                'rate_amount' => 50,
                'currency' => 'AED',
                'priority' => 10,
                'is_active' => true,
            ]);
        }

        return [$vendor, $pair];
    }
}
