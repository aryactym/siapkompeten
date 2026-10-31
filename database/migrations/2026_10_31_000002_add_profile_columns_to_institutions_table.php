<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('nomor_lisensi', 100)->nullable()->after('name');
            $table->date('masa_berlaku_lisensi')->nullable()->after('nomor_lisensi');
            $table->enum('jenis_lsp', ['p1', 'p2', 'p3'])->default('p3')->after('masa_berlaku_lisensi');
            $table->string('email', 150)->nullable()->after('jenis_lsp');
            $table->string('telepon', 50)->nullable()->after('email');
            $table->text('alamat')->nullable()->after('telepon');
            $table->string('website', 200)->nullable()->after('alamat');
            $table->string('nama_ketua', 150)->nullable()->after('website');
            $table->string('stempel_path')->nullable()->after('nama_ketua');
            $table->string('tanda_tangan_path')->nullable()->after('stempel_path');
            $table->boolean('is_setup_completed')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_lisensi',
                'masa_berlaku_lisensi',
                'jenis_lsp',
                'email',
                'telepon',
                'alamat',
                'website',
                'nama_ketua',
                'stempel_path',
                'tanda_tangan_path',
                'is_setup_completed',
            ]);
        });
    }
};
