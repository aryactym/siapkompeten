<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biayas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('skkni_id')->nullable()->constrained('skknis')->nullOnDelete();
            $table->foreignUuid('skema_id')->constrained('skemas')->restrictOnDelete();
            $table->string('jenis_biaya', 80);
            $table->unsignedBigInteger('nominal')->default(0);
            $table->enum('tanggung_jawab', ['peserta', 'fasilitator'])->default('peserta');
            $table->timestamps();

            $table->unique(['institution_id', 'skema_id', 'jenis_biaya']);
            $table->index(['institution_id', 'skema_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biayas');
    }
};
