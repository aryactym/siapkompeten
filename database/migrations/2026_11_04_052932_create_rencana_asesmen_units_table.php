<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_asesmen_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('rencana_asesmen_id')->constrained('rencana_asesmens')->cascadeOnDelete();
            $table->foreignUuid('unit_kompetensi_id')->constrained('unit_kompetensis')->restrictOnDelete();
            $table->integer('urutan')->default(1);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['rencana_asesmen_id', 'unit_kompetensi_id'], 'rencana_unit_unique');
            $table->index(['institution_id', 'rencana_asesmen_id'], 'rencana_unit_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_asesmen_units');
    }
};
