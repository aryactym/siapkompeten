<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('asesor_id')->constrained('asesors')->restrictOnDelete();
            $table->enum('pendekatan', ['observasi', 'demonstrasi_simulasi', 'tes_lisan', 'tes_tertulis', 'wawancara', 'portofolio']);
            $table->text('tujuan');
            $table->text('ruang_lingkup');
            $table->date('tanggal_asesmen');
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pendaftaran_id'], 'rencana_pendaftaran_unique');
            $table->index(['institution_id', 'pendaftaran_id', 'status'], 'rencana_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_asesmens');
    }
};
