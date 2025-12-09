-- Delete existing users
TRUNCATE TABLE users;

-- Insert admin user with properly bcrypt hashed password
-- Password: password
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) 
VALUES (
  'Admin Triloka', 
  'admin@triloka.com', 
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  1,
  NOW(),
  NOW()
);

-- Insert klien user
-- Password: password
INSERT INTO users (name, email, password, role, phone, company_name, is_active, created_at, updated_at) 
VALUES (
  'Mitra Sejahtera', 
  'mitra@example.com', 
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'klien',
  '081234567890',
  'PT Mitra Sejahtera',
  1,
  NOW(),
  NOW()
);

-- Verification
SELECT id, name, email, role FROM users;
