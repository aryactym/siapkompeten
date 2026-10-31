<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skema_persyaratans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('skema_id')->constrained('skemas')->cascadeOnDelete();
            $table->string('nama_dokumen', 200);
            $table->text('keterangan')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['skema_id', 'urutan']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skema_persyaratans');
    }
};
