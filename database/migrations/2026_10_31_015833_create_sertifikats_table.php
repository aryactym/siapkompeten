<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikats', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->restrictOnDelete();
            // Satu pendaftaran hanya menghasilkan satu sertifikat.
            $table->foreignUuid('pendaftaran_id')->unique()->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('asesi_id')->constrained('asesis')->cascadeOnDelete();
            $table->foreignUuid('skema_id')->constrained('skemas')->restrictOnDelete();
            // Nomor seri blangko kertas fisik BNSP (diisi admin dari blangko fisik).
            $table->string('no_seri_blangko', 100)->unique();
            // Nomor registrasi lisensi nasional (dibangkitkan sistem).
            $table->string('no_registrasi', 100)->unique();
            $table->date('tanggal_terbit');
            $table->date('berlaku_hingga')->nullable();
            $table->enum('status', ['aktif', 'dibatalkan', 'kedaluwarsa'])->default('aktif');
            $table->foreignUuid('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('qr_token', 64)->unique();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'asesi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikats');
    }
};
