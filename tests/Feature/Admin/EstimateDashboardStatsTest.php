<?php

namespace Tests\Feature\Admin;

use App\Models\Estimate;
use App\Services\Admin\EstimateDashboardStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstimateDashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_builds_kpis_ignoring_superseded_quotes(): void
    {
        $session = '11111111-1111-1111-1111-111111111111';

        Estimate::query()->create($this->estimateAttrs([
            'uuid' => '11111111-1111-1111-1111-111111111101',
            'session_uuid' => $session,
            'status' => Estimate::STATUS_SUPERSEDED,
            'total_amount' => 100,
            'source_language_code' => 'en',
            'target_language_code' => 'ar',
            'document_type_name' => 'Passport',
            'pricing_rule_name' => 'Standard',
        ]));

        $current = Estimate::query()->create($this->estimateAttrs([
            'uuid' => '11111111-1111-1111-1111-111111111102',
            'session_uuid' => $session,
            'previous_estimate_id' => null,
            'status' => Estimate::STATUS_QUOTED,
            'total_amount' => 250.5,
            'source_language_code' => 'en',
            'target_language_code' => 'ar',
            'document_type_name' => 'Passport',
            'pricing_rule_name' => 'Standard',
            'delivery_speed_name' => 'Express',
        ]));

        $current->addOns()->create([
            'name' => 'Notarization',
            'pricing_mode' => 'fixed',
            'unit_amount' => 50,
            'quantity' => 1,
            'amount' => 50,
        ]);

        $stats = (new EstimateDashboardStats)->build();

        $this->assertSame(1, $stats['kpis']['current_total']);
        $this->assertSame(1, $stats['kpis']['quoted_total']);
        $this->assertSame(0, $stats['kpis']['converted_total']);
        $this->assertSame(0.0, $stats['kpis']['conversion_rate']);
        $this->assertEquals(250.5, $stats['kpis']['pipeline_value']);
        $this->assertSame(100.0, $stats['kpis']['add_on_attach_rate']);
        $this->assertCount(1, $stats['language_pairs']);
        $this->assertSame('en → ar', $stats['language_pairs'][0]['label']);
        $this->assertCount(1, $stats['recent_quotes']);
        $this->assertSame($current->id, $stats['recent_quotes']->first()->id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function estimateAttrs(array $overrides = []): array
    {
        return array_merge([
            'uuid' => fake()->uuid(),
            'session_uuid' => fake()->uuid(),
            'status' => Estimate::STATUS_QUOTED,
            'billing_unit' => 'word',
            'billing_quantity' => 10,
            'unit_rate' => 0.5,
            'page_count' => 1,
            'word_count' => 10,
            'translation_amount' => 5,
            'add_ons_total' => 0,
            'delivery_speed_amount' => 0,
            'total_amount' => 5,
            'currency' => 'AED',
        ], $overrides);
    }
}
