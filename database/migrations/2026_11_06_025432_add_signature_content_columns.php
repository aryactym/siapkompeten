<?php

use App\Models\User;
use App\Support\SignatureImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom tanda tangan terenkripsi di DB dan pindahkan data lama dari disk.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->mediumText('signature_data')->nullable()->after('signature_path');
            $table->string('signature_mime', 32)->nullable()->after('signature_data');
            $table->string('signature_hash', 64)->nullable()->after('signature_mime');
            $table->timestamp('signature_uploaded_at')->nullable()->after('signature_hash');
        });

        Schema::table('institutions', function (Blueprint $table): void {
            $table->mediumText('tanda_tangan_data')->nullable()->after('tanda_tangan_path');
            $table->string('tanda_tangan_mime', 32)->nullable()->after('tanda_tangan_data');
            $table->string('tanda_tangan_hash', 64)->nullable()->after('tanda_tangan_mime');
            $table->timestamp('tanda_tangan_uploaded_at')->nullable()->after('tanda_tangan_hash');
        });

        $this->migrateLegacyUserSignatures();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['signature_data', 'signature_mime', 'signature_hash', 'signature_uploaded_at']);
        });

        Schema::table('institutions', function (Blueprint $table): void {
            $table->dropColumn(['tanda_tangan_data', 'tanda_tangan_mime', 'tanda_tangan_hash', 'tanda_tangan_uploaded_at']);
        });
    }

    private function migrateLegacyUserSignatures(): void
    {
        $publicRoot = realpath(storage_path('app/public'));

        if ($publicRoot === false) {
            throw new RuntimeException('Disk publik signatures tidak ditemukan.');
        }

        DB::transaction(function () use ($publicRoot): void {
            User::query()
                ->whereNotNull('signature_path')
                ->get()
                ->each(function (User $user) use ($publicRoot): void {
                    $fullPath = realpath($publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, (string) $user->signature_path));

                    if ($fullPath === false || ! str_starts_with($fullPath, $publicRoot.DIRECTORY_SEPARATOR) || ! is_file($fullPath)) {
                        throw new RuntimeException("Berkas tanda tangan pengguna {$user->id} tidak valid.");
                    }

                    $binary = file_get_contents($fullPath);
                    if ($binary === false || strlen($binary) > 2 * 1024 * 1024) {
                        throw new RuntimeException("Berkas tanda tangan pengguna {$user->id} gagal dibaca.");
                    }

                    $payload = SignatureImage::encodePng($binary);
                    $user->forceFill([
                        'signature_data' => $payload['data'],
                        'signature_mime' => 'image/png',
                        'signature_hash' => $payload['hash'],
                        'signature_uploaded_at' => now(),
                        'signature_path' => null,
                    ])->save();
                });
        });
    }
};
