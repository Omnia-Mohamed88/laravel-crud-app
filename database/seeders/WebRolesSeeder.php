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
        // Create roles with the 'web' guard
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Create permissions with the 'web' guard
        $manageCategories = Permission::firstOrCreate(['name' => 'manage_categories', 'guard_name' => 'web']);
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);

        // Assign permissions to roles
        $admin->givePermissionTo([$manageCategories, $manageUsers]);
        $superadmin->givePermissionTo([$manageCategories, $manageUsers]);

        // Optionally assign the role to a user
        $user = User::first(); // Adjust this if necessary
        if ($user) {
            $user->assignRole('admin'); // Ensure the user is assigned the 'admin' role
        }
    }
}
