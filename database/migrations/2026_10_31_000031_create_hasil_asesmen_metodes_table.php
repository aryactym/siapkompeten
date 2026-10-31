<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_asesmen_metodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('hasil_unit_id')->constrained('hasil_asesmen_units')->cascadeOnDelete();
            $table->enum('metode', ['observasi', 'tes_lisan', 'tes_tertulis']);
            $table->boolean('digunakan')->default(false);
            $table->boolean('bukti_langsung')->default(false);
            $table->boolean('bukti_tidak_langsung')->default(false);
            $table->boolean('bukti_tambahan')->default(false);
            $table->boolean('pencapaian')->default(false);
            $table->enum('keputusan', ['kompeten', 'belum_kompeten', 'asesmen_lanjut'])->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'hasil_unit_id', 'metode']);
            $table->index(['institution_id', 'metode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_asesmen_metodes');
    }
};
