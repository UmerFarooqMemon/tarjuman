@php
    /** @var array $document */
    $canDelete = $canDelete ?? false;
@endphp
<div class="vendor-order-doc">
    <div class="vendor-order-doc__main">
        <div class="vendor-order-doc__icon"><i class="ti ti-file"></i></div>
        <div>
            <div class="vendor-order-doc__name">{{ $document['name'] }}</div>
            <div class="vendor-order-doc__meta">
                @if (($document['kind'] ?? '') === 'source')
                    <span>{{ __('general.pages') }}: {{ number_format((int) ($document['pages'] ?? 0)) }}</span>
                    <span class="vendor-order-doc__meta-sep" aria-hidden="true">·</span>
                    <span>{{ __('general.words') }}: {{ number_format((int) ($document['words'] ?? 0)) }}</span>
                    @if (! empty($document['amount_html']))
                        <span class="vendor-order-doc__meta-sep" aria-hidden="true">·</span>
                        <span class="vendor-order-doc__price">{!! $document['amount_html'] !!}</span>
                    @endif
                @else
                    <span>{{ __('general.translated_document') }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="vendor-order-doc__actions">
        <a
            href="{{ $document['preview_url'] }}"
            target="_blank"
            rel="noopener"
            class="btn btn-sm btn-outline-primary"
        >{{ __('general.preview') }}</a>
        @if (! empty($document['download_url']))
            <a href="{{ $document['download_url'] }}" class="btn btn-sm btn-primary">{{ __('general.download') }}</a>
        @endif
        @if ($canDelete && ! empty($document['delete_url']))
            <form method="POST" action="{{ $document['delete_url'] }}" onsubmit="return confirm(@json(__('general.confirm_delete_document')))">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-label-danger">{{ __('general.delete') }}</button>
            </form>
        @endif
    </div>
</div>
