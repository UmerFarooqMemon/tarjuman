<?php

namespace Tests\Feature\Api;

use App\Models\AddOn;
use App\Models\DeliverySpeed;
use App\Models\DocumentType;
use App\Models\Estimate;
use App\Models\Language;
use App\Models\PricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstimateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['api.token' => 'test-api-token']);
    }

    #[Test]
    public function it_rejects_missing_api_token(): void
    {
        $this->postJson('/api/estimate', [])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    #[Test]
    public function it_rejects_invalid_api_token(): void
    {
        $this->withHeader('X-API-Token', 'wrong-token')
            ->postJson('/api/estimate', [])
            ->assertUnauthorized();
    }

    #[Test]
    public function it_estimates_from_docx_with_optional_add_ons(): void
    {
        [$source, $target] = $this->createLanguages();
        $documentType = $this->createDocumentType();
        $this->createDefaultPricingRule();
        $fixedAddOn = $this->createAddOn('Notarization', AddOn::PRICING_MODE_FIXED, 50);
        $perPageAddOn = $this->createAddOn('Urgent', AddOn::PRICING_MODE_PER_PAGE, 10);

        $docxPath = $this->makeTempDocx('Hello world from estimate document');

        try {
            $response = $this->withHeader('X-API-Token', 'test-api-token')
                ->post('/api/estimate', [
                    'documents' => [
                        new UploadedFile($docxPath, 'sample.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                    ],
                    'document_type_id' => $documentType->id,
                    'source_language_id' => $source->id,
                    'target_language_id' => $target->id,
                    'add_on_ids' => [$fixedAddOn->id, $perPageAddOn->id],
                ]);

            $response->assertOk()
                ->assertJsonPath('document_type.id', $documentType->id)
                ->assertJsonPath('languages.source.code', 'en')
                ->assertJsonPath('languages.target.code', 'ar')
                ->assertJsonPath('documents.0.extension', 'docx')
                ->assertJsonPath('documents.0.method', 'native')
                ->assertJsonPath('currency.code', platformCurrency())
                ->assertJsonPath('currency.icon_url', currencyIconUrl());

            $this->assertGreaterThan(0, $response->json('totals.words'));
            $this->assertGreaterThan(0, $response->json('totals.pages'));
            $this->assertCount(2, $response->json('add_ons'));
            $this->assertNotNull($response->json('translation'));
            $this->assertNotNull($response->json('total_amount'));
            $this->assertNotNull($response->json('estimate_id'));
            $this->assertNotNull($response->json('request_id'));
            $this->assertSame($response->json('request_id'), $response->json('session_id'));
            $this->assertFalse(File::exists($docxPath) && str_contains(dirname($docxPath), 'tmp'.DIRECTORY_SEPARATOR.'estimates'));

            $estimate = Estimate::query()->with(['documents', 'addOns'])->findOrFail($response->json('estimate_id'));
            $this->assertSame(Estimate::STATUS_QUOTED, $estimate->status);
            $this->assertSame($response->json('request_id'), $estimate->uuid);
            $this->assertSame($response->json('session_id'), $estimate->session_uuid);
            $this->assertSame($documentType->id, $estimate->document_type_id);
            $this->assertSame($response->json('total_amount'), number_format((float) $estimate->total_amount, 4, '.', ''));
            $this->assertCount(1, $estimate->documents);
            $this->assertCount(2, $estimate->addOns);
        } finally {
            if (is_file($docxPath)) {
                @unlink($docxPath);
            }
        }
    }

    #[Test]
    public function it_returns_platform_translation_pricing_for_language_pair(): void
    {
        [$source, $target] = $this->createLanguages();
        $documentType = $this->createDocumentType();
        $fixedAddOn = $this->createAddOn('Notarization', AddOn::PRICING_MODE_FIXED, 50);
        $deliverySpeed = $this->createDeliverySpeed();

        PricingRule::create([
            'name' => 'Standard',
            'min_pages' => null,
            'max_pages' => null,
            'billing_unit' => PricingRule::BILLING_UNIT_WORD,
            'rate_amount' => 0.5,
            'currency' => 'AED',
            'priority' => 10,
            'is_active' => true,
        ]);

        $docxPath = $this->makeTempDocx('Hello world from estimate document');

        try {
            $response = $this->withHeader('X-API-Token', 'test-api-token')
                ->post('/api/estimate', [
                    'documents' => [
                        new UploadedFile($docxPath, 'sample.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                    ],
                    'document_type_id' => $documentType->id,
                    'source_language_id' => $source->id,
                    'target_language_id' => $target->id,
                    'add_on_ids' => [$fixedAddOn->id],
                    'delivery_speed_id' => $deliverySpeed->id,
                ]);

            $response->assertOk()
                ->assertJsonPath('translation.billing_unit', 'word')
                ->assertJsonPath('add_ons_total', '50.0000')
                ->assertJsonPath('delivery_speed.id', $deliverySpeed->id)
                ->assertJsonPath('delivery_speed_amount', '25.0000')
                ->assertJsonPath('currency.code', 'AED')
                ->assertJsonMissingPath('vendors');

            $wordCount = (int) $response->json('totals.words');
            $this->assertGreaterThan(0, $wordCount);
            $this->assertSame($wordCount, (int) $response->json('translation.quantity'));
            $this->assertSame(
                bcmul('0.5000', (string) $wordCount, 4),
                $response->json('translation.amount')
            );
            $this->assertSame(
                bcadd(bcadd($response->json('translation.amount'), '50.0000', 4), '25.0000', 4),
                $response->json('total_amount')
            );

            $estimate = Estimate::query()->findOrFail($response->json('estimate_id'));
            $this->assertSame('word', $estimate->billing_unit);
            $this->assertSame($wordCount, $estimate->billing_quantity);
            $this->assertSame($deliverySpeed->id, $estimate->delivery_speed_id);
            $this->assertSame('25.0000', number_format((float) $estimate->delivery_speed_amount, 4, '.', ''));
            $this->assertSame($response->json('translation.rule_id'), $estimate->pricing_rule_id);
        } finally {
            if (is_file($docxPath)) {
                @unlink($docxPath);
            }
        }
    }

    #[Test]
    public function it_wipes_temp_estimate_directories_after_request(): void
    {
        [$source, $target] = $this->createLanguages();
        $documentType = $this->createDocumentType();
        $this->createDefaultPricingRule();
        $docxPath = $this->makeTempDocx('Temporary wipe check document');

        try {
            $before = $this->estimateTempDirectories();

            $this->withHeader('Authorization', 'Bearer test-api-token')
                ->post('/api/estimate', [
                    'documents' => [
                        new UploadedFile($docxPath, 'wipe.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                    ],
                    'document_type_id' => $documentType->id,
                    'source_language_id' => $source->id,
                    'target_language_id' => $target->id,
                ])
                ->assertOk();

            $after = $this->estimateTempDirectories();

            $this->assertSame($before, $after);
        } finally {
            if (is_file($docxPath)) {
                @unlink($docxPath);
            }
        }
    }

    #[Test]
    public function it_supersedes_previous_estimate_when_customer_recalculates(): void
    {
        [$source, $target] = $this->createLanguages();
        $documentType = $this->createDocumentType();
        $this->createDefaultPricingRule();

        $firstDoc = $this->makeTempDocx('First estimate document text');
        $secondPassFirstDoc = $this->makeTempDocx('First estimate document text');
        $secondPassExtraDoc = $this->makeTempDocx('Second estimate document with extra pages words');

        try {
            $first = $this->withHeader('X-API-Token', 'test-api-token')
                ->post('/api/estimate', [
                    'documents' => [
                        new UploadedFile($firstDoc, 'first.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                    ],
                    'document_type_id' => $documentType->id,
                    'source_language_id' => $source->id,
                    'target_language_id' => $target->id,
                ])
                ->assertOk();

            $second = $this->withHeader('X-API-Token', 'test-api-token')
                ->post('/api/estimate', [
                    'documents' => [
                        new UploadedFile($secondPassFirstDoc, 'first.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                        new UploadedFile($secondPassExtraDoc, 'second.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                    ],
                    'document_type_id' => $documentType->id,
                    'source_language_id' => $source->id,
                    'target_language_id' => $target->id,
                    'previous_request_id' => $first->json('request_id'),
                ])
                ->assertOk();

            $this->assertSame($first->json('session_id'), $second->json('session_id'));
            $this->assertNotSame($first->json('request_id'), $second->json('request_id'));

            $original = Estimate::query()->findOrFail($first->json('estimate_id'));
            $latest = Estimate::query()->findOrFail($second->json('estimate_id'));

            $this->assertSame(Estimate::STATUS_SUPERSEDED, $original->status);
            $this->assertSame(Estimate::STATUS_QUOTED, $latest->status);
            $this->assertSame($original->id, $latest->previous_estimate_id);
            $this->assertSame(1, Estimate::query()->current()->count());
            $this->assertSame(2, Estimate::query()->count());

            $latest->markConverted(101);
            $this->assertSame(1, Estimate::query()->converted()->count());
            $this->assertSame(1, Estimate::query()->current()->count());
            $this->assertSame(1, Estimate::query()->current()->converted()->count());
        } finally {
            foreach ([$firstDoc, $secondPassFirstDoc, $secondPassExtraDoc] as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }

    /**
     * @return array{0: Language, 1: Language}
     */
    protected function createLanguages(): array
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

        return [$source, $target];
    }

    protected function createDocumentType(): DocumentType
    {
        $type = DocumentType::create([
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $type->translateOrNew('en')->name = 'Passport';
        $type->translateOrNew('ar')->name = 'جواز سفر';
        $type->save();

        return $type;
    }

    protected function createAddOn(string $name, string $mode, float $amount): AddOn
    {
        $addOn = AddOn::create([
            'pricing_mode' => $mode,
            'default_amount' => $amount,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $addOn->translateOrNew('en')->name = $name;
        $addOn->translateOrNew('ar')->name = $name;
        $addOn->save();

        return $addOn;
    }

    protected function createDefaultPricingRule(): PricingRule
    {
        return PricingRule::create([
            'name' => 'Any volume',
            'min_pages' => null,
            'max_pages' => null,
            'billing_unit' => PricingRule::BILLING_UNIT_WORD,
            'rate_amount' => 0.5,
            'currency' => 'AED',
            'priority' => 1,
            'is_active' => true,
        ]);
    }

    protected function createDeliverySpeed(): DeliverySpeed
    {
        $speed = DeliverySpeed::create([
            'price_amount' => 25,
            'min_hours' => null,
            'max_hours' => 24,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $speed->translateOrNew('en')->fill([
            'name' => 'Express',
            'duration_label' => '24 Hours',
        ]);
        $speed->translateOrNew('ar')->fill([
            'name' => 'سريع',
            'duration_label' => '٢٤ ساعة',
        ]);
        $speed->save();

        return $speed;
    }

    protected function makeTempDocx(string $text): string
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addText($text);

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'estimate_'.uniqid('', true).'.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    /**
     * @return list<string>
     */
    protected function estimateTempDirectories(): array
    {
        $root = storage_path('app/tmp/estimates');
        if (! is_dir($root)) {
            return [];
        }

        return collect(File::directories($root))
            ->map(fn (string $dir) => basename($dir))
            ->sort()
            ->values()
            ->all();
    }
}
