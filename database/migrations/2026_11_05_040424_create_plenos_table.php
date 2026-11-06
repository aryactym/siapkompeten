<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plenos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('jadwal_id')->constrained('jadwal_asesmens')->cascadeOnDelete();
            $table->string('nomor_ba', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->enum('keputusan', ['disetujui', 'ditolak'])->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('jadwal_id');
            $table->index(['institution_id', 'status'], 'pleno_institusi_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plenos');
    }
};
