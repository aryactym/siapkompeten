<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('jadwal_id')->constrained('jadwal_asesmens')->cascadeOnDelete();
            $table->foreignUuid('asesor_id')->nullable()->constrained('asesors')->nullOnDelete();
            $table->text('aspek_positif')->nullable();
            $table->text('aspek_negatif')->nullable();
            $table->text('pencatatan_penolakan')->nullable();
            $table->text('saran_perbaikan')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('jadwal_id');
            $table->index(['institution_id', 'status'], 'laporan_institusi_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_asesmens');
    }
};
