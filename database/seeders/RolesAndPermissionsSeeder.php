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
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        \DB::table('role_has_permissions')->delete();

        \DB::table('permissions')->delete();

        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'api']);
        $manageCategories = Permission::firstOrCreate(['name' => 'manage_categories', 'guard_name' => 'api']);
        $manageProducts = Permission::firstOrCreate(['name' => 'manage_products', 'guard_name' => 'api']);

        $superadmin->givePermissionTo([$manageUsers, $manageCategories, $manageProducts]);
        $admin->givePermissionTo([$manageCategories, $manageProducts]);

        $user = User::first();
        if ($user) {
            $user->assignRole('superadmin');
        }
    }
}
