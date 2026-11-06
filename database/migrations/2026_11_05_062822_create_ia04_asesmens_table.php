<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia04_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->string('judul_kegiatan', 200)->nullable();
            $table->text('skenario')->nullable();
            $table->text('hal_dipersiapkan')->nullable();
            $table->string('waktu_persiapan', 100)->nullable();
            $table->text('hasil_dipresentasikan')->nullable();
            $table->string('waktu_presentasi', 100)->nullable();
            $table->text('umpan_balik')->nullable();
            $table->enum('rekomendasi', ['kompeten', 'belum_kompeten'])->nullable();
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('pendaftaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia04_asesmens');
    }
};
