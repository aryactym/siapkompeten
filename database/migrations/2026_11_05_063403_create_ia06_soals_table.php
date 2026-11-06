<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia06_soals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('ia06_asesmen_id')->constrained('ia06_asesmens')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('pertanyaan');
            $table->text('kunci')->nullable();
            $table->text('jawaban_asesi')->nullable();
            $table->boolean('pencapaian')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'ia06_asesmen_id'], 'ia06_soal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia06_soals');
    }
};
