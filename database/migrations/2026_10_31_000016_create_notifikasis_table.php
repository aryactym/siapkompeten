<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->enum('channel', ['sms', 'email', 'whatsapp', 'inapp'])->default('email');
            $table->string('tujuan', 150);
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul', 200)->nullable();
            $table->text('pesan');
            $table->string('tipe', 60);
            $table->nullableUuidMorphs('related');
            $table->enum('status', ['menunggu', 'terkirim', 'gagal'])->default('menunggu');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('provider_ref', 150)->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'status', 'created_at']);
            $table->index(['institution_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
