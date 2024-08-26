<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class WebRolesSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']); 

        $manageCategories = Permission::firstOrCreate(['name' => 'manage_categories', 'guard_name' => 'web']);
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);

        $admin->givePermissionTo([$manageCategories, $manageUsers]);
        $superadmin->givePermissionTo([$manageCategories, $manageUsers]);

        $user = User::first(); 
        if ($user) {
            $user->assignRole('admin'); 
        }
    }
}
