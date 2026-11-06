<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumen_asesmen_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('instrumen_asesmen_id')->constrained('instrumen_asesmens')->cascadeOnDelete();
            $table->foreignUuid('unit_kompetensi_id')->nullable()->constrained('unit_kompetensis')->nullOnDelete();
            $table->foreignUuid('kuk_id')->constrained('kriteria_unjuk_kerjas')->restrictOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->enum('hasil', ['tercapai', 'belum_tercapai'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['instrumen_asesmen_id', 'kuk_id'], 'instrumen_item_kuk_unique');
            $table->index(['institution_id', 'instrumen_asesmen_id'], 'instrumen_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumen_asesmen_items');
    }
};
