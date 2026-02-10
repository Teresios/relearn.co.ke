# Quick Setup Guide - Role-Based Access Control

## 🚀 Quick Start (3 Steps)

### Step 1: Run Migration
```bash
php artisan migrate
```

This creates:
- ✅ 3 new roles: `super_admin`, `admin`, `product_admin`
- ✅ 7 new permissions
- ✅ Role-permission mappings

### Step 2: Assign Existing Admin Users to Super Admin (IMPORTANT!)

**Option A: Via Laravel Tinker** (Interactive)
```bash
php artisan tinker

# In Tinker shell:
> $admin = App\Models\User::find(1); // Replace 1 with your main admin's ID
> $admin->assignRole('super_admin');
> exit
```

**Option B: Via Artisan Command** (Create one)
```bash
php artisan admin:promote-to-super-admin
```

**Option C: Via Database**
See `RBAC_SQL_REFERENCE.sql` for manual SQL commands

**Why?** Your main admin user was created before the new roles existed. This ensures at least one Super Admin exists for managing others.

### Step 3: Clear Cache
```bash
php artisan cache:clear
php artisan config:cache
```

---

## ✅ Verification

### Check if everything works:

1. **Go to Admin Dashboard**
   - URL: `https://www.relearn.co.ke/admin/users`

2. **Verify Role Dropdown Updated**
   - Should show: Customer, Affiliate, Admin, Product Admin, Super Admin
   - ✅ If you see these options, migration worked!

3. **Create a Test Product Admin**
   - Click on any user
   - Go to "Quick Actions"
   - Click "Make Admin" → "Make Product Admin"
   - Save and test

4. **Test Restrictions**
   - Log out
   - Log in as the Product Admin user
   - Try accessing `/admin/users` 
   - ✅ Should see error or be redirected

---

## 📋 Common Tasks

### Make a User Super Admin
```bash
php artisan tinker
> User::find(1)->assignRole('super_admin');
> exit
```

### Make a User Product Admin
```bash
php artisan tinker
> User::find(5)->assignRole('product_admin');
> exit
```

### Check a User's Role
```bash
php artisan tinker
> User::find(1)->roles->pluck('name');
> exit
```

### Remove All Admin Roles from User
```bash
php artisan tinker
> $user = User::find(5);
> $user->removeRole('admin');
> $user->removeRole('super_admin');
> $user->removeRole('product_admin');
> $user->assignRole('customer');
> exit
```

---

## 🧪 Testing Scenarios

### Test 1: Product Admin Access Restriction
✅ **Expected Behavior**:
1. Create a Product Admin user
2. Log in as that user
3. Accessing `/admin/users` should be restricted
4. User should only see products in navigation

### Test 2: Super Admin Protection
✅ **Expected Behavior**:
1. Create a Regular Admin and a Super Admin
2. Log in as Regular Admin
3. Try to remove Super Admin - button should be **disabled**
4. Shows: "Cannot Modify Super Admin"

### Test 3: Permission Validation
✅ **Expected Behavior**:
1. Log in as any non-admin user
2. Try accessing `/admin` dashboard
3. Should be blocked with "Unauthorized" message

---

## 🔧 Troubleshooting

### Problem: Can't see new roles in dropdown
**Solution**:
```bash
php artisan cache:clear
php artisan migrate:refresh --path="database/migrations/2026_01_19_create_admin_roles.php"
```

### Problem: "You do not have permission..." error
**Solution**:
- Ensure user has `manage-admin-roles` permission
- Only Super Admin and Admin roles have this
- Check: User → Roles → Check if admin role is assigned

### Problem: Permissions not applying
**Solution**:
```bash
php artisan cache:clear
php artisan config:cache
```

### Problem: Tinker command not found
**Solution**:
```bash
composer require laravel/tinker
php artisan tinker
```

---

## 📚 Files Modified

| File | Change |
|------|--------|
| `database/migrations/2026_01_19_create_admin_roles.php` | ✅ NEW - Creates roles & permissions |
| `app/Http/Controllers/Admin/UserController.php` | ✅ UPDATED - Permission checks added |
| `routes/web.php` | ✅ UPDATED - New route added |
| `resources/views/admin/users/index.blade.php` | ✅ UPDATED - UI changes |
| `resources/views/admin/users/show.blade.php` | ✅ UPDATED - UI changes |

---

## 🎯 What's Different Now?

### Before:
- Any admin could remove any other admin
- Only 1 admin role existed
- No granular access control

### After:
- ✅ Only Super Admins can remove other Super Admins
- ✅ Product Admins can ONLY manage products
- ✅ 5 distinct roles with different permissions
- ✅ Permission-based access control throughout

---

## 📞 Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Run migrations in verbose mode: `php artisan migrate -v`
3. Check permissions table: `SELECT * FROM permissions;`
4. Check roles table: `SELECT * FROM roles;`
5. Verify Spatie package installed: `composer show spatie/laravel-permission`

---

## 🔐 Security Checklist

- [ ] Migration ran successfully
- [ ] At least one Super Admin exists
- [ ] No errors in Laravel logs
- [ ] Product Admin can't access user management
- [ ] Regular Admin can't remove Super Admin
- [ ] Permission checks work on both views and routes
- [ ] Users can't change their own role
- [ ] Bulk email respects role filtering

---

**Implementation Date**: January 19, 2026
**Status**: ✅ Ready for Production
**Maintenance**: No regular maintenance needed
