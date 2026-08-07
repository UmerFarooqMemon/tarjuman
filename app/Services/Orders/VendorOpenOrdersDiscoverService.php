<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VendorOpenOrdersDiscoverService
{
    public const PER_PAGE = 12;

    /**
     * @return array{q: string, sort: string, delivery_speed_id: ?int, add_on_id: ?int, document_type_id: ?int}
     */
    public function filtersFromRequest(Request $request): array
    {
        $sort = (string) $request->input('sort', 'newest');
        if (! in_array($sort, ['newest', 'amount_desc', 'amount_asc'], true)) {
            $sort = 'newest';
        }

        return [
            'q' => trim((string) $request->input('q', '')),
            'sort' => $sort,
            'delivery_speed_id' => $request->filled('delivery_speed_id')
                ? (int) $request->input('delivery_speed_id')
                : null,
            'add_on_id' => $request->filled('add_on_id')
                ? (int) $request->input('add_on_id')
                : null,
            'document_type_id' => $request->filled('document_type_id')
                ? (int) $request->input('document_type_id')
                : null,
        ];
    }

    /**
     * @param  array{q: string, sort: string, delivery_speed_id: ?int, add_on_id: ?int, document_type_id: ?int}  $filters
     */
    public function paginate(array $filters, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{q: string, sort: string, delivery_speed_id: ?int, add_on_id: ?int, document_type_id: ?int}  $filters
     */
    public function query(array $filters): Builder
    {
        $query = Order::query()
            ->with([
                'documentType.translations',
                'deliverySpeed.translations',
                'sourceLanguage.translations',
                'targetLanguage.translations',
                'addOns.addOn.translations',
                'estimate',
            ])
            ->where('status', Order::STATUS_OPEN)
            ->whereNull('vendor_id');

        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('order_id', 'like', $term)
                    ->orWhereHas('documentType.translations', fn (Builder $t) => $t->where('name', 'like', $term))
                    ->orWhereHas('sourceLanguage.translations', fn (Builder $t) => $t->where('name', 'like', $term))
                    ->orWhereHas('targetLanguage.translations', fn (Builder $t) => $t->where('name', 'like', $term));
            });
        }

        if ($filters['delivery_speed_id']) {
            $query->where('delivery_speed_id', $filters['delivery_speed_id']);
        }

        if ($filters['document_type_id']) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        if ($filters['add_on_id']) {
            $query->whereHas('addOns', fn (Builder $a) => $a->where('add_on_id', $filters['add_on_id']));
        }

        return match ($filters['sort']) {
            'amount_desc' => $query->orderByDesc('estimate_amount')->orderByDesc('id'),
            'amount_asc' => $query->orderBy('estimate_amount')->orderByDesc('id'),
            default => $query->latest('id'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function card(Order $order): array
    {
        $source = $order->sourceLanguage?->displayName() ?: '—';
        $target = $order->targetLanguage?->displayName() ?: '—';
        $delivery = $order->deliverySpeed;
        $deliveryName = $delivery?->displayName() ?: '—';
        $deliveryDuration = $delivery?->displayDuration() ?: null;
        $deliveryLabel = $deliveryName;
        if (filled($deliveryDuration) && $deliveryName !== '—') {
            $deliveryLabel = trim($deliveryName.' '.$deliveryDuration);
        }

        $pages = (int) ($order->page_count ?? $order->estimate?->page_count ?? 0);
        $words = (int) ($order->word_count ?? $order->estimate?->word_count ?? 0);
        $currency = $order->currency ?: platformCurrency();
        $amount = (float) ($order->estimate_amount ?? 0);

        $postedAt = optional($order->created_at)
            ?->timezone(config('app.timezone'))
            ?->locale('en')
            ?->format('d M Y, g:i A');

        return [
            'order_id' => $order->order_id,
            'document_type' => $order->documentType?->displayName()
                ?: ($order->estimate?->document_type_name ?: '—'),
            'language_pair' => trim($source.' → '.$target, ' →'),
            'delivery_label' => $deliveryLabel,
            'pages' => $pages,
            'words' => $words,
            'pages_label' => trans_choice('general.pages_count', $pages, ['count' => number_format($pages)]),
            'words_label' => trans_choice('general.words_count', $words, ['count' => number_format($words)]),
            'amount' => $amount,
            'amount_html' => formatMoney($amount, $currency),
            'currency' => $currency,
            'posted_at' => $postedAt,
            'notes' => filled($order->customer_note) ? $order->customer_note : null,
            'add_ons' => $order->addOns->map(function ($addOn) {
                return [
                    'name' => $addOn->addOn?->displayName() ?: ($addOn->name ?: '—'),
                ];
            })->values()->all(),
            'view_url' => route('vendor.orders.details', $order, false),
            'accept_url' => route('vendor.orders.accept', $order, false),
        ];
    }
}
