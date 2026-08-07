@php
    /** @var array $card */
@endphp
<article class="vendor-discover-card" data-order-id="{{ $card['order_id'] }}">
    <div class="vendor-discover-card__top">
        <div class="vendor-discover-card__type">{{ $card['document_type'] }}</div>
        <div class="vendor-discover-card__amount">{!! $card['amount_html'] !!}</div>
    </div>

    <div class="vendor-discover-card__id">{{ $card['order_id'] }}</div>

    <div class="vendor-discover-card__meta">
        <div><i class="ti ti-language-hiragana"></i><span>{{ $card['language_pair'] }}</span></div>
        <div><i class="ti ti-truck-delivery"></i><span>{{ $card['delivery_label'] }}</span></div>
        <div><i class="ti ti-file-text"></i><span>{{ $card['pages_label'] }}</span></div>
        <div><i class="ti ti-alphabet-latin"></i><span>{{ $card['words_label'] }}</span></div>
    </div>

    @if (! empty($card['add_ons']))
        <div class="vendor-discover-card__addons">
            @foreach ($card['add_ons'] as $addOn)
                <span class="vendor-discover-card__addon">{{ $addOn['name'] }}</span>
            @endforeach
        </div>
    @endif

    @if (! empty($card['notes']))
        <p class="vendor-discover-card__notes">{{ \Illuminate\Support\Str::limit($card['notes'], 110) }}</p>
    @endif

    <div class="vendor-discover-card__footer">
        <span class="vendor-discover-card__time" dir="ltr">{{ $card['posted_at'] }}</span>
        <div class="vendor-discover-card__actions">
            <button
                type="button"
                class="btn btn-sm btn-secondary"
                data-vendor-order-view
                data-view-url="{{ $card['view_url'] }}"
            >{{ __('general.view') }}</button>
            <form method="POST" action="{{ $card['accept_url'] }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">{{ __('general.accept') }}</button>
            </form>
        </div>
    </div>
</article>
