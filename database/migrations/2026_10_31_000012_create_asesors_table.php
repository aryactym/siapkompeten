<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('no_register', 100);
            $table->string('no_lisensi', 100)->nullable();
            $table->date('lisensi_berlaku_hingga')->nullable();
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('kebangsaan', 100)->default('Indonesia');
            $table->text('alamat')->nullable();
            $table->char('province_code', 2)->nullable();
            $table->char('city_code', 4)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('pendidikan_terakhir', 100)->nullable();
            $table->string('bidang_keahlian', 150)->nullable();
            $table->string('institusi_asal', 200)->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('foto_path')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'user_id']);
            $table->unique(['institution_id', 'no_register']);
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesors');
    }
};
