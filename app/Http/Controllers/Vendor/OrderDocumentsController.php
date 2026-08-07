<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Order;
use App\Models\OrderDocument;
use App\Services\Orders\OrderCompleteService;
use App\Services\Orders\OrderDocumentAccess;
use App\Services\Orders\OrderDocumentStreamer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderDocumentsController extends Controller
{
    public function __construct(
        protected OrderDocumentAccess $access,
        protected OrderDocumentStreamer $streamer,
        protected OrderCompleteService $completeService,
    ) {
        $this->middleware('auth:vendor');
        $this->middleware('throttle:vendor-docs');
        // Stream URLs are relative (host-independent); validate accordingly.
        $this->middleware(ValidateSignature::relative())->only('stream');
    }

    public function store(Request $request, Order $order)
    {
        $vendorUser = auth('vendor')->user();
        $vendorId = (int) ($vendorUser?->vendor_id ?? 0);

        if (! $this->completeService->vendorCanManageDelivery($order, $vendorId)) {
            throw new AccessDeniedHttpException(__('general.order_delivery_upload_denied'));
        }

        $mimes = implode(',', config('estimation.allowed_mimes', ['pdf', 'docx', 'jpg', 'jpeg', 'png']));
        $maxKb = (int) config('estimation.max_file_kb', 10240);
        $maxFiles = (int) config('estimation.max_files', 10);

        $request->validate([
            'documents' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
            'documents.*' => ['required', 'file', 'mimes:'.$mimes, 'max:'.$maxKb],
        ]);

        /** @var list<UploadedFile> $files */
        $files = array_values(array_filter(
            $request->file('documents', []),
            fn ($file) => $file instanceof UploadedFile
        ));

        try {
            $this->completeService->uploadDeliveryDocuments($order, $files, (int) $vendorUser->id);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('general.order_delivery_uploaded'));
    }

    public function destroy(Order $order, OrderDocument $document)
    {
        $vendorUser = auth('vendor')->user();
        $vendorId = (int) ($vendorUser?->vendor_id ?? 0);

        if (! $this->completeService->vendorCanManageDelivery($order, $vendorId)) {
            throw new AccessDeniedHttpException(__('general.order_delivery_upload_denied'));
        }

        try {
            $this->completeService->deleteDeliveryDocument($order, $document, (int) $vendorUser->id);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('general.order_delivery_removed'));
    }

    public function preview(Order $order, OrderDocument $document)
    {
        $vendorUser = auth('vendor')->user();
        $this->access->assertVendorCanAccess($order, $document, $vendorUser);

        $this->streamer->audit($order, $document, $vendorUser, 'document_previewed');

        $binary = $this->streamer->decrypt($document);
        $mime = $this->streamer->resolveMime($document, $binary);
        $isPdf = $mime === 'application/pdf';
        $isImage = str_starts_with($mime, 'image/');
        $isDocx = $this->streamer->isDocx($document, $binary);

        // Embed small images directly so <img> never depends on a second request.
        $imageDataUri = null;
        if ($isImage && strlen($binary) <= 6_000_000) {
            $imageDataUri = 'data:'.$mime.';base64,'.base64_encode($binary);
        }

        $docxHtml = null;
        if ($isDocx) {
            $docxHtml = $this->streamer->docxToPreviewHtml($binary);
        }

        $contentUrl = $this->streamer->contentUrl($order, $document);
        $watermark = trim(($vendorUser->vendor?->displayName() ?: $vendorUser->fullName()).' · '.$order->order_id);
        $downloadAllowed = vendorDocumentDownloadAllowed();

        return view('vendor.orders.documents.preview', compact(
            'order',
            'document',
            'mime',
            'contentUrl',
            'imageDataUri',
            'docxHtml',
            'watermark',
            'isPdf',
            'isImage',
            'isDocx',
            'downloadAllowed',
        ));
    }

    public function download(Order $order, OrderDocument $document): Response
    {
        $vendorUser = auth('vendor')->user();
        $this->access->assertVendorCanAccess($order, $document, $vendorUser);
        $this->access->assertVendorCanDownload();

        $this->streamer->audit($order, $document, $vendorUser, 'document_downloaded');

        $watermark = trim(($vendorUser->vendor?->displayName() ?: $vendorUser->fullName()).' · '.$order->order_id);

        return $this->streamer->stream($document, 'attachment', $watermark);
    }

    /**
     * Session-authenticated inline content for <img>/iframe (no signature required).
     */
    public function content(Order $order, OrderDocument $document): Response
    {
        $vendorUser = auth('vendor')->user();
        $this->access->assertVendorCanAccess($order, $document, $vendorUser);

        $response = $this->streamer->stream($document, 'inline');

        if (! vendorDocumentDownloadAllowed()) {
            $response->headers->set('Content-Disposition', 'inline');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function stream(Order $order, OrderDocument $document): Response
    {
        $vendorUser = auth('vendor')->user();
        $this->access->assertVendorCanAccess($order, $document, $vendorUser);

        $response = $this->streamer->stream($document, 'inline');

        if (! vendorDocumentDownloadAllowed()) {
            $response->headers->set('Content-Disposition', 'inline');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
