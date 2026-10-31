<?php

namespace App\Actions;

use App\Models\Asesor;
use App\Models\AsesorInvitation;
use App\Models\User;
use App\Services\NotificationOutbox;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Mengundang asesor baru ke lembaga (branch `single`).
 *
 * Membuat akun User (peran asesor) + profil Asesor, lalu mengirim tautan
 * aktivasi (set kata sandi) ke email undangan. Berbeda dari `CreateAsesiManual`,
 * asesor tidak memiliki NIK wajib di registrasi — di sini NIK opsional.
 */
class InviteAsesor
{
    public function __construct(private readonly NotificationOutbox $outbox) {}

    /**
     * @param  array{nik?: ?string, nama: string, email: string, no_register: string, bidang_keahlian?: ?string}  $data
     */
    public function execute(array $data, User $admin): AsesorInvitation
    {
        $institution = TenantContext::requireId() ?? (string) $admin->institution_id;

        $nik = isset($data['nik']) ? trim($data['nik']) : null;
        $nikHash = $nik !== null && $nik !== '' ? User::hashNik($nik) : null;

        // Cek duplikasi NIK & email per lembaga.
        $nikExists = $nikHash !== null
            ? User::query()
                ->where('institution_id', $institution)
                ->where('nik_hash', $nikHash)
                ->exists()
            : false;

        throw_if($nikExists, ValidationException::withMessages([
            'nik' => 'NIK sudah terdaftar pada lembaga ini.',
        ]));

        $emailExists = User::query()
            ->where('institution_id', $institution)
            ->where('email', $data['email'])
            ->exists();

        throw_if($emailExists, ValidationException::withMessages([
            'email' => 'Email sudah terdaftar pada lembaga ini.',
        ]));

        $noRegisterExists = Asesor::query()
            ->where('no_register', $data['no_register'])
            ->exists();

        throw_if($noRegisterExists, ValidationException::withMessages([
            'no_register' => 'Nomor register asesor sudah dipakai.',
        ]));

        return DB::transaction(function () use ($data, $admin, $institution, $nik, $nikHash): AsesorInvitation {
            $user = User::create([
                'institution_id' => $institution,
                'name' => $data['nama'],
                'nik' => $nik,
                'email' => $data['email'],
                'password' => Str::password(24),
                'status' => 'aktif',
            ]);

            $user->assignRole('asesor');
            $user->institutions()->attach($institution, ['role' => 'asesor']);

            $asesor = Asesor::create([
                'institution_id' => $institution,
                'user_id' => $user->id,
                'no_register' => $data['no_register'],
                'bidang_keahlian' => $data['bidang_keahlian'] ?? null,
                'kebangsaan' => 'Indonesia',
                'status' => 'aktif',
            ]);

            $invitation = AsesorInvitation::create([
                'institution_id' => $institution,
                'invited_by' => $admin->id,
                'asesor_id' => $asesor->id,
                'nik_hash' => $nikHash,
                'nama' => $data['nama'],
                'email' => $data['email'],
                'no_register' => $data['no_register'],
                'bidang_keahlian' => $data['bidang_keahlian'] ?? null,
                'status' => AsesorInvitation::STATUS_MENUNGGU,
                'expires_at' => now()->addDays(7),
            ]);

            activity('asesor')
                ->causedBy($admin)
                ->performedOn($asesor)
                ->withProperties(['invitation_id' => $invitation->id])
                ->log('Asesor diundang menjadi anggota lembaga.');

            return $invitation;
        });
    }

    /**
     * Antrekan email aktivasi ke outbox (dipanggil setelah commit transaksi).
     */
    public function sendActivation(AsesorInvitation $invitation): void
    {
        $user = $invitation->asesor?->user;
        if ($user === null) {
            return;
        }

        $url = URL::temporarySignedRoute('asesi.activate', now()->addDays(7), ['user' => $user->id]);

        $this->outbox->queue(
            $user,
            'undangan_asesor',
            'Undangan Keanggotaan Asesor — '.config('app.name'),
            sprintf(
                "Anda diundang menjadi Asesor pada lembaga kami. Aktifkan akun Anda dengan menetapkan kata sandi melalui tautan berikut (berlaku 7 hari):\n\n%s",
                $url,
            ),
            $invitation,
        );
    }
}
