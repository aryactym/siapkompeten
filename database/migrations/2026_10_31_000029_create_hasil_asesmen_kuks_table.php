<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_asesmen_kuks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('hasil_id')->constrained('hasil_asesmens')->cascadeOnDelete();
            $table->foreignUuid('kuk_id')->constrained('kriteria_unjuk_kerjas')->restrictOnDelete();
            $table->boolean('pencapaian')->default(false);
            $table->string('bukti_no', 60)->nullable();
            $table->boolean('valid')->default(false);
            $table->boolean('autentik')->default(false);
            $table->boolean('terkini')->default(false);
            $table->boolean('memadai')->default(false);
            $table->timestamps();

            $table->unique(['institution_id', 'hasil_id', 'kuk_id']);
            $table->index(['institution_id', 'kuk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_asesmen_kuks');
    }
};
