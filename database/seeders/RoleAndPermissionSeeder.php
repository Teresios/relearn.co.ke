<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view-admin-dashboard',
            'manage-products',
            'manage-orders',
            'manage-users',
            'view-analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $affiliateRole = Role::firstOrCreate(['name' => 'affiliate']);

        // Give admin all permissions (wrapped in try-catch in case migrations haven't run)
        try {
            $adminRole->givePermissionTo(Permission::all());
        } catch (\Exception $e) {
            // Tables might not exist yet, skip permission assignment
            // This can happen if migrations haven't been run
        }

        // Customers and affiliates don't need any special permissions for now
    }
}
