<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->smallInteger('tahun');
            $table->unsignedTinyInteger('periode')->nullable();
            $table->string('tempat_ringkas', 200)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_asesmens');
    }
};
