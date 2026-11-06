<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persetujuan_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->string('jenis', 10);
            $table->string('status', 20)->default('menunggu');
            $table->timestamp('disetujui_pada')->nullable();
            $table->foreignUuid('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('dokumen_hash', 64)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'jenis']);
            $table->index(['institution_id', 'pendaftaran_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_asesmens');
    }
};
