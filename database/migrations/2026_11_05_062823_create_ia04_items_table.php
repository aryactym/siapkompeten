<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia04_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('ia04_asesmen_id')->constrained('ia04_asesmens')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->string('lingkup', 200)->nullable();
            $table->text('pertanyaan');
            $table->text('tanggapan')->nullable();
            $table->boolean('pencapaian')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'ia04_asesmen_id'], 'ia04_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia04_items');
    }
};
