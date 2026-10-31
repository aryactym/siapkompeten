<?php

namespace App\Services\Notifications;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Http;

class HttpSmsGateway implements SmsGateway
{
    public function send(Notifikasi $notifikasi): ?string
    {
        $url = config('notifikasi.drivers.http.url');

        if (blank($url)) {
            throw new \RuntimeException('Endpoint SMS belum dikonfigurasi.');
        }

        $response = Http::withToken((string) config('notifikasi.drivers.http.token'))
            ->timeout(15)
            ->post($url, [
                'sender' => config('notifikasi.drivers.http.sender'),
                'destination' => $notifikasi->tujuan,
                'message' => $notifikasi->pesan,
            ]);

        $response->throw();

        return is_string($response->json('message_id')) ? $response->json('message_id') : null;
    }
}
