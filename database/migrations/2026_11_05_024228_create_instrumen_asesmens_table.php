<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumen_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->enum('jenis', ['ia01', 'ia02', 'ia08', 'ia11']);
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->text('catatan')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'jenis'], 'instrumen_pendaftaran_jenis_unique');
            $table->index(['institution_id', 'jenis'], 'instrumen_institusi_jenis_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumen_asesmens');
    }
};
