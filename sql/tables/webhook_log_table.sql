-- Create webhook_log table for GoCardless webhook integration
-- This table provides idempotency through unique constraint on webhook_id

CREATE TABLE `webhook_log` (
  `idwebhook_log` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `webhook_id` VARCHAR(255) NOT NULL,
  `event_id` VARCHAR(255) NOT NULL,
  `resource_type` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `resource_id` VARCHAR(255) NULL,
  `payload` TEXT NOT NULL,
  `processed` TINYINT(1) DEFAULT 0,
  `idmember` INT UNSIGNED NULL,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL,
  UNIQUE KEY `unique_webhook_id` (`webhook_id`),
  INDEX `idx_event_id` (`event_id`),
  INDEX `idx_resource` (`resource_type`, `resource_id`),
  INDEX `idx_processed` (`processed`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
