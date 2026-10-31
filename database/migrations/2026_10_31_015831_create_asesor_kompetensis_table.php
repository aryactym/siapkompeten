<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor_kompetensis', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->restrictOnDelete();
            $table->foreignUuid('asesor_id')->constrained('asesors')->cascadeOnDelete();
            $table->foreignUuid('skema_id')->nullable()->constrained('skemas')->nullOnDelete();
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_hingga')->nullable();
            $table->timestamps();

            // Satu skema hanya boleh muncul sekali per asesor dalam satu LSP.
            $table->unique(['institution_id', 'asesor_id', 'skema_id']);
            $table->index(['institution_id', 'skema_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_kompetensis');
    }
};
