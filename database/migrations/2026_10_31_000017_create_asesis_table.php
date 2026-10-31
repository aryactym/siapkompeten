<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('no_registrasi', 40);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('kebangsaan', 100)->default('Indonesia');
            $table->text('alamat_rumah')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->char('kecamatan_code', 7)->nullable();
            $table->char('kota_code', 4)->nullable();
            $table->char('provinsi_code', 2)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('telp_rumah', 50)->nullable();
            $table->string('telp_kantor', 50)->nullable();
            $table->string('pendidikan_terakhir', 100)->nullable();
            $table->string('pekerjaan_instansi', 200)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->text('alamat_kantor')->nullable();
            $table->string('foto_path')->nullable();
            $table->enum('status_verifikasi', ['belum_terverifikasi', 'terverifikasi'])->default('belum_terverifikasi');
            $table->enum('status_kompetensi', ['belum_asesmen', 'kompeten', 'belum_kompeten'])->default('belum_asesmen');
            $table->enum('sumber', ['mandiri', 'impor', 'admin'])->default('mandiri');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'user_id']);
            $table->unique(['institution_id', 'no_registrasi']);
            $table->index(['institution_id', 'provinsi_code', 'kota_code']);
            $table->index(['institution_id', 'status_verifikasi', 'status_kompetensi']);

            $table->foreign('provinsi_code')->references('code')->on(config('laravolt.indonesia.table_prefix', 'indonesia_').'provinces')->nullOnDelete();
            $table->foreign('kota_code')->references('code')->on(config('laravolt.indonesia.table_prefix', 'indonesia_').'cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesis');
    }
};
