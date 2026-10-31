<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('kategori', 50);
            $table->string('judul', 255);
            $table->string('sub_judul', 255)->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->time('jam_terbit')->nullable();
            $table->longText('isi')->nullable();
            $table->string('gambar_path')->nullable();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'kategori']);
            $table->index(['institution_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_contents');
    }
};
