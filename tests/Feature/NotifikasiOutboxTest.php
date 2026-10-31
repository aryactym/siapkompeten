<?php

namespace Tests\Feature;

use App\Jobs\SendNotifikasi;
use App\Models\Institution;
use App\Models\Notifikasi;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotifikasiOutboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_command_requeues_only_failed_notifications_below_max_attempts(): void
    {
        Queue::fake();

        $institution = Institution::create([
            'name' => 'LSP Notifikasi',
            'slug' => 'lsp-notifikasi',
            'is_active' => true,
        ]);

        TenantContext::set($institution);

        $user = User::factory()->create([
            'institution_id' => $institution->id,
            'email' => 'penerima@example.test',
            'status' => 'aktif',
        ]);

        $retryable = Notifikasi::create([
            'channel' => 'email',
            'tujuan' => $user->email,
            'user_id' => $user->id,
            'judul' => 'Uji Kirim Ulang',
            'pesan' => 'Pesan uji.',
            'tipe' => 'uji',
            'status' => 'gagal',
            'attempts' => 1,
        ]);

        $exhausted = Notifikasi::create([
            'channel' => 'email',
            'tujuan' => $user->email,
            'user_id' => $user->id,
            'judul' => 'Sudah Batas',
            'pesan' => 'Pesan uji.',
            'tipe' => 'uji',
            'status' => 'gagal',
            'attempts' => 3,
        ]);

        $terkirim = Notifikasi::create([
            'channel' => 'email',
            'tujuan' => $user->email,
            'user_id' => $user->id,
            'judul' => 'Sudah Terkirim',
            'pesan' => 'Pesan uji.',
            'tipe' => 'uji',
            'status' => 'terkirim',
            'attempts' => 0,
        ]);

        $this->artisan('notifikasi:retry-gagal')->assertSuccessful();

        $this->assertSame('menunggu', $retryable->fresh()->status);
        $this->assertSame('gagal', $exhausted->fresh()->status);
        $this->assertSame('terkirim', $terkirim->fresh()->status);

        Queue::assertPushed(SendNotifikasi::class, 1);
        Queue::assertPushed(SendNotifikasi::class, fn (SendNotifikasi $job): bool => $job->notifikasiId === $retryable->id);
    }
}
