<?php

namespace App\Actions;

use App\Models\Asesi;
use App\Models\User;
use App\Services\NotificationOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Buat akun User baru sekaligus profil Asesi secara manual oleh admin.
 *
 * Menetapkan sumber='admin', no_registrasi otomatis, dan mengirim
 * tautan aktivasi ke email asesi yang baru dibuat.
 */
final class CreateAsesiManual
{
    public function __construct(private readonly NotificationOutbox $outbox) {}

    /**
     * @param  array{
     *     name: string,
     *     nik: string,
     *     email: string,
     *     phone: string,
     *     jenis_kelamin: ?string,
     *     tempat_lahir: ?string,
     *     tanggal_lahir: ?string,
     *     kebangsaan: string,
     *     alamat_rumah: ?string,
     *     kecamatan_code: ?string,
     *     kota_code: ?string,
     *     provinsi_code: ?string,
     *     kode_pos: ?string,
     *     telp_rumah: ?string,
     *     telp_kantor: ?string,
     *     pendidikan_terakhir: ?string,
     *     pekerjaan_instansi: ?string,
     *     jabatan: ?string,
     *     alamat_kantor: ?string,
     * } $data
     */
    public function execute(array $data, User $admin): Asesi
    {
        $institutionId = (string) $admin->institution_id;

        $asesi = DB::transaction(function () use ($data, $admin, $institutionId): Asesi {
            $user = User::create([
                'institution_id' => $institutionId,
                'name' => $data['name'],
                'nik' => $data['nik'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Str::password(24),
                'status' => 'aktif',
            ]);

            $user->assignRole('asesi');
            $user->institutions()->attach($institutionId, ['role' => 'asesi']);

            $asesi = Asesi::create([
                'institution_id' => $institutionId,
                'user_id' => $user->id,
                'no_registrasi' => $this->generateNoRegistrasi($institutionId),
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'kebangsaan' => $data['kebangsaan'] ?? 'Indonesia',
                'alamat_rumah' => $data['alamat_rumah'] ?? null,
                'kecamatan_code' => $data['kecamatan_code'] ?? null,
                'kota_code' => $data['kota_code'] ?? null,
                'provinsi_code' => $data['provinsi_code'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
                'telp_rumah' => $data['telp_rumah'] ?? null,
                'telp_kantor' => $data['telp_kantor'] ?? null,
                'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? null,
                'pekerjaan_instansi' => $data['pekerjaan_instansi'] ?? null,
                'jabatan' => $data['jabatan'] ?? null,
                'alamat_kantor' => $data['alamat_kantor'] ?? null,
                'sumber' => 'admin',
                'status_verifikasi' => 'belum_terverifikasi',
                'status_kompetensi' => 'belum_asesmen',
            ]);

            activity('asesi')
                ->causedBy($admin)
                ->performedOn($asesi)
                ->log('Asesi ditambahkan secara manual oleh admin.');

            return $asesi;
        });

        $this->sendActivationLink($asesi->user);

        return $asesi;
    }

    /**
     * Buat no_registrasi unik dengan format YYYYMMDDnnnn.
     */
    private function generateNoRegistrasi(string $institutionId): string
    {
        $prefix = now()->format('Ymd');
        $sequence = Asesi::withoutTenantScope()
            ->where('institution_id', $institutionId)
            ->where('no_registrasi', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (
            Asesi::withoutTenantScope()
                ->where('institution_id', $institutionId)
                ->where('no_registrasi', $number)
                ->exists()
        );

        return $number;
    }

    private function sendActivationLink(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'asesi.activate',
            now()->addDays(7),
            ['user' => $user->id],
        );

        $this->outbox->queue(
            $user,
            'aktivasi_akun',
            'Aktivasi Akun SiapKompeten',
            sprintf(
                'Akun Anda telah dibuat oleh admin LSP. Atur kata sandi melalui tautan berikut (berlaku 7 hari): %s',
                $url,
            ),
            $user,
        );
    }
}
