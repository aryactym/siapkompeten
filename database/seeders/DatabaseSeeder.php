<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed initial database: roles, super admin, demo institutions, and tenant admin.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
        ]);

        $utama = Institution::firstOrCreate(
            ['slug' => 'siap-kompeten'],
            ['name' => 'LSP Siap Kompeten', 'is_active' => true]
        );

        $mitra = Institution::firstOrCreate(
            ['slug' => 'mitra-nusantara'],
            ['name' => 'LSP Mitra Nusantara', 'is_active' => true]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@siapkompeten.test'],
            [
                'institution_id' => $utama->id,
                'name' => 'Admin SiapKompeten',
                'username' => 'adminlsp',
                'password' => 'password',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);

        $admin->institutions()->syncWithoutDetaching([
            $utama->id => ['role' => 'admin'],
            $mitra->id => ['role' => 'admin'],
        ]);

        // Demo Asesor
        $asesor = User::firstOrCreate(
            ['email' => 'asesor@siapkompeten.test'],
            [
                'institution_id' => $utama->id,
                'name' => 'Ir. Hendra Wijaya, M.Kom (Asesor)',
                'username' => 'asesorhendra',
                'nik' => '3201123456780001',
                'password' => 'password',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );
        $asesor->syncRoles(['asesor']);
        $asesor->institutions()->syncWithoutDetaching([
            $utama->id => ['role' => 'asesor'],
        ]);

        // Demo Asesi
        $asesi = User::firstOrCreate(
            ['email' => 'asesi@siapkompeten.test'],
            [
                'institution_id' => $utama->id,
                'name' => 'Budi Pratama (Asesi/Peserta)',
                'username' => 'budipratama',
                'nik' => '3201123456780002',
                'password' => 'password',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );
        $asesi->syncRoles(['asesi']);
        $asesi->institutions()->syncWithoutDetaching([
            $utama->id => ['role' => 'asesi'],
        ]);

        $this->call(MasterDataSeeder::class);
    }
}
