<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak support ALTER COLUMN untuk enum, gunakan raw SQL
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE hasil_asesmen_metodes 
                SET metode = 'demonstrasi_simulasi' 
                WHERE metode = 'demonstrasi'
            ");
        } else {
            Schema::table('hasil_asesmen_metodes', function (Blueprint $table) {
                $table->enum('metode', [
                    'observasi',
                    'demonstrasi_simulasi',
                    'tes_lisan',
                    'tes_tertulis',
                    'wawancara',
                    'portofolio',
                ])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE hasil_asesmen_metodes 
                SET metode = 'demonstrasi' 
                WHERE metode = 'demonstrasi_simulasi'
            ");
        }
    }
};
