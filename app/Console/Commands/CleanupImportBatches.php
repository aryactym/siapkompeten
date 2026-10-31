<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('import:bersihkan {--days=1}')]
#[Description('Hapus batch impor asesi berstatus pratinjau yang kedaluwarsa')]
class CleanupImportBatches extends Command
{
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $batches = ImportBatch::withoutTenantScope()
            ->where('status', ImportBatch::STATUS_PREVIEW)
            ->where('created_at', '<', now()->subDays($days))
            ->limit(200)
            ->get();

        foreach ($batches as $batch) {
            Storage::disk('local')->delete($batch->file_path);
            ImportBatch::withoutTenantScope()->whereKey($batch->getKey())->delete();
        }

        $this->info('Batch impor pratinjau dihapus: '.$batches->count());

        return self::SUCCESS;
    }
}
