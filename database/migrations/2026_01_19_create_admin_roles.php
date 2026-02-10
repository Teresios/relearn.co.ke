<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateRoleBasedAccessControlSystem extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new admin roles if they don't exist
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $productAdminRole = Role::firstOrCreate(['name' => 'product_admin']);

        // Create permissions
        $permissions = [
            'manage-all-users',           // Can view and manage all users including admins
            'manage-admin-roles',         // Can create/remove admin roles from users
            'manage-products',            // Can add/edit/delete products
            'view-analytics',             // Can view analytics dashboards
            'manage-orders',              // Can view and manage orders
            'manage-affiliates',          // Can manage affiliate program
            'manage-settings',            // Can access system settings
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to super_admin (all permissions)
        $superAdminRole->syncPermissions(Permission::all());

        // Assign permissions to product_admin (limited to product management only)
        $productAdminRole->syncPermissions([
            'manage-products',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new roles if they exist
        Role::where('name', 'super_admin')->delete();
        Role::where('name', 'product_admin')->delete();

        // Drop new permissions
        Permission::whereIn('name', [
            'manage-all-users',
            'manage-admin-roles',
            'manage-products',
            'view-analytics',
            'manage-orders',
            'manage-affiliates',
            'manage-settings',
        ])->delete();
    }
}
