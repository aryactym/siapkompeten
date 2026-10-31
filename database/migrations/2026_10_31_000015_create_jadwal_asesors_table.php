<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_asesors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('jadwal_id')->constrained('jadwal_asesmens')->cascadeOnDelete();
            $table->foreignUuid('asesor_id')->constrained('asesors')->restrictOnDelete();
            $table->enum('peran', ['utama', 'pendamping', 'observer'])->default('utama');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'jadwal_id', 'asesor_id']);
            $table->index(['institution_id', 'asesor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_asesors');
    }
};
