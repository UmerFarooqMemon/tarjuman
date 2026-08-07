<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SecureOrderFileStore
{
    /**
     * Persist an uploaded source/delivery file for an order (encrypted at rest).
     *
     * @return array{uuid: string, disk_path: string, original_name: string, mime: ?string, checksum_sha256: string, encryption: string, size: int}
     */
    public function store(UploadedFile $file, Order $order, string $kind): array
    {
        $docUuid = (string) Str::uuid();
        $folder = $order->order_id ?: (string) $order->id;
        $relative = "orders/{$folder}/{$kind}/{$docUuid}.enc";
        $plaintext = file_get_contents($file->getRealPath());
        if ($plaintext === false) {
            throw new RuntimeException('Unable to read uploaded file.');
        }

        $ciphertext = Crypt::encryptString($plaintext);
        Storage::disk($this->disk())->put($relative, $ciphertext);

        return [
            'uuid' => $docUuid,
            'disk_path' => $relative,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'checksum_sha256' => hash('sha256', $plaintext),
            'encryption' => 'app_v1',
            'size' => strlen($plaintext),
        ];
    }

    public function decryptToString(OrderDocument $document): string
    {
        if ($document->isPurged()) {
            throw new RuntimeException('Document has been purged.');
        }

        $ciphertext = Storage::disk($this->disk())->get($document->disk_path);
        if ($ciphertext === null) {
            throw new RuntimeException('Encrypted document missing from storage.');
        }

        return Crypt::decryptString($ciphertext);
    }

    public function purge(OrderDocument $document): void
    {
        if (Storage::disk($this->disk())->exists($document->disk_path)) {
            Storage::disk($this->disk())->delete($document->disk_path);
        }

        $document->forceFill([
            'purged_at' => now(),
            'disk_path' => '',
        ])->save();
    }

    protected function disk(): string
    {
        return 'local';
    }
}
