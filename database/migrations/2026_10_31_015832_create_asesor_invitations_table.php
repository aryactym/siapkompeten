<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->restrictOnDelete();
            $table->foreignUuid('invited_by')->constrained('users')->restrictOnDelete();
            // Asesor yang dibuat dari undangan ini (nullable sampai user dibuat).
            $table->foreignUuid('asesor_id')->nullable()->constrained('asesors')->cascadeOnDelete();
            $table->string('nik_hash', 64)->nullable();
            $table->string('nama', 150);
            $table->string('email', 150);
            $table->string('no_register', 100);
            $table->string('bidang_keahlian', 150)->nullable();
            $table->enum('status', ['menunggu', 'diterima', 'dibatalkan', 'kedaluwarsa'])->default('menunggu');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor_invitations');
    }
};
