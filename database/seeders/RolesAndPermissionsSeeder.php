<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // delete all users
        User::truncate();

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'view roles',
            'view permissions',
            'create roles',
            'create permissions',
            'edit roles',
            'edit permissions',
            'delete roles',
            'delete permissions',
            'sync permissions',
            'view home',
            'view dashboard',

            'view master companies',
            'create master companies',
            'edit master companies',
            'delete master companies',

            'view master subscribtions',
            'create master subscribtions',
            'edit master subscribtions',
            'delete master subscribtions',

            'view master branches',
            'create master branches',
            'edit master branches',
            'delete master branches',

            'view master employees',
            'create master employees',
            'edit master employees',
            'delete master employees',

            'view master salaries',
            'create master salaries',
            'edit master salaries',
            'delete master salaries',

            'view master track records',
            'create master track records',
            'edit master track records',
            'delete master track records',

            'view master  kpi',
            'create master  kpi',
            'edit master  kpi',
            'delete master  kpi',

            'view master schedules',
            'create master schedules',
            'edit master schedules',
            'delete master schedules',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'HR']);
        $adminRole->givePermissionTo([
            'view home',
            'view dashboard',
        ]);

        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerRole->givePermissionTo([
            'view home',
            'view dashboard',
        ]);

        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@hrdsiltrans.com',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('superadmin123'),
        ]);

        $superAdmin->assignRole('Super Admin');

        $admin = User::firstOrCreate([
            'email' => 'hr@hrdsiltrans.com',
        ], [
            'name' => 'HR User',
            'password' => bcrypt('hr123456'),
        ]);

        $admin->assignRole('HR');
    }
}
