<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia10_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->string('nama_pihak_ketiga', 200)->nullable();
            $table->string('jabatan', 200)->nullable();
            $table->string('instansi', 200)->nullable();
            $table->text('klarifikasi')->nullable();
            $table->enum('status', ['draf', 'final'])->default('draf');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUuid('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('pendaftaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia10_asesmens');
    }
};
