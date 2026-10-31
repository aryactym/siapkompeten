<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_dokumens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('skema_persyaratan_id')->constrained('skema_persyaratans')->restrictOnDelete();
            $table->string('file_path')->nullable();
            $table->string('file_mime', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('nilai_teks', 255)->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->foreignUuid('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'pendaftaran_id', 'skema_persyaratan_id'], 'pendaftaran_dokumen_unik');
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_dokumens');
    }
};
