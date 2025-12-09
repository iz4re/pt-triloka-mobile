-- New tables for CV Triloka Backend
-- Run this in phpMyAdmin SQL tab

CREATE TABLE IF NOT EXISTS `items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT 0,
  `min_stock` int NOT NULL DEFAULT 10,
  `unit` varchar(50) DEFAULT 'pcs',
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_item_code_unique` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','transfer','check','other') NOT NULL DEFAULT 'transfer',
  `proof_image` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_created_by_foreign` (`created_by`),
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `model_id` bigint UNSIGNED DEFAULT NULL,
  `changes` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('payment_reminder','payment_received','stock_alert','invoice_overdue','general') NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `project_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_number` varchar(255) NOT NULL,
  `klien_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('construction','renovation','supply','contractor','other') NOT NULL DEFAULT 'other',
  `description` text NOT NULL,
  `location` text NOT NULL,
  `expected_budget` decimal(15,2) DEFAULT NULL,
  `expected_timeline` varchar(255) DEFAULT NULL,
  `status` enum('pending','quoted','negotiating','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_requests_request_number_unique` (`request_number`),
  KEY `project_requests_klien_id_foreign` (`klien_id`),
  CONSTRAINT `project_requests_klien_id_foreign` FOREIGN KEY (`klien_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quotations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` bigint UNSIGNED NOT NULL,
  `quotation_number` varchar(255) NOT NULL,
  `version` int NOT NULL DEFAULT 1,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text,
  `valid_until` date NOT NULL,
  `status` enum('draft','sent','approved','rejected','revised') NOT NULL DEFAULT 'draft',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotations_quotation_number_unique` (`quotation_number`),
  KEY `quotations_request_id_foreign` (`request_id`),
  KEY `quotations_created_by_foreign` (`created_by`),
  CONSTRAINT `quotations_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `project_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `quotation_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` enum('material','labor','equipment','other') NOT NULL DEFAULT 'other',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `negotiations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint UNSIGNED NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `sender_type` enum('client','admin') NOT NULL DEFAULT 'client',
  `message` text NOT NULL,
  `counter_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `negotiations_quotation_id_foreign` (`quotation_id`),
  KEY `negotiations_sender_id_foreign` (`sender_id`),
  CONSTRAINT `negotiations_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `negotiations_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `request_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` bigint UNSIGNED NOT NULL,
  `document_type` enum('ktp','npwp','drawing','rab','permit','photo','other') NOT NULL DEFAULT 'other',
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `description` text,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `request_documents_request_id_foreign` (`request_id`),
  KEY `request_documents_verified_by_foreign` (`verified_by`),
  CONSTRAINT `request_documents_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `project_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `request_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
