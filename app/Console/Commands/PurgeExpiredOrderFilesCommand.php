<?php

namespace App\Console\Commands;

use App\Models\OrderDocument;
use App\Services\Orders\SecureOrderFileStore;
use Illuminate\Console\Command;

class PurgeExpiredOrderFilesCommand extends Command
{
    protected $signature = 'orders:purge-expired-files';

    protected $description = 'Securely delete order source/delivery files past retention';

    public function handle(SecureOrderFileStore $store): int
    {
        $query = OrderDocument::query()
            ->whereNull('purged_at')
            ->whereNotNull('retained_until')
            ->where('retained_until', '<=', now());

        $count = 0;
        $query->chunkById(100, function ($documents) use ($store, &$count): void {
            foreach ($documents as $document) {
                $store->purge($document);
                $document->order?->events()->create([
                    'type' => 'file_purged',
                    'actor_type' => 'system',
                    'payload' => [
                        'document_id' => $document->id,
                        'kind' => $document->kind,
                    ],
                ]);
                $count++;
            }
        });

        $this->info("Purged {$count} order document(s).");

        return self::SUCCESS;
    }
}
