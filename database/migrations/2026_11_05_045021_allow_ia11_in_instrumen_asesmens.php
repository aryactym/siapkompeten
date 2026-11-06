<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrumen_asesmens', function (Blueprint $table) {
            $table->enum('jenis', ['ia01', 'ia02', 'ia08', 'ia11'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('instrumen_asesmens', function (Blueprint $table) {
            $table->enum('jenis', ['ia01', 'ia02', 'ia08'])->change();
        });
    }
};
