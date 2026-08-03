<?php

namespace App\Services\Estimation;

use App\Models\AddOn;
use Illuminate\Support\Collection;

class AddOnEstimator
{
    /**
     * @param  list<int|string>  $addOnIds
     * @return array{items: list<array<string, mixed>>, total: string}
     */
    public function estimate(array $addOnIds, int $totalPages): array
    {
        $ids = collect($addOnIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [
                'items' => [],
                'total' => '0.0000',
            ];
        }

        /** @var Collection<int, AddOn> $addOns */
        $addOns = AddOn::query()
            ->with('translations')
            ->active()
            ->whereIn('id', $ids->all())
            ->ordered()
            ->get()
            ->keyBy('id');

        $items = [];
        $total = '0';

        foreach ($ids as $id) {
            $addOn = $addOns->get($id);
            if (! $addOn) {
                continue;
            }

            $amount = $addOn->pricing_mode === AddOn::PRICING_MODE_PER_PAGE
                ? bcmul((string) $addOn->default_amount, (string) max(1, $totalPages), 4)
                : number_format((float) $addOn->default_amount, 4, '.', '');

            $total = bcadd($total, $amount, 4);

            $items[] = [
                'id' => $addOn->id,
                'name' => $addOn->displayName(),
                'pricing_mode' => $addOn->pricing_mode,
                'unit_amount' => number_format((float) $addOn->default_amount, 4, '.', ''),
                'quantity' => $addOn->pricing_mode === AddOn::PRICING_MODE_PER_PAGE ? max(1, $totalPages) : 1,
                'amount' => $amount,
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}
