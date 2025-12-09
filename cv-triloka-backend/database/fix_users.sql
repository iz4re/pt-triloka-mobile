-- 1. First, check if users exist
SELECT id, name, email, role, is_active FROM users WHERE email IN ('admin@triloka.com', 'mitra@example.com');

-- 2. If above returns nothing or wrong data, run this to recreate users:

-- Delete old users (if any)
DELETE FROM users WHERE email IN ('admin@triloka.com', 'mitra@example.com');

-- Create fresh users with CORRECT password hash
-- Password hash below = 'password'
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) 
VALUES 
  (
    'Admin Triloka', 
    'admin@triloka.com', 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1,
    NOW(),
    NOW()
  ),
  (
    'Mitra Sejahtera', 
    'mitra@example.com', 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'klien',
    1,
    NOW(),
    NOW()
  );

-- 3. Verify users created
SELECT id, name, email, role, is_active FROM users;
