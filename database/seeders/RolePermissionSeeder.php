<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::firstOrCreate(['name' => 'manage_soal']);
        Permission::firstOrCreate(['name' => 'manage_peserta']);
        Permission::firstOrCreate(['name' => 'manage_ujian']);

        // Create Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $peserta    = Role::firstOrCreate(['name' => 'peserta']);

        // Super Admin mendapat semua permission
        $superAdmin->syncPermissions(Permission::all());

        $this->command->info('✅ Roles dan Permissions berhasil dibuat/diperbarui.');
    }
}
