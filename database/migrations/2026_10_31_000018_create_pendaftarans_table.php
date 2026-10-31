<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('no_pendaftaran', 40);
            $table->foreignUuid('asesi_id')->constrained('asesis')->restrictOnDelete();
            $table->foreignUuid('skema_id')->constrained('skemas')->restrictOnDelete();
            $table->foreignUuid('jadwal_id')->nullable()->constrained('jadwal_asesmens')->nullOnDelete();
            $table->enum('status', [
                'draf',
                'dokumen_diajukan',
                'dokumen_ditolak',
                'menunggu_pembayaran',
                'pembayaran_diajukan',
                'menunggu_jadwal',
                'terjadwal',
                'asesmen_berjalan',
                'selesai',
                'ditolak',
                'dibatalkan',
            ])->default('draf');
            $table->text('catatan_penolakan')->nullable();
            $table->foreignUuid('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'no_pendaftaran']);
            $table->index(['institution_id', 'asesi_id', 'status']);
            $table->index(['institution_id', 'jadwal_id']);
            $table->index(['institution_id', 'skema_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftarans');
    }
};
