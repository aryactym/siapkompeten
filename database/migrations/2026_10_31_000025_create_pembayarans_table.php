<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->unsignedBigInteger('total')->default(0);
            $table->foreignUuid('rekening_bank_id')->nullable()->constrained('rekening_banks')->nullOnDelete();
            $table->string('bukti_path')->nullable();
            $table->string('bukti_original_name')->nullable();
            $table->string('bukti_mime', 100)->nullable();
            $table->unsignedBigInteger('bukti_size')->nullable();
            $table->date('tanggal_bayar')->nullable();
            $table->enum('status', ['menunggu', 'valid', 'tidak_valid'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->foreignUuid('divalidasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('divalidasi_pada')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'pendaftaran_id']);
            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'rekening_bank_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
