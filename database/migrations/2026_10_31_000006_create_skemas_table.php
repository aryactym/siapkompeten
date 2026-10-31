<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skemas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('kode_skema', 100);
            $table->string('nama_skema', 255);
            $table->string('nomor_skema', 150)->nullable();
            $table->enum('jenis', ['kkni', 'okupasi', 'klaster'])->default('okupasi');
            $table->unsignedTinyInteger('level_kkni')->nullable();
            $table->string('kategori', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('biaya', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'kode_skema']);
            $table->index(['institution_id', 'is_active']);
            $table->index(['institution_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skemas');
    }
};
