<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('kode_tuk', 50);
            $table->string('nama_tuk', 200);
            $table->enum('jenis', ['sewaktu', 'mandiri', 'tempat_kerja'])->default('sewaktu');
            $table->string('penanggung_jawab', 150);
            $table->string('telepon', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->char('province_code', 2)->nullable();
            $table->char('city_code', 4)->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'kode_tuk']);
            $table->foreign('province_code')->references('code')->on(config('laravolt.indonesia.table_prefix', 'indonesia_').'provinces')->nullOnDelete();
            $table->foreign('city_code')->references('code')->on(config('laravolt.indonesia.table_prefix', 'indonesia_').'cities')->nullOnDelete();
            $table->index(['institution_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuks');
    }
};
