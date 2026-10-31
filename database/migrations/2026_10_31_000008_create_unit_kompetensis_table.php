<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_kompetensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('skkni_id')->nullable()->constrained('skknis')->nullOnDelete();
            $table->string('kode_unit', 100);
            $table->string('judul_unit', 255);
            $table->string('standar_kompetensi', 100)->default('SKKNI');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'kode_unit']);
            $table->index(['institution_id', 'skkni_id']);
            $table->index(['institution_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_kompetensis');
    }
};
