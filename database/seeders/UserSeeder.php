<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);
        
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('superadminpassword')
            ]
        );
        $superadmin->assignRole($superadminRole);
        
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('adminpassword')
            ]
        );
        $admin->assignRole($adminRole);
        
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('userpassword')
            ]
        );
        $user->assignRole($userRole);
    }
}
