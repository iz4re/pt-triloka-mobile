-- Sample Notifications for Testing
-- Run this in MySQL or via php artisan tinker

-- First, get the user ID for mitra@example.com
SET @mitra_id = (SELECT id FROM users WHERE email = 'mitra@example.com');

-- Clear existing notifications for this user
DELETE FROM notifications WHERE user_id = @mitra_id;

-- Insert sample notifications
INSERT INTO notifications (user_id, type, title, message, is_read, created_at, updated_at) VALUES
(@mitra_id, 'invoice', 'Invoice Baru Dibuat', 'Invoice #INV-2024-001 telah dibuat untuk proyek Website Development. Total: Rp 15.000.000', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(@mitra_id, 'payment', 'Pembayaran Diterima', 'Pembayaran sebesar Rp 5.000.000 untuk Invoice #INV-2024-001 telah diterima', 0, DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(@mitra_id, 'reminder', 'Jatuh Tempo Invoice', 'Invoice #INV-2024-002 akan jatuh tempo dalam 3 hari. Mohon segera lakukan pembayaran.', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(@mitra_id, 'invoice', 'Invoice Telah Dibayar', 'Invoice #INV-2023-099 telah lunas. Terima kasih atas pembayaran Anda.', 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@mitra_id, 'info', 'Selamat Datang di CV Triloka', 'Terima kasih telah bergabung dengan sistem manajemen keuangan CV Triloka.', 1, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Verify
SELECT id, type, title, is_read, created_at FROM notifications WHERE user_id = @mitra_id ORDER BY created_at DESC;
