<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\VendorUser;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderDocumentStreamer
{
    public function __construct(
        protected SecureOrderFileStore $files,
    ) {}

    /**
     * Short-lived signed URL for iframe/preview streaming (default 3 minutes).
     */
    public function temporaryStreamUrl(Order $order, OrderDocument $document, int $expiresInSeconds = 180): string
    {
        return URL::temporarySignedRoute(
            'vendor.orders.documents.stream',
            now()->addSeconds($expiresInSeconds),
            [
                'order' => $order->order_id,
                'document' => $document->uuid,
            ],
            absolute: false
        );
    }

    /**
     * Session-authenticated content URL (no signature) for same-tab <img>/iframe preview.
     */
    public function contentUrl(Order $order, OrderDocument $document): string
    {
        // Absolute URL avoids iframe path-resolution quirks under locale prefixes.
        return route('vendor.orders.documents.content', [
            'order' => $order,
            'document' => $document,
        ]);
    }

    public function decrypt(OrderDocument $document): string
    {
        return $this->files->decryptToString($document);
    }

    public function resolveMime(OrderDocument $document, ?string $binary = null): string
    {
        $binary ??= $this->decrypt($document);
        $sniffed = $this->sniffMime($binary);
        $declared = strtolower(trim((string) $document->mime));

        if ($sniffed) {
            return $sniffed;
        }

        if ($declared === 'image/jpg') {
            return 'image/jpeg';
        }

        // Some uploads store "application/x-pdf" / "application/octet-stream" for PDFs.
        $name = strtolower((string) $document->original_name);
        if (str_ends_with($name, '.pdf')) {
            return 'application/pdf';
        }

        if (str_ends_with($name, '.docx')) {
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }

        if (
            str_contains($declared, 'wordprocessingml')
            || $declared === 'application/msword'
        ) {
            return $declared;
        }

        return $declared !== '' ? $declared : 'application/octet-stream';
    }

    public function isImage(OrderDocument $document, ?string $binary = null): bool
    {
        return str_starts_with($this->resolveMime($document, $binary), 'image/');
    }

    public function isPdf(OrderDocument $document, ?string $binary = null): bool
    {
        return $this->resolveMime($document, $binary) === 'application/pdf';
    }

    public function isDocx(OrderDocument $document, ?string $binary = null): bool
    {
        $mime = $this->resolveMime($document, $binary);
        if (str_contains($mime, 'wordprocessingml.document')) {
            return true;
        }

        $name = strtolower((string) $document->original_name);

        return str_ends_with($name, '.docx');
    }

    /**
     * Convert a DOCX binary into sanitized HTML suitable for inline preview.
     */
    public function docxToPreviewHtml(string $binary): ?string
    {
        if ($binary === '') {
            return null;
        }

        $tmpBase = tempnam(sys_get_temp_dir(), 'trj_docx_');
        if ($tmpBase === false) {
            return null;
        }

        $docxPath = $tmpBase.'.docx';
        @unlink($tmpBase);

        $htmlPath = null;

        try {
            if (file_put_contents($docxPath, $binary) === false) {
                return null;
            }

            $phpWord = IOFactory::load($docxPath);
            $writer = IOFactory::createWriter($phpWord, 'HTML');
            $htmlPath = tempnam(sys_get_temp_dir(), 'trj_docx_html_');
            if ($htmlPath === false) {
                return null;
            }

            $writer->save($htmlPath);
            $raw = file_get_contents($htmlPath);
            if ($raw === false || trim($raw) === '') {
                return null;
            }

            return $this->sanitizeDocxHtml($raw);
        } catch (Throwable $e) {
            Log::warning('DOCX preview conversion failed', ['error' => $e->getMessage()]);

            return null;
        } finally {
            if (is_string($docxPath) && is_file($docxPath)) {
                @unlink($docxPath);
            }
            if (is_string($htmlPath) && is_file($htmlPath)) {
                @unlink($htmlPath);
            }
        }
    }

    /**
     * Build a data URI for reliable <img> preview (avoids signed-URL/img quirks).
     */
    public function dataUri(OrderDocument $document, int $maxBytes = 6_000_000): ?string
    {
        $binary = $this->decrypt($document);
        if (strlen($binary) > $maxBytes) {
            return null;
        }

        $mime = $this->resolveMime($document, $binary);
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    public function stream(
        OrderDocument $document,
        string $disposition = 'inline',
        ?string $watermarkLabel = null,
    ): StreamedResponse|Response {
        $plaintext = $this->decrypt($document);
        $mime = $this->resolveMime($document, $plaintext);
        $filename = $document->original_name ?: ($document->uuid.'.bin');

        // Only mutate downloads. Preview must keep original bytes for rendering.
        if ($disposition === 'attachment' && $watermarkLabel && str_starts_with($mime, 'image/')) {
            $safeLabel = preg_replace('/[^\x20-\x7E]/', '', $watermarkLabel) ?: (string) $document->uuid;
            $watermarked = $this->watermarkImage($plaintext, $mime, trim($safeLabel));
            if (is_array($watermarked)) {
                $plaintext = $watermarked['binary'];
                $mime = $watermarked['mime'];
            }
        }

        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($plaintext),
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($disposition === 'inline') {
            // Chrome's built-in PDF viewer often fails with Cache-Control: no-store
            // when embedding authenticated responses in iframe/<object>.
            $headers['Cache-Control'] = 'private, max-age=60, must-revalidate';
            $headers['Content-Disposition'] = 'inline; filename="'.$this->safeFilename($filename).'"';

            return response($plaintext, 200, $headers);
        }

        $headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, private, max-age=0';
        $headers['Pragma'] = 'no-cache';
        $headers['Expires'] = '0';

        return response()->streamDownload(function () use ($plaintext) {
            echo $plaintext;
        }, $filename, $headers, $disposition);
    }

    public function audit(Order $order, OrderDocument $document, VendorUser $actor, string $type): void
    {
        $order->events()->create([
            'type' => $type,
            'actor_type' => 'vendor_user',
            'actor_id' => $actor->id,
            'payload' => [
                'document_uuid' => $document->uuid,
                'document_name' => $document->original_name,
                'mime' => $document->mime,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ],
        ]);
    }

    protected function sniffMime(string $binary): ?string
    {
        if (str_starts_with($binary, '%PDF')) {
            return 'application/pdf';
        }

        if (strlen($binary) >= 3 && $binary[0] === "\xFF" && $binary[1] === "\xD8" && $binary[2] === "\xFF") {
            return 'image/jpeg';
        }

        if (str_starts_with($binary, "\x89PNG\r\n\x1a\n")) {
            return 'image/png';
        }

        if (str_starts_with($binary, 'GIF87a') || str_starts_with($binary, 'GIF89a')) {
            return 'image/gif';
        }

        if (str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP') {
            return 'image/webp';
        }

        // DOCX is a ZIP package; local headers for Content_Types / word usually appear early.
        if (str_starts_with($binary, "PK\x03\x04")) {
            $probe = substr($binary, 0, min(strlen($binary), 65536));
            if (str_contains($probe, 'word/') || str_contains($probe, 'wordprocessingml')) {
                return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            }
        }

        return null;
    }

    /**
     * Keep only safe markup for embedding in the vendor preview shell.
     */
    protected function sanitizeDocxHtml(string $html): string
    {
        $styles = '';
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches)) {
            foreach ($matches[1] as $css) {
                // Block CSS that could escape the preview shell.
                $css = preg_replace('/@import\b[^;]*;/i', '', $css) ?? $css;
                $css = preg_replace('/expression\s*\(/i', '(', $css) ?? $css;
                $css = preg_replace('/javascript\s*:/i', '', $css) ?? $css;
                $styles .= '<style>'.$css.'</style>';
            }
        }

        $body = $html;
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $matches)) {
            $body = $matches[1];
        }

        // Drop scripts/iframes/objects; keep basic Word formatting markup.
        $body = preg_replace('#<(script|iframe|object|embed)[^>]*>.*?</\1>#is', '', $body) ?? $body;
        $body = preg_replace('#\son\w+\s*=\s*("|\').*?\1#i', '', $body) ?? $body;
        $body = preg_replace('#javascript\s*:#i', '', $body) ?? $body;

        $body = trim($body);
        if ($body === '') {
            return '<p></p>';
        }

        return $styles.$body;
    }

    /**
     * @return array{binary: string, mime: string}|null
     */
    protected function watermarkImage(string $binary, string $mime, string $label): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            return null;
        }

        if (function_exists('imagepalettetotruecolor') && ! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $color = imagecolorallocatealpha($image, 40, 40, 40, 70);
        $font = 5;
        $text = substr($label, 0, 80);
        $textWidth = imagefontwidth($font) * strlen($text);
        $x = max(8, $width - $textWidth - 12);
        $y = max(8, $height - 20);
        imagestring($image, $font, $x, $y, $text, $color);

        $outputMime = str_contains($mime, 'png') || str_contains($mime, 'webp') || str_contains($mime, 'gif')
            ? 'image/png'
            : 'image/jpeg';

        ob_start();
        if ($outputMime === 'image/png') {
            imagepng($image);
        } else {
            imagejpeg($image, null, 90);
        }
        imagedestroy($image);

        $out = ob_get_clean();
        if ($out === false) {
            return null;
        }

        return [
            'binary' => $out,
            'mime' => $outputMime,
        ];
    }

    protected function safeFilename(string $filename): string
    {
        return str_replace(['"', "\r", "\n"], '', $filename);
    }
}
