<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bandings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('pengaju_id')->constrained('users')->restrictOnDelete();
            $table->text('alasan');
            $table->enum('status', ['diajukan', 'ditinjau', 'diterima', 'ditolak', 'selesai'])->default('diajukan');
            $table->text('keputusan')->nullable();
            $table->foreignUuid('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->timestamps();

            $table->unique('pendaftaran_id');
            $table->index(['institution_id', 'status'], 'banding_institusi_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bandings');
    }
};
