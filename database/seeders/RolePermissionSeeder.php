<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Company Management
            'company.view',
            'company.edit',
            'company.manage-users',
            'company.manage-settings',

            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.invite',

            // Customer Management (CRM)
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',

            // Lead Management
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.delete',
            'leads.convert',

            // Sales Management
            'quotes.view',
            'quotes.create',
            'quotes.edit',
            'quotes.delete',
            'quotes.convert',

            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'orders.fulfill',

            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.pay',

            // Inventory Management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',

            'purchase-orders.view',
            'purchase-orders.create',
            'purchase-orders.edit',
            'purchase-orders.delete',
            'purchase-orders.receive',

            // Financial Management
            'transactions.view',
            'transactions.create',
            'transactions.edit',
            'transactions.delete',

            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',
            'expenses.approve',

            'reports.view',
            'reports.create',
            'reports.export',

            // Project Management
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',

            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.complete',

            'timesheets.view',
            'timesheets.create',
            'timesheets.edit',
            'timesheets.approve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            [
                'name' => 'super-admin',
                'permissions' => Permission::all(),
                'description' => 'Full system access'
            ],
            [
                'name' => 'company-owner',
                'permissions' => Permission::whereIn('name', [
                    'company.view', 'company.edit', 'company.manage-users', 'company.manage-settings',
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.invite',
                    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                    'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.convert',
                    'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.delete', 'quotes.convert',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.fulfill',
                    'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.pay',
                    'products.view', 'products.create', 'products.edit', 'products.delete',
                    'inventory.view', 'inventory.adjust', 'inventory.transfer',
                    'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit', 'purchase-orders.delete', 'purchase-orders.receive',
                    'transactions.view', 'transactions.create', 'transactions.edit', 'transactions.delete',
                    'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.approve',
                    'reports.view', 'reports.create', 'reports.export',
                    'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.complete',
                    'timesheets.view', 'timesheets.create', 'timesheets.edit', 'timesheets.approve',
                ])->get(),
                'description' => 'Full access to company data'
            ],
            [
                'name' => 'admin',
                'permissions' => Permission::whereIn('name', [
                    'company.view',
                    'users.view', 'users.create', 'users.edit', 'users.invite',
                    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                    'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.convert',
                    'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.delete', 'quotes.convert',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                    'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
                    'products.view', 'products.create', 'products.edit',
                    'inventory.view', 'inventory.adjust',
                    'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit', 'purchase-orders.delete',
                    'transactions.view', 'transactions.create', 'transactions.edit',
                    'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
                    'reports.view', 'reports.export',
                    'projects.view', 'projects.create', 'projects.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.complete',
                    'timesheets.view', 'timesheets.create', 'timesheets.edit',
                ])->get(),
                'description' => 'Administrative access within company'
            ],
            [
                'name' => 'manager',
                'permissions' => Permission::whereIn('name', [
                    'users.view',
                    'customers.view', 'customers.create', 'customers.edit',
                    'leads.view', 'leads.create', 'leads.edit', 'leads.convert',
                    'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.convert',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.fulfill',
                    'invoices.view', 'invoices.create', 'invoices.edit',
                    'products.view', 'products.create',
                    'inventory.view', 'inventory.adjust',
                    'purchase-orders.view', 'purchase-orders.create',
                    'transactions.view',
                    'expenses.view', 'expenses.create', 'expenses.edit',
                    'reports.view',
                    'projects.view', 'projects.create', 'projects.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit',
                    'timesheets.view', 'timesheets.create', 'timesheets.edit',
                ])->get(),
                'description' => 'Management access for team operations'
            ],
            [
                'name' => 'sales-rep',
                'permissions' => Permission::whereIn('name', [
                    'customers.view', 'customers.create', 'customers.edit',
                    'leads.view', 'leads.create', 'leads.edit', 'leads.convert',
                    'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.convert',
                    'orders.view', 'orders.create', 'orders.edit',
                    'invoices.view',
                    'products.view',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.complete',
                    'reports.view',
                ])->get(),
                'description' => 'Sales representative access'
            ],
            [
                'name' => 'employee',
                'permissions' => Permission::whereIn('name', [
                    'customers.view',
                    'leads.view', 'leads.create', 'leads.edit',
                    'quotes.view',
                    'orders.view', 'orders.create',
                    'products.view',
                    'inventory.view',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.complete',
                    'timesheets.view', 'timesheets.create', 'timesheets.edit',
                    'expenses.view', 'expenses.create', 'expenses.edit',
                    'reports.view',
                ])->get(),
                'description' => 'Basic employee access'
            ],
            [
                'name' => 'read-only',
                'permissions' => Permission::whereIn('name', [
                    'company.view',
                    'customers.view',
                    'leads.view',
                    'quotes.view',
                    'orders.view',
                    'invoices.view',
                    'products.view',
                    'inventory.view',
                    'transactions.view',
                    'expenses.view',
                    'reports.view',
                    'projects.view',
                    'tasks.view',
                    'timesheets.view',
                ])->get(),
                'description' => 'Read-only access to all modules'
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['name' => $roleData['name']]);
            $role->givePermissionTo($roleData['permissions']);
        }

        // Create default super admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@erp.local'],
            [
                'name' => 'ERP Administrator',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $user->assignRole('super-admin');

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Default admin user: admin@erp.local / password');
    }
}
