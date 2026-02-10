# Role-Based Access Control (RBAC) System

## Overview

A comprehensive role-based access control system has been implemented to restrict admin privileges and manage different levels of administrative access.

## Roles

### 1. **Super Admin**
- **Permissions**: Full system access
- **Capabilities**:
  - Manage all users including creating/removing admin roles
  - Manage products
  - View analytics
  - Manage orders
  - Manage affiliates
  - Manage system settings
  - Assign other Super Admin roles
  - Override restrictions on admin removal

### 2. **Admin** (Regular Admin)
- **Permissions**: Limited to not managing super admins
- **Capabilities**:
  - Manage all users (except Super Admins)
  - Create/remove admin roles (only for non-super-admin users)
  - Manage products
  - View analytics
  - Manage orders
  - Manage affiliates
  - Manage settings

### 3. **Product Admin**
- **Permissions**: Limited to product management only
- **Capabilities**:
  - Add products
  - Edit products
  - Delete products
  - ⚠️ **Cannot**:
    - Manage other users
    - Manage orders
    - View analytics
    - Manage affiliates
    - Access system settings

### 4. **Affiliate** (User Role)
- **Permissions**: Access to affiliate program features
- **Capabilities**:
  - Manage own affiliate links
  - View own commission reports
  - Request payouts

### 5. **Customer**
- **Permissions**: Standard user permissions
- **Capabilities**:
  - Purchase products
  - Download purchased content
  - View own order history

## Implementation Details

### Database Changes

A new migration file was created:
- `2026_01_19_create_admin_roles.php`

This migration:
1. Creates three roles: `super_admin`, `admin`, `product_admin`
2. Creates seven permissions:
   - `manage-all-users`
   - `manage-admin-roles`
   - `manage-products`
   - `view-analytics`
   - `manage-orders`
   - `manage-affiliates`
   - `manage-settings`
3. Assigns all permissions to `super_admin`
4. Assigns only `manage-products` to `product_admin`

### How to Run Migration

```bash
php artisan migrate
```

### Controller Changes

**File**: `app/Http/Controllers/Admin/UserController.php`

#### Updated Methods:

1. **`toggleRole(User $user)`**
   - Now checks if user has `manage-admin-roles` permission
   - Prevents removing `super_admin` role unless you're a super admin
   - Handles all admin roles (admin, super_admin, product_admin)

2. **`assignRole(User $user, Request $request)` (NEW)**
   - Allows assigning specific roles to users
   - Validates permissions before assignment
   - Prevents non-super-admins from assigning super_admin role
   - Supports assigning: admin, product_admin, super_admin

### View Changes

**Files Updated**:
1. `resources/views/admin/users/index.blade.php`
2. `resources/views/admin/users/show.blade.php`

**Changes**:
- Role dropdown now includes `product_admin` and `super_admin` options
- Admin action buttons only appear if user has `manage-admin-roles` permission
- Super Admin removal is disabled for non-super-admins
- New dropdown menu for assigning specific admin roles
- Role display badges updated to show all role types

### Route Changes

**File**: `routes/web.php`

New route added:
```php
Route::post('/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('assign-role');
```

## Usage Guide

### Creating a Product Admin

1. Go to **Admin Dashboard** → **Users**
2. Click on a user's view button or settings dropdown
3. In Quick Actions, click "Make Admin" dropdown
4. Select "Make Product Admin"
5. User will now only have access to product management features

### Promoting to Super Admin

⚠️ **Only Super Admins can promote users to Super Admin**

1. Log in as a Super Admin
2. Go to **Users** tab
3. Click on a user's settings dropdown
4. Select "Make Admin" → "Make Super Admin"

### Preventing Admin Removal

1. If you're a Regular Admin, you **cannot** remove Super Admin users
2. A disabled button with lock icon will appear: "Cannot Modify Super Admin"
3. Only Super Admins can manage other Super Admins

### Permission Checks

The system automatically checks:
- `auth()->user()->hasPermissionTo('manage-admin-roles')` - For admin management features
- `auth()->user()->hasRole('super_admin')` - For super admin-only actions

## Security Features

✅ **Implemented Security Measures**:

1. **Role Hierarchy Protection**
   - Can't modify Super Admin unless you're Super Admin
   - Can't assign Super Admin role unless you're Super Admin

2. **Permission-Based Access**
   - Product Admins can't access user management
   - Regular flow users can't see admin options

3. **Self-Protection**
   - Users can't change their own role
   - Users can't remove themselves from admin status

4. **Audit Trail Ready**
   - All role changes go through controlled methods
   - Can easily add logging to track who changed what

## Testing the System

### Test Case 1: Product Admin Restrictions
1. Create a Product Admin user
2. Try accessing `/admin/users` - Should see permission error
3. Product Admin should only see products management area
✅ **Expected**: Limited UI, no user management access

### Test Case 2: Admin Role Removal Protection
1. Create a Super Admin and Regular Admin
2. Log in as Regular Admin
3. Try to remove Super Admin - Should be disabled
✅ **Expected**: Button is disabled with lock icon

### Test Case 3: Permission Validation
1. Log in as Product Admin
2. Try accessing other admin features directly via URL
3. System should block access (middleware/authorization)
✅ **Expected**: "Unauthorized" error or redirect

## Future Enhancements

Possible improvements:
1. Add UI restrictions based on role in navigation menu
2. Add middleware checks to automatically block access by role
3. Create role management interface in admin dashboard
4. Add audit logging for role changes
5. Add granular permission management in admin UI
6. Create different dashboard views per role

## Troubleshooting

### Issue: "You do not have permission to manage admin roles"
**Solution**: Check that you have `manage-admin-roles` permission. Only Super Admin and Admin roles have this.

### Issue: Can't see "Make Super Admin" option
**Solution**: Only Super Admins can assign Super Admin role. Log in as Super Admin first.

### Issue: Permission not loading
**Solution**: Run `php artisan cache:clear` to clear cached permissions.

---

**Last Updated**: January 19, 2026
**System**: Relearn Digital Store
**Version**: 1.0
