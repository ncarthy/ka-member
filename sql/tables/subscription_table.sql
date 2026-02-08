-- Create subscription table for GoCardless subscription tracking
-- Links members to their GoCardless subscription, mandate, and customer records

CREATE TABLE `subscription` (
  `idsubscription` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_idmember` INT NOT NULL,
  `gc_mandate_id` VARCHAR(255) NOT NULL,
  `gc_customer_id` VARCHAR(255) NOT NULL,
  `gc_subscriptionid` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_gc_subscription` (`gc_subscriptionid`),
  INDEX `idx_member` (`member_idmember`),
  INDEX `idx_mandate` (`gc_mandate_id`),
  INDEX `idx_customer` (`gc_customer_id`),
  CONSTRAINT `fk_member_subscription` FOREIGN KEY (`member_idmember`) REFERENCES `member` (`idmember`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
