<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Customer Management Permissions
        $customerPermissions = [
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',
            'manage-customer-contacts',
        ];

        foreach ($customerPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($customerPermissions);
        }

        if ($managerRole) {
            $managerRole->givePermissionTo([
                'view-customers',
                'create-customers',
                'edit-customers',
                'manage-customer-contacts',
            ]);
        }

        if ($employeeRole) {
            $employeeRole->givePermissionTo([
                'view-customers',
                'create-customers',
                'edit-customers',
            ]);
        }
    }
}
