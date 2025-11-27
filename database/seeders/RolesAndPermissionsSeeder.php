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



        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [

            // Home
            'view home',
            'create home',
            'update home',
            'delete home',

            // Dashboard
            'view dashboard',
            'create dashboard',
            'update dashboard',
            'delete dashboard',

            // Master Data (module)
            'view master data',
            'create master data',
            'update master data',
            'delete master data',

            // Master Data → Children
            'view master data roles',
            'create master data roles',
            'update master data roles',
            'delete master data roles',
            'view master data permissions',
            'create master data permissions',
            'update master data permissions',
            'delete master data permissions',
            'view master data companies',
            'create master data companies',
            'update master data companies',
            'delete master data companies',
            'view master data subscriptions',
            'create master data subscriptions',
            'update master data subscriptions',
            'delete master data subscriptions',
            'view master data branches',
            'create master data branches',
            'update master data branches',
            'delete master data branches',
            'view master data employees',
            'create master data employees',
            'update master data employees',
            'delete master data employees',
            'view master data salaries',
            'create master data salaries',
            'update master data salaries',
            'delete master data salaries',
            'view master data track records',
            'create master data track records',
            'update master data track records',
            'delete master data track records',
            'view master data kpi',
            'create master data kpi',
            'update master data kpi',
            'delete master data kpi',
            'view master data schedules',
            'create master data schedules',
            'update master data schedules',
            'delete master data schedules',
            'view master data overtimes',
            'create master data overtimes',
            'update master data overtimes',
            'delete master data overtimes',
            'view master data balances',
            'create master data balances',
            'update master data balances',
            'delete master data balances',
            'view master data announcements',
            'create master data announcements',
            'update master data announcements',
            'delete master data announcements',
            'view master data vacancies',
            'create master data vacancies',
            'update master data vacancies',
            'delete master data vacancies',
            'view master data applications',
            'create master data applications',
            'update master data applications',
            'delete master data applications',

            // Transactions (module)
            'view transactions',
            'create transactions',
            'update transactions',
            'delete transactions',

            // Transactions → Children
            'view transactions attendances',
            'create transactions attendances',
            'update transactions attendances',
            'delete transactions attendances',
            'view transactions leaves',
            'create transactions leaves',
            'update transactions leaves',
            'delete transactions leaves',
            'view transactions cuts',
            'create transactions cuts',
            'update transactions cuts',
            'delete transactions cuts',
            'view transactions debts',
            'create transactions debts',
            'update transactions debts',
            'delete transactions debts',
            'view transactions reimbursements',
            'create transactions reimbursements',
            'update transactions reimbursements',
            'delete transactions reimbursements',
            'view transactions kpi',
            'create transactions kpi',
            'update transactions kpi',
            'delete transactions kpi',

            // Reports (module)
            'view reports',
            'create reports',
            'update reports',
            'delete reports',

            // Reports → Children
            'view reports branches',
            'create reports branches',
            'update reports branches',
            'delete reports branches',
            'view reports employees',
            'create reports employees',
            'update reports employees',
            'delete reports employees',
            'view reports applications',
            'create reports applications',
            'update reports applications',
            'delete reports applications',
            'view reports attendances',
            'create reports attendances',
            'update reports attendances',
            'delete reports attendances',
            'view reports today attendances',
            'create reports today attendances',
            'update reports today attendances',
            'delete reports today attendances',
            'view reports leaves',
            'create reports leaves',
            'update reports leaves',
            'delete reports leaves',
            'view reports intensive',
            'create reports intensive',
            'update reports intensive',
            'delete reports intensive',
            'view reports overtimes',
            'create reports overtimes',
            'update reports overtimes',
            'delete reports overtimes',
            'view reports cuts',
            'create reports cuts',
            'update reports cuts',
            'delete reports cuts',
            'view reports debts',
            'create reports debts',
            'update reports debts',
            'delete reports debts',
            'view reports reimbursements',
            'create reports reimbursements',
            'update reports reimbursements',
            'delete reports reimbursements',
            'view reports salaries',
            'create reports salaries',
            'update reports salaries',
            'delete reports salaries',
            'view reports transfers',
            'create reports transfers',
            'update reports transfers',
            'delete reports transfers',
            'view reports tax',
            'create reports tax',
            'update reports tax',
            'delete reports tax',
            'view reports kpi',
            'create reports kpi',
            'update reports kpi',
            'delete reports kpi',

            // Tools (module)
            'view tools',
            'create tools',
            'update tools',
            'delete tools',

            // Tools → Children
            'view tools id card',
            'create tools id card',
            'update tools id card',
            'delete tools id card',
            'view tools reset pin',
            'create tools reset pin',
            'update tools reset pin',
            'delete tools reset pin',
            'view tools machine card',
            'create tools machine card',
            'update tools machine card',
            'delete tools machine card',
            'view tools change log',
            'create tools change log',
            'update tools change log',
            'delete tools change log',
            'view tools access log',
            'create tools access log',
            'update tools access log',
            'delete tools access log',

            // Settings
            'view settings',
            'create settings',
            'update settings',
            'delete settings',

            // Attendances (standalone)
            'view attendances',
            'create attendances',
            'update attendances',
            'delete attendances',

            // E-slip
            'view e-slip',
            'create e-slip',
            'update e-slip',
            'delete e-slip',

            // Update Pin
            'view update pin',
            'create update pin',
            'update update pin',
            'delete update pin',

            'view master data home',
            'create master data home',
            'edit master data home',
            'delete master data home',
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

        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);
        $employeeRole->givePermissionTo([
            'view home',
            'view attendances',
            'view e-slip',
            'view update pin',
        ]);

        $employee = User::firstOrCreate([
            'email' => 'employee@hrdsiltrans.com',
        ], [
            'name' => 'Employee User',
            'password' => bcrypt('employee123'),
        ]);

        $employee->assignRole('Employee');
    }
}
