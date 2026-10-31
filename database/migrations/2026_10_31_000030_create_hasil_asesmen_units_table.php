<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_asesmen_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('hasil_id')->constrained('hasil_asesmens')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->constrained('unit_kompetensis')->restrictOnDelete();
            $table->enum('keputusan', ['kompeten', 'belum_kompeten', 'asesmen_lanjut'])->nullable();
            $table->boolean('pencapaian')->default(false);
            $table->timestamps();

            $table->unique(['institution_id', 'hasil_id', 'unit_id']);
            $table->index(['institution_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_asesmen_units');
    }
};
