<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apl02_jawabans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('penilaian_id')->constrained('apl02_penilaians')->cascadeOnDelete();
            $table->foreignUuid('kuk_id')->constrained('kriteria_unjuk_kerjas')->restrictOnDelete();
            $table->boolean('jawaban_asesi')->default(false);
            $table->text('bukti_asesi')->nullable();
            $table->text('bukti_asesor')->nullable();
            $table->boolean('valid')->default(false);
            $table->boolean('autentik')->default(false);
            $table->boolean('terkini')->default(false);
            $table->boolean('memadai')->default(false);
            $table->timestamps();

            $table->unique(['institution_id', 'penilaian_id', 'kuk_id']);
            $table->index(['institution_id', 'kuk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apl02_jawabans');
    }
};
