<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apl02_penilaians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignUuid('asesor_id')->nullable()->constrained('asesors')->nullOnDelete();
            $table->enum('status', ['draf_asesi', 'diserahkan', 'diverifikasi'])->default('draf_asesi');
            $table->timestamp('diserahkan_pada')->nullable();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'pendaftaran_id']);
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apl02_penilaians');
    }
};
