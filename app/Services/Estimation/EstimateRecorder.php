<?php

namespace App\Services\Estimation;

use App\Models\DeliverySpeed;
use App\Models\DocumentType;
use App\Models\Estimate;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class EstimateRecorder
{
    /**
     * Persist a successful quote for reporting and later order conversion.
     *
     * Pass the previous estimate (via previous_request_id / session_id) when the
     * customer recalculates in the same checkout flow. Older quoted revisions in
     * that session are marked superseded so funnel counts stay 1 session → 1 order.
     *
     * @param  list<DocumentMetrics>  $documents
     * @param  array{pages: int, words: int}  $totals
     * @param  array{items: list<array<string, mixed>>, total: string}  $addOns
     * @param  array{
     *     translation: array<string, mixed>,
     *     add_ons_total: string,
     *     delivery_speed: array<string, mixed>|null,
     *     delivery_speed_amount: string,
     *     total_amount: string,
     *     currency: string
     * }  $pricing
     */
    public function record(
        string $uuid,
        DocumentType $documentType,
        Language $sourceLanguage,
        Language $targetLanguage,
        array $documents,
        array $totals,
        array $addOns,
        array $pricing,
        ?DeliverySpeed $deliverySpeed = null,
        ?Estimate $previousEstimate = null,
        ?string $sessionUuid = null,
    ): Estimate {
        return DB::transaction(function () use (
            $uuid,
            $documentType,
            $sourceLanguage,
            $targetLanguage,
            $documents,
            $totals,
            $addOns,
            $pricing,
            $deliverySpeed,
            $previousEstimate,
            $sessionUuid,
        ) {
            $translation = $pricing['translation'];
            $deliveryPayload = $pricing['delivery_speed'];

            [$resolvedSessionUuid, $previousId] = $this->resolveSession(
                $uuid,
                $previousEstimate,
                $sessionUuid,
            );

            $estimate = Estimate::query()->create([
                'uuid' => $uuid,
                'session_uuid' => $resolvedSessionUuid,
                'previous_estimate_id' => $previousId,
                'status' => Estimate::STATUS_QUOTED,
                'document_type_id' => $documentType->id,
                'document_type_name' => $documentType->displayName(),
                'source_language_id' => $sourceLanguage->id,
                'source_language_code' => $sourceLanguage->code,
                'source_language_name' => $sourceLanguage->displayName(),
                'target_language_id' => $targetLanguage->id,
                'target_language_code' => $targetLanguage->code,
                'target_language_name' => $targetLanguage->displayName(),
                'pricing_rule_id' => $translation['rule_id'] ?? null,
                'pricing_rule_name' => $translation['rule_name'] ?? null,
                'billing_unit' => $translation['billing_unit'] ?? null,
                'billing_quantity' => (int) ($translation['quantity'] ?? 0),
                'unit_rate' => $translation['unit_rate'] ?? '0.0000',
                'page_count' => (int) ($totals['pages'] ?? $translation['page_count'] ?? 0),
                'word_count' => (int) ($totals['words'] ?? $translation['word_count'] ?? 0),
                'translation_amount' => $translation['amount'] ?? '0.0000',
                'add_ons_total' => $pricing['add_ons_total'],
                'delivery_speed_id' => $deliverySpeed?->id,
                'delivery_speed_name' => $deliveryPayload['name'] ?? $deliverySpeed?->displayName(),
                'delivery_speed_amount' => $pricing['delivery_speed_amount'],
                'total_amount' => $pricing['total_amount'],
                'currency' => $pricing['currency'],
            ]);

            // Replace any still-open quotes in this session (the customer's latest calc wins).
            Estimate::query()
                ->forSession($resolvedSessionUuid)
                ->quoted()
                ->where('id', '!=', $estimate->id)
                ->each(fn (Estimate $old) => $old->markSuperseded());

            foreach ($documents as $metrics) {
                $estimate->documents()->create([
                    'filename' => $metrics->filename,
                    'extension' => $metrics->extension,
                    'pages' => $metrics->pages,
                    'words' => $metrics->words,
                    'method' => $metrics->method,
                    'used_fallback' => $metrics->usedFallback,
                    'warnings' => $metrics->warnings,
                ]);
            }

            foreach ($addOns['items'] as $item) {
                $estimate->addOns()->create([
                    'add_on_id' => $item['id'] ?? null,
                    'name' => $item['name'],
                    'pricing_mode' => $item['pricing_mode'],
                    'unit_amount' => $item['unit_amount'],
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'amount' => $item['amount'],
                ]);
            }

            return $estimate->load(['documents', 'addOns']);
        });
    }

    /**
     * @return array{0: string, 1: int|null}
     */
    protected function resolveSession(
        string $newUuid,
        ?Estimate $previousEstimate,
        ?string $sessionUuid,
    ): array {
        if ($previousEstimate !== null) {
            // Already checked out — treat as a brand-new customer journey.
            if ($previousEstimate->isConverted()) {
                return [$newUuid, null];
            }

            return [
                $previousEstimate->session_uuid ?: $previousEstimate->uuid,
                $previousEstimate->id,
            ];
        }

        if ($sessionUuid !== null && $sessionUuid !== '') {
            $sessionHasConverted = Estimate::query()
                ->forSession($sessionUuid)
                ->converted()
                ->exists();

            if ($sessionHasConverted) {
                return [$newUuid, null];
            }

            return [$sessionUuid, null];
        }

        return [$newUuid, null];
    }
}
