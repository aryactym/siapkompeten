<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_asesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('jadwal_id')->nullable()->constrained('jadwal_asesmens')->nullOnDelete();
            $table->enum('status', ['hadir', 'izin', 'tidak_hadir']);
            $table->text('catatan')->nullable();
            $table->foreignUuid('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dicatat_pada')->nullable();
            $table->timestamp('terkunci_pada')->nullable();
            $table->timestamps();

            $table->unique('pendaftaran_id');
            $table->index(['institution_id', 'jadwal_id'], 'presensi_jadwal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_asesis');
    }
};
