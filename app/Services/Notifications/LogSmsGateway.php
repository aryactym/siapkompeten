<?php

namespace App\Services\Notifications;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogSmsGateway implements SmsGateway
{
    public function send(Notifikasi $notifikasi): ?string
    {
        // Pesan tidak ditulis ke log agar data pribadi tidak bocor (OWASP A09).
        Log::info('SMS terkirim melalui driver log.', [
            'notifikasi_id' => $notifikasi->id,
            'tipe' => $notifikasi->tipe,
        ]);

        return 'log-'.Str::uuid()->toString();
    }
}
