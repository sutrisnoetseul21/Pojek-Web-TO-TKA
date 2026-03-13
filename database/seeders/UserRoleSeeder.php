<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin System',
                'email' => 'superadmin@excellent.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        // Create Admin SD
        User::updateOrCreate(
            ['username' => 'adminsd'],
            [
                'name' => 'Admin Jenjang SD',
                'email' => 'adminsd@excellent.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jenjang' => 'SD',
            ]
        );

        // Create Admin SMP
        User::updateOrCreate(
            ['username' => 'adminsmp'],
            [
                'name' => 'Admin Jenjang SMP',
                'email' => 'adminsmp@excellent.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jenjang' => 'SMP',
            ]
        );
    }
}
