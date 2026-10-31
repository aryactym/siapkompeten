<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria_unjuk_kerjas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('elemen_kompetensi_id')->constrained('elemen_kompetensis')->cascadeOnDelete();
            $table->string('urutan', 20)->default('1.1');
            $table->text('pernyataan_kuk');
            $table->text('pertanyaan_observasi')->nullable();
            $table->text('pertanyaan_lisan')->nullable();
            $table->text('pertanyaan_tertulis')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['elemen_kompetensi_id', 'urutan']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_unjuk_kerjas');
    }
};
