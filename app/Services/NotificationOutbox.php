<?php

namespace App\Services;

use App\Jobs\SendNotifikasi as SendNotifikasiJob;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationOutbox
{
    /**
     * Catat notifikasi ke outbox lalu kirim melalui queue (FR-NTF-01).
     */
    public function queue(
        User $user,
        string $tipe,
        string $judul,
        string $pesan,
        ?Model $related = null,
    ): ?Notifikasi {
        $channel = filled($user->email) ? 'email' : (filled($user->phone) ? 'sms' : null);

        if ($channel === null) {
            return null;
        }

        $notifikasi = Notifikasi::create([
            'channel' => $channel,
            'tujuan' => $channel === 'email' ? $user->email : $user->phone,
            'user_id' => $user->id,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'related_type' => $related?->getMorphClass(),
            'related_id' => $related?->getKey(),
            'status' => 'menunggu',
        ]);

        SendNotifikasiJob::dispatch($notifikasi->id)->afterCommit();

        return $notifikasi;
    }
}
