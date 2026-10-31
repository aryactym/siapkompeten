<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekening_banks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('nama_bank', 60);
            $table->string('no_rekening', 40);
            $table->string('atas_nama', 120);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'no_rekening']);
            $table->index(['institution_id', 'is_aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_banks');
    }
};
