<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_asesmens', function (Blueprint $table) {
            $table->json('instrumen_terpilih')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_asesmens', function (Blueprint $table) {
            $table->dropColumn('instrumen_terpilih');
        });
    }
};
