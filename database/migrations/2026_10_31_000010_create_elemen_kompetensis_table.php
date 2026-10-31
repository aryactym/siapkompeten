<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elemen_kompetensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('unit_kompetensi_id')->constrained('unit_kompetensis')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->text('nama_elemen');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_kompetensi_id', 'urutan']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elemen_kompetensis');
    }
};
