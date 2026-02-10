-- RBAC System - Manual Database Setup (Optional Reference)
-- Use migrations instead: php artisan migrate

-- Create roles (if migration doesn't run)
INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('super_admin', 'web', NOW(), NOW()),
('admin', 'web', NOW(), NOW()),
('product_admin', 'web', NOW(), NOW()),
('customer', 'web', NOW(), NOW()),
('affiliate', 'web', NOW(), NOW());

-- Create permissions
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('manage-all-users', 'web', NOW(), NOW()),
('manage-admin-roles', 'web', NOW(), NOW()),
('manage-products', 'web', NOW(), NOW()),
('view-analytics', 'web', NOW(), NOW()),
('manage-orders', 'web', NOW(), NOW()),
('manage-affiliates', 'web', NOW(), NOW()),
('manage-settings', 'web', NOW(), NOW());

-- Get role and permission IDs (replace X with actual IDs from above)
-- SELECT id FROM roles WHERE name = 'super_admin';
-- SELECT id FROM permissions WHERE name = 'manage-products';

-- Assign all permissions to super_admin
INSERT INTO role_has_permissions (role_id, permission_id) VALUES
(1, 1), -- super_admin -> manage-all-users
(1, 2), -- super_admin -> manage-admin-roles
(1, 3), -- super_admin -> manage-products
(1, 4), -- super_admin -> view-analytics
(1, 5), -- super_admin -> manage-orders
(1, 6), -- super_admin -> manage-affiliates
(1, 7); -- super_admin -> manage-settings

-- Assign all permissions to admin
INSERT INTO role_has_permissions (role_id, permission_id) VALUES
(2, 1), -- admin -> manage-all-users
(2, 2), -- admin -> manage-admin-roles
(2, 3), -- admin -> manage-products
(2, 4), -- admin -> view-analytics
(2, 5), -- admin -> manage-orders
(2, 6), -- admin -> manage-affiliates
(2, 7); -- admin -> manage-settings

-- Assign only manage-products to product_admin
INSERT INTO role_has_permissions (role_id, permission_id) VALUES
(3, 3); -- product_admin -> manage-products

-- Example: Make a user a Super Admin
-- Replace user_id with actual user ID (e.g., 1)
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
(1, 'App\\Models\\User', 1);

-- Example: Make a user a Product Admin
-- Replace user_id with actual user ID (e.g., 5)
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
(3, 'App\\Models\\User', 5);

-- Check all users and their roles
SELECT u.id, u.name, u.email, r.name as role
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id AND mhr.model_type = 'App\\Models\\User'
LEFT JOIN roles r ON mhr.role_id = r.id
ORDER BY u.id;

-- Check a specific user's permissions
SELECT p.name as permission
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN role_has_permissions rhp ON mhr.role_id = rhp.role_id
JOIN permissions p ON rhp.permission_id = p.id
WHERE u.id = 1; -- Replace 1 with user ID

-- Remove a role from a user
DELETE FROM model_has_roles 
WHERE model_id = 1 AND model_type = 'App\\Models\\User';

-- Change a user's role
UPDATE model_has_roles 
SET role_id = 3 
WHERE model_id = 5 AND model_type = 'App\\Models\\User';

-- View all roles and their permissions
SELECT r.name as role, p.name as permission
FROM roles r
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
LEFT JOIN permissions p ON rhp.permission_id = p.id
ORDER BY r.id, p.id;
