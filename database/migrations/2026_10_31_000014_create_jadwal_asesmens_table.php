<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('event_id')->constrained('event_asesmens')->restrictOnDelete();
            $table->foreignUuid('skema_id')->constrained('skemas')->restrictOnDelete();
            $table->foreignUuid('tuk_id')->constrained('tuks')->restrictOnDelete();
            $table->string('gelombang', 50)->nullable();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedSmallInteger('kuota')->default(1);
            $table->enum('lingkup_asesor', ['wilayah', 'nonwilayah'])->default('wilayah');
            $table->string('no_surat_tugas', 100)->nullable();
            $table->enum('status', ['draf', 'terbuka', 'berjalan', 'selesai', 'dibatalkan'])->default('draf');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'tanggal']);
            $table->index(['institution_id', 'skema_id', 'tuk_id']);
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_asesmens');
    }
};
