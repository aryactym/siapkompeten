<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'superadmin@siapkompeten.test');
        $username = 'superadmin';

        $superAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'institution_id' => null, // Platform level user
                'name' => 'Super Administrator Platform',
                'username' => $username,
                'password' => env('SUPERADMIN_PASSWORD', 'password'),
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->assignRole('super-admin');
    }
}
