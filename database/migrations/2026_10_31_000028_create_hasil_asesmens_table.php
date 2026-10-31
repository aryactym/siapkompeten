<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('asesor_id')->nullable()->constrained('asesors')->nullOnDelete();
            $table->enum('rekomendasi', ['direkomendasikan', 'tidak_direkomendasikan'])->nullable();
            $table->decimal('persen_kompeten', 5, 2)->default(0);
            $table->boolean('umpan_balik_diberikan')->default(false);
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['institution_id', 'pendaftaran_id']);
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_asesmens');
    }
};
