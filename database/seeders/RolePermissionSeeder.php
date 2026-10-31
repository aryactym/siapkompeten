<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'super-admin',
            'admin',
            'asesor',
            'asesi',
            'pimpinan',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Define baseline permissions
        $permissions = [
            'manage-platform',
            'manage-lsp',
            'manage-users',
            'manage-standar',
            'manage-jadwal',
            'manage-asesi',
            'verify-documents',
            'assess-asesi',
            'view-reports',
            'export-reports',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Assign permissions to admin
        $adminRole = Role::findByName('admin', 'web');
        $adminRole->syncPermissions([
            'manage-lsp',
            'manage-users',
            'manage-standar',
            'manage-jadwal',
            'manage-asesi',
            'verify-documents',
            'view-reports',
            'export-reports',
        ]);

        // Assign permissions to asesor
        $asesorRole = Role::findByName('asesor', 'web');
        $asesorRole->syncPermissions([
            'assess-asesi',
        ]);

        // Assign permissions to pimpinan
        $pimpinanRole = Role::findByName('pimpinan', 'web');
        $pimpinanRole->syncPermissions([
            'view-reports',
            'export-reports',
        ]);
    }
}
