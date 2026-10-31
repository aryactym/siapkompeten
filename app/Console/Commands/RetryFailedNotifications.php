<?php

namespace App\Console\Commands;

use App\Jobs\SendNotifikasi;
use App\Models\Notifikasi;
use Illuminate\Console\Command;

class RetryFailedNotifications extends Command
{
    protected $signature = 'notifikasi:retry-gagal';

    protected $description = 'Kirim ulang notifikasi berstatus gagal sesuai batas percobaan';

    public function handle(): int
    {
        $maxAttempts = (int) config('notifikasi.retry.tries', 3);

        $notifikasis = Notifikasi::withoutTenantScope()
            ->where('status', 'gagal')
            ->where('attempts', '<', $maxAttempts)
            ->orderBy('updated_at')
            ->limit(100)
            ->get();

        $notifikasis->each(function (Notifikasi $notifikasi): void {
            $notifikasi->update(['status' => 'menunggu']);
            SendNotifikasi::dispatch($notifikasi->id);
        });

        $this->info('Notifikasi dijadwalkan ulang: '.$notifikasis->count());

        return self::SUCCESS;
    }
}
