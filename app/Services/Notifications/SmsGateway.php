<?php

namespace App\Services\Notifications;

use App\Models\Notifikasi;

interface SmsGateway
{
    /**
     * Kirim SMS dan kembalikan referensi pesan dari penyedia (bila ada).
     */
    public function send(Notifikasi $notifikasi): ?string;
}
