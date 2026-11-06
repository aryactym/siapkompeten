<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia05_soals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('ia05_asesmen_id')->constrained('ia05_asesmens')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('pertanyaan');
            $table->string('opsi_a', 1000);
            $table->string('opsi_b', 1000);
            $table->string('opsi_c', 1000)->nullable();
            $table->string('opsi_d', 1000)->nullable();
            $table->enum('kunci', ['a', 'b', 'c', 'd']);
            $table->enum('jawaban_asesi', ['a', 'b', 'c', 'd'])->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'ia05_asesmen_id'], 'ia05_soal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia05_soals');
    }
};
