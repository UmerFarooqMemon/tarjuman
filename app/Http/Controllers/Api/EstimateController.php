<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EstimateRequest;
use App\Models\DeliverySpeed;
use App\Models\DocumentType;
use App\Models\Language;
use App\Services\Estimation\AddOnEstimator;
use App\Services\Estimation\DocumentAnalyzer;
use App\Services\Estimation\DocumentMetrics;
use App\Services\Estimation\EstimatePricingService;
use App\Services\Estimation\TesseractLanguageMapper;
use App\Services\Estimation\TesseractOcrService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class EstimateController extends Controller
{
    public function store(
        EstimateRequest $request,
        DocumentAnalyzer $analyzer,
        AddOnEstimator $addOnEstimator,
        TesseractLanguageMapper $languageMapper,
        TesseractOcrService $ocrService,
        EstimatePricingService $estimatePricingService,
    ): JsonResponse {
        /** @var list<\Illuminate\Http\UploadedFile> $files */
        $files = $request->file('documents', []);

        $sourceLanguage = Language::query()->findOrFail($request->integer('source_language_id'));
        $targetLanguage = Language::query()->findOrFail($request->integer('target_language_id'));

        $deliverySpeed = null;
        if ($request->filled('delivery_speed_id')) {
            $deliverySpeed = DeliverySpeed::query()
                ->active()
                ->findOrFail($request->integer('delivery_speed_id'));
        }

        // OCR reads the source document using the matching installed pack.
        $ocrLanguages = $ocrService->resolveLanguages(
            $languageMapper->forSource($sourceLanguage->code)
        );

        $analysis = $analyzer->analyze($files, $ocrLanguages);
        $totals = $analysis['totals'];
        $addOns = $addOnEstimator->estimate(
            $request->input('add_on_ids', []),
            (int) $totals['pages'],
        );

        $documentType = DocumentType::with('translations')->findOrFail($request->integer('document_type_id'));

        try {
            $pricing = $estimatePricingService->quote(
                pageCount: max(1, (int) $totals['pages']),
                wordCount: max(0, (int) $totals['words']),
                addOnsTotal: $addOns['total'],
                deliverySpeed: $deliverySpeed,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'No pricing is configured for this document volume.',
                'errors' => [
                    'pricing' => ['No pricing is configured for this document volume.'],
                ],
            ], 422);
        }

        return response()->json([
            'request_id' => $analysis['request_id'],
            'document_type' => [
                'id' => $documentType->id,
                'name' => $documentType->displayName(),
            ],
            'languages' => [
                'source' => [
                    'id' => $sourceLanguage->id,
                    'code' => $sourceLanguage->code,
                    'name' => $sourceLanguage->displayName(),
                ],
                'target' => [
                    'id' => $targetLanguage->id,
                    'code' => $targetLanguage->code,
                    'name' => $targetLanguage->displayName(),
                ],
            ],
            'documents' => array_map(
                fn (DocumentMetrics $metrics) => $metrics->toArray(),
                $analysis['documents'],
            ),
            'totals' => $totals,
            'add_ons' => $addOns['items'],
            'add_ons_total' => $addOns['total'],
            'translation' => $pricing['translation'],
            'delivery_speed' => $pricing['delivery_speed'],
            'delivery_speed_amount' => $pricing['delivery_speed_amount'],
            'total_amount' => $pricing['total_amount'],
            'currency' => [
                'code' => platformCurrency(),
                'icon_url' => currencyIconUrl(),
            ],
        ]);
    }
}
