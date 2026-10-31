<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skema_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('skema_id')->constrained('skemas')->cascadeOnDelete();
            $table->foreignUuid('unit_kompetensi_id')->constrained('unit_kompetensis')->cascadeOnDelete();
            $table->enum('jenis', ['inti', 'pilihan'])->default('inti');
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();

            $table->unique(['skema_id', 'unit_kompetensi_id']);
            $table->index(['skema_id', 'urutan']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skema_units');
    }
};
