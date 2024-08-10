<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create roles with the 'api' guard
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        // Remove all role-permission relationships
        \DB::table('role_has_permissions')->delete();

        // Remove all permissions
        \DB::table('permissions')->delete();

        // Create permissions with the 'api' guard
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'api']);
        $manageCategories = Permission::firstOrCreate(['name' => 'manage_categories', 'guard_name' => 'api']);
        $manageProducts = Permission::firstOrCreate(['name' => 'manage_products', 'guard_name' => 'api']);

        // Assign permissions to roles
        $superadmin->givePermissionTo([$manageUsers, $manageCategories, $manageProducts]);
        $admin->givePermissionTo([$manageCategories, $manageProducts]);

        // Optionally assign roles to a user
        $user = User::first(); // Replace with a specific user ID if needed
        if ($user) {
            $user->assignRole('superadmin'); // or 'admin'
        }
    }
}
