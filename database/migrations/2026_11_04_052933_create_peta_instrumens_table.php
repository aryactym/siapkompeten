<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peta_instrumens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('rencana_asesmen_unit_id')->constrained('rencana_asesmen_units')->cascadeOnDelete();
            $table->foreignUuid('kuk_id')->constrained('kriteria_unjuk_kerjas')->restrictOnDelete();
            $table->enum('jenis_instrumen', ['observasi', 'demonstrasi_simulasi', 'tes_lisan', 'tes_tertulis', 'wawancara', 'portofolio']);
            $table->enum('metode', ['observasi', 'demonstrasi_simulasi', 'tes_lisan', 'tes_tertulis', 'wawancara', 'portofolio'])->nullable();
            $table->text('bukti')->nullable();
            $table->integer('urutan')->default(1);
            $table->timestamps();

            $table->unique(['rencana_asesmen_unit_id', 'kuk_id'], 'peta_unit_kuk_unique');
            $table->index(['institution_id', 'rencana_asesmen_unit_id'], 'peta_unit_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peta_instrumens');
    }
};
