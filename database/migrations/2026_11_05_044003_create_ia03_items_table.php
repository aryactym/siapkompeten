<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia03_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('ia03_asesmen_id')->constrained('ia03_asesmens')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('pertanyaan');
            $table->text('tanggapan')->nullable();
            $table->boolean('pencapaian')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'ia03_asesmen_id'], 'ia03_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia03_items');
    }
};
