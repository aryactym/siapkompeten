<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validasi_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('aspek_validitas')->nullable();
            $table->text('aspek_reliabilitas')->nullable();
            $table->text('aspek_keadilan')->nullable();
            $table->text('aspek_kesesuaian')->nullable();
            $table->enum('keputusan', ['disetujui', 'revisi', 'ditolak'])->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('pendaftaran_id');
            $table->index(['institution_id', 'status'], 'validasi_institusi_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validasi_asesmens');
    }
};
