<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupRbacRolesPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create permissions table if it doesn't exist
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('guard_name')->default('web');
                $table->timestamps();
            });
        }

        // Create roles table if it doesn't exist
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('guard_name')->default('web');
                $table->timestamps();
            });
        }

        // Create role_has_permissions table if it doesn't exist
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function ($table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['permission_id', 'role_id']);
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            });
        }

        // Create model_has_permissions table if it doesn't exist
        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function ($table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('model_id');
                $table->string('model_type');
                $table->primary(['permission_id', 'model_id', 'model_type']);
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->index(['model_id', 'model_type']);
            });
        }

        // Create model_has_roles table if it doesn't exist
        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function ($table) {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('model_id');
                $table->string('model_type');
                $table->primary(['role_id', 'model_id', 'model_type']);
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->index(['model_id', 'model_type']);
            });
        }

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
