<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umpan_balik_asesmens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->json('komponen_json')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('disubmit_pada')->nullable();
            $table->foreignUuid('disubmit_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('pendaftaran_id');
            $table->index(['institution_id', 'disubmit_pada'], 'umpan_balik_institusi_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umpan_balik_asesmens');
    }
};
