<?php

namespace App\Jobs;

use App\Models\Notifikasi;
use App\Services\Notifications\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendNotifikasi implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly string $notifikasiId) {}

    public function handle(SmsGateway $smsGateway): void
    {
        $notifikasi = Notifikasi::withoutTenantScope()->find($this->notifikasiId);

        if ($notifikasi === null || $notifikasi->status === 'terkirim') {
            return;
        }

        try {
            $providerRef = match ($notifikasi->channel) {
                'email' => $this->sendEmail($notifikasi),
                'sms', 'whatsapp' => $smsGateway->send($notifikasi),
                default => throw new RuntimeException('Kanal notifikasi tidak didukung.'),
            };

            $notifikasi->markSent($providerRef);
        } catch (Throwable $exception) {
            // Simpan galat generik; detail teknis tidak memuat data pribadi.
            $notifikasi->markFailed('Pengiriman gagal ('.class_basename($exception).').');
        }
    }

    private function sendEmail(Notifikasi $notifikasi): ?string
    {
        if (blank($notifikasi->tujuan)) {
            throw new RuntimeException('Alamat email tujuan kosong.');
        }

        Mail::raw($notifikasi->pesan, function ($message) use ($notifikasi): void {
            $message->to($notifikasi->tujuan)
                ->subject($notifikasi->judul ?? 'Notifikasi SiapKompeten');
        });

        return null;
    }
}
