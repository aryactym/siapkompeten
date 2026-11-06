<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tinjauan_proses_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('jadwal_id')->constrained('jadwal_asesmens')->cascadeOnDelete();
            $table->foreignUuid('asesor_id')->nullable()->constrained('asesors')->nullOnDelete();
            $table->json('proses_json')->nullable();
            $table->text('rekomendasi_proses')->nullable();
            $table->json('dimensi_json')->nullable();
            $table->text('rekomendasi_dimensi')->nullable();
            $table->text('komentar')->nullable();
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('jadwal_id');
            $table->index(['institution_id', 'status'], 'tinjauan_institusi_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tinjauan_proses_asesmens');
    }
};
