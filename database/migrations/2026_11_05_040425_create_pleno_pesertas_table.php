<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pleno_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pleno_id')->constrained('plenos')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->enum('keputusan', ['kompeten', 'belum_kompeten'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pleno_id', 'pendaftaran_id']);
            $table->index(['institution_id', 'pleno_id'], 'pleno_peserta_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pleno_pesertas');
    }
};
