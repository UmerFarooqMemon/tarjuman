@extends('vendor.layouts.app')

@section('css')
<style>
    .doc-preview-shell {
        position: relative;
        min-height: 75vh;
        background: #f5f5f9;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .doc-preview-frame,
    .doc-preview-image,
    .doc-preview-canvas {
        width: 100%;
        border: 0;
        background: #fff;
    }
    .doc-preview-pdf {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 75vh;
    }
    .doc-preview-pdf .doc-preview-frame {
        display: block;
        width: 100%;
        height: 75vh;
        position: relative;
        z-index: 1;
    }
    /* Watermark stays visible; pointer-events:none lets wheel/scroll reach the iframe */
    .doc-preview-shell.is-pdf .doc-watermark,
    .doc-preview-shell.is-docx .doc-watermark {
        z-index: 2;
        pointer-events: none;
    }
    .doc-preview-docx {
        position: relative;
        z-index: 1;
        height: 75vh;
        overflow: auto;
        overscroll-behavior: contain;
        background: #fff;
        padding: 1.5rem 1.75rem;
    }
    .doc-preview-docx__body {
        max-width: 48rem;
        margin: 0 auto;
        color: #2f2b3d;
        font-size: 0.975rem;
        line-height: 1.65;
        word-break: break-word;
    }
    .doc-preview-docx__body p {
        margin-bottom: 0.85rem;
    }
    .doc-preview-docx__body table {
        width: 100%;
        max-width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    .doc-preview-docx__body table td,
    .doc-preview-docx__body table th {
        border: 1px solid #d9dee3;
        padding: 0.35rem 0.5rem;
        vertical-align: top;
    }
    .doc-preview-docx__body img {
        max-width: 100%;
        height: auto;
    }
    .doc-preview-image,
    .doc-preview-canvas {
        object-fit: contain;
        display: block;
        margin: 0 auto;
        max-width: 100%;
        min-height: 70vh;
        padding: 1rem;
        position: relative;
        z-index: 1;
    }
    .doc-preview-canvas {
        padding: 0;
        width: 100%;
        height: auto;
        max-height: 75vh;
    }
    .doc-preview-shell.is-protected {
        user-select: none;
        -webkit-user-select: none;
    }
    .doc-preview-shell.is-protected .doc-preview-image,
    .doc-preview-shell.is-protected .doc-preview-canvas {
        -webkit-user-drag: none;
        user-drag: none;
        pointer-events: none;
    }
    /* Image-only: block save/drag. Never cover PDFs — that traps scroll. */
    .doc-protect-overlay {
        display: none;
        position: absolute;
        inset: 0;
        z-index: 3;
        cursor: default;
    }
    .doc-preview-shell.is-protected:not(.is-pdf):not(.is-docx) .doc-protect-overlay {
        display: block;
    }
    .doc-watermark {
        pointer-events: none;
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        overflow: hidden;
    }
    .doc-watermark span {
        transform: rotate(-28deg);
        font-size: clamp(1.1rem, 2.5vw, 1.75rem);
        font-weight: 700;
        letter-spacing: 0.04em;
        color: rgba(40, 40, 40, 0.14);
        white-space: nowrap;
        user-select: none;
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-1">{{ __('general.preview') }}: {{ $document->original_name }}</h4>
        <p class="text-muted mb-0">{{ $order->order_id }} · {{ $mime }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($downloadAllowed)
            <a href="{{ route('vendor.orders.documents.download', [$order, $document]) }}" class="btn btn-primary">
                {{ __('general.download') }}
            </a>
        @endif
        <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-outline-secondary">
            {{ __('general.back') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div
            class="doc-preview-shell{{ $downloadAllowed ? '' : ' is-protected' }}{{ $isPdf ? ' is-pdf' : '' }}{{ !empty($isDocx) ? ' is-docx' : '' }}"
            data-doc-preview
            data-download-allowed="{{ $downloadAllowed ? '1' : '0' }}"
        >
            <div class="doc-watermark" aria-hidden="true"><span>{{ $watermark }}</span></div>
            <div class="doc-protect-overlay" aria-hidden="true"></div>

            @if ($isPdf)
                <div class="doc-preview-pdf" data-pdf-preview data-src="{{ $contentUrl }}">
                    <div class="p-5 text-center text-muted" data-pdf-loading>
                        {{ __('general.loading') }}
                    </div>
                    <iframe
                        class="doc-preview-frame d-none"
                        data-pdf-frame
                        title="{{ $document->original_name }}"
                    ></iframe>
                    <div class="p-4 text-center d-none" data-pdf-fallback>
                        <p class="mb-3">{{ __('general.order_document_preview_open_tab') }}</p>
                        <a href="{{ $contentUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                            {{ __('general.open_in_new_tab') }}
                        </a>
                    </div>
                </div>
            @elseif (!empty($isDocx))
                @if (!empty($docxHtml))
                    <div class="doc-preview-docx">
                        <div class="doc-preview-docx__body">
                            {!! $docxHtml !!}
                        </div>
                    </div>
                @else
                    <div class="p-5 text-center">
                        <p class="mb-3">{{ __('general.order_document_preview_docx_failed') }}</p>
                        @if ($downloadAllowed)
                            <a href="{{ route('vendor.orders.documents.download', [$order, $document]) }}" class="btn btn-primary">
                                {{ __('general.download') }}
                            </a>
                        @endif
                    </div>
                @endif
            @elseif ($isImage)
                @if ($downloadAllowed)
                    <img
                        class="doc-preview-image"
                        src="{{ $imageDataUri ?: $contentUrl }}"
                        alt="{{ $document->original_name }}"
                    >
                @else
                    <canvas
                        class="doc-preview-canvas"
                        data-doc-canvas
                        data-src="{{ $imageDataUri ?: $contentUrl }}"
                        aria-label="{{ $document->original_name }}"
                    ></canvas>
                @endif
            @else
                <div class="p-5 text-center">
                    <p class="mb-3">{{ __('general.order_document_preview_unsupported') }}</p>
                    @if ($downloadAllowed)
                        <a href="{{ route('vendor.orders.documents.download', [$order, $document]) }}" class="btn btn-primary">
                            {{ __('general.download') }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
        <p class="small text-muted mt-3 mb-0">{{ __('general.order_document_security_note') }}</p>
    </div>
</div>
@endsection

@section('footer-js')
<script>
(function () {
    // Fetch PDF as a blob so Chrome can render it from a blob: URL.
    // Direct iframe src to authenticated/streaming responses often stays blank.
    var pdfRoot = document.querySelector('[data-pdf-preview]');
    if (pdfRoot) {
        var src = pdfRoot.getAttribute('data-src');
        var frame = pdfRoot.querySelector('[data-pdf-frame]');
        var loading = pdfRoot.querySelector('[data-pdf-loading]');
        var fallback = pdfRoot.querySelector('[data-pdf-fallback]');
        var showFallback = function () {
            if (loading) loading.classList.add('d-none');
            if (frame) frame.classList.add('d-none');
            if (fallback) fallback.classList.remove('d-none');
        };
        if (src && frame && window.fetch) {
            fetch(src, { credentials: 'same-origin', headers: { 'Accept': 'application/pdf,*/*' } })
                .then(function (res) {
                    if (!res.ok) throw new Error('preview_fetch_failed');
                    return res.blob();
                })
                .then(function (blob) {
                    var type = (blob && blob.type) ? blob.type : '';
                    if (type && type.indexOf('pdf') === -1 && type.indexOf('octet-stream') === -1) {
                        // Likely an HTML error/login page wrapped as a blob.
                        throw new Error('preview_not_pdf');
                    }
                    var pdfBlob = (type.indexOf('pdf') !== -1)
                        ? blob
                        : new Blob([blob], { type: 'application/pdf' });
                    var url = URL.createObjectURL(pdfBlob);
                    frame.src = url + '#toolbar=0&navpanes=0';
                    frame.classList.remove('d-none');
                    if (loading) loading.classList.add('d-none');
                })
                .catch(showFallback);
        } else {
            showFallback();
        }
    }
})();
</script>
@unless ($downloadAllowed)
<script>
(function () {
    var root = document.querySelector('[data-doc-preview]');
    if (!root || root.getAttribute('data-download-allowed') === '1') return;

    var block = function (e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    };

    root.addEventListener('contextmenu', block);
    root.addEventListener('dragstart', block);
    document.addEventListener('keydown', function (e) {
        var key = (e.key || '').toLowerCase();
        if ((e.ctrlKey || e.metaKey) && (key === 's' || key === 'p')) {
            e.preventDefault();
        }
    });

    var canvas = root.querySelector('[data-doc-canvas]');
    if (!canvas) return;

    var src = canvas.getAttribute('data-src');
    if (!src) return;

    var img = new Image();
    img.onload = function () {
        var maxW = canvas.parentElement ? canvas.parentElement.clientWidth - 16 : img.width;
        var scale = Math.min(1, maxW / img.width);
        var w = Math.max(1, Math.floor(img.width * scale));
        var h = Math.max(1, Math.floor(img.height * scale));
        canvas.width = w;
        canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
    };
    img.src = src;
})();
</script>
@endunless
@endsection
