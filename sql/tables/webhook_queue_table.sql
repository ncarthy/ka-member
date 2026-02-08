-- Create webhook_queue table for asynchronous webhook processing
-- Events are queued here and processed by a background worker

CREATE TABLE `webhook_queue` (
  `idwebhook_queue` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` VARCHAR(255) NOT NULL,
  `resource_type` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `payload` TEXT NOT NULL,
  `raw_payload` TEXT NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `retry_count` INT UNSIGNED DEFAULT 0,
  `max_retries` INT UNSIGNED DEFAULT 3,
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processing_at` TIMESTAMP NULL,
  `completed_at` TIMESTAMP NULL,
  `next_retry_at` TIMESTAMP NULL,
  UNIQUE KEY `unique_event_id` (`event_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_next_retry` (`status`, `next_retry_at`),
  INDEX `idx_resource_action` (`resource_type`, `action`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
