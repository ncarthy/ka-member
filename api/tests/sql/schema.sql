SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `user` (
  `iduser` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `new_pass` VARCHAR(255) NOT NULL,
  `isAdmin` TINYINT(1) NOT NULL DEFAULT 0,
  `suspended` TINYINT(1) NOT NULL DEFAULT 0,
  `name` VARCHAR(150) NOT NULL,
  `failedloginattempts` INT NOT NULL DEFAULT 0,
  `email` VARCHAR(150) DEFAULT NULL,
  `title` VARCHAR(20) DEFAULT NULL,
  `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`iduser`),
  UNIQUE KEY `ux_user_username` (`username`)
);

CREATE TABLE IF NOT EXISTS `usertoken` (
  `idusertoken` INT NOT NULL AUTO_INCREMENT,
  `iduser` INT NOT NULL,
  `primaryKey` VARCHAR(100) NOT NULL,
  `secondaryKey` VARCHAR(100) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `expiresAt` DATETIME NOT NULL,
  PRIMARY KEY (`idusertoken`),
  KEY `ix_usertoken_user` (`iduser`)
);

CREATE TABLE IF NOT EXISTS `country` (
  `id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `ISO3166` VARCHAR(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_country_iso` (`ISO3166`)
);

CREATE TABLE IF NOT EXISTS `bankaccount` (
  `bankID` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`bankID`)
);

CREATE TABLE IF NOT EXISTS `paymenttype` (
  `paymenttypeID` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`paymenttypeID`)
);

CREATE TABLE IF NOT EXISTS `membershipstatus` (
  `idmembership` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `multiplier` INT NOT NULL DEFAULT 1,
  `membershipfee` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `gocardlesslink` VARCHAR(255) DEFAULT '',
  PRIMARY KEY (`idmembership`)
);

CREATE TABLE IF NOT EXISTS `member` (
  `idmember` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(40) DEFAULT '',
  `businessname` VARCHAR(255) DEFAULT '',
  `bankpayerref` VARCHAR(150) DEFAULT '',
  `note` TEXT,
  `addressfirstline` VARCHAR(255) DEFAULT '',
  `addresssecondline` VARCHAR(255) DEFAULT '',
  `city` VARCHAR(120) DEFAULT '',
  `county` VARCHAR(120) DEFAULT '',
  `postcode` VARCHAR(32) DEFAULT '',
  `countryID` INT DEFAULT NULL,
  `area` VARCHAR(120) DEFAULT '',
  `email1` VARCHAR(150) DEFAULT '',
  `phone1` VARCHAR(64) DEFAULT '',
  `addressfirstline2` VARCHAR(255) DEFAULT '',
  `addresssecondline2` VARCHAR(255) DEFAULT '',
  `city2` VARCHAR(120) DEFAULT '',
  `county2` VARCHAR(120) DEFAULT '',
  `postcode2` VARCHAR(32) DEFAULT '',
  `country2ID` INT DEFAULT NULL,
  `email2` VARCHAR(150) DEFAULT '',
  `phone2` VARCHAR(64) DEFAULT '',
  `membership_idmembership` INT NOT NULL,
  `expirydate` DATE DEFAULT NULL,
  `joindate` DATE DEFAULT NULL,
  `reminderdate` DATE DEFAULT NULL,
  `updatedate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deletedate` DATE DEFAULT NULL,
  `repeatpayment` TINYINT(1) NOT NULL DEFAULT 0,
  `recurringpayment` TINYINT(1) NOT NULL DEFAULT 0,
  `username` VARCHAR(100) NOT NULL DEFAULT '',
  `gdpr_email` TINYINT(1) NOT NULL DEFAULT 0,
  `gdpr_tel` TINYINT(1) NOT NULL DEFAULT 0,
  `gdpr_address` TINYINT(1) NOT NULL DEFAULT 0,
  `gdpr_sm` TINYINT(1) NOT NULL DEFAULT 0,
  `postonhold` TINYINT(1) NOT NULL DEFAULT 0,
  `emailonhold` TINYINT(1) NOT NULL DEFAULT 0,
  `multiplier` INT DEFAULT NULL,
  `membership_fee` DECIMAL(10,2) DEFAULT NULL,
  `gpslat1` DECIMAL(10,7) DEFAULT NULL,
  `gpslat2` DECIMAL(10,7) DEFAULT NULL,
  `gpslng1` DECIMAL(10,7) DEFAULT NULL,
  `gpslng2` DECIMAL(10,7) DEFAULT NULL,
  PRIMARY KEY (`idmember`),
  KEY `ix_member_status` (`membership_idmembership`)
);

CREATE TABLE IF NOT EXISTS `membername` (
  `idmembername` INT NOT NULL AUTO_INCREMENT,
  `honorific` VARCHAR(30) DEFAULT '',
  `firstname` VARCHAR(120) DEFAULT '',
  `surname` VARCHAR(120) DEFAULT '',
  `member_idmember` INT NOT NULL,
  PRIMARY KEY (`idmembername`),
  KEY `ix_membername_member` (`member_idmember`)
);

CREATE TABLE IF NOT EXISTS `transaction` (
  `idtransaction` INT NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `paymenttypeID` INT NOT NULL,
  `member_idmember` INT NOT NULL,
  `bankID` INT NOT NULL,
  `note` VARCHAR(255) DEFAULT '',
  PRIMARY KEY (`idtransaction`),
  KEY `ix_transaction_member` (`member_idmember`),
  KEY `ix_transaction_date` (`date`)
);

CREATE TABLE IF NOT EXISTS `gocardless_mandate` (
  `idmandate` INT NOT NULL AUTO_INCREMENT,
  `member_idmember` INT NOT NULL,
  `gc_mandate_id` VARCHAR(100) NOT NULL,
  `gc_customer_id` VARCHAR(100) NOT NULL,
  `gc_subscriptionid` VARCHAR(100) DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idmandate`),
  UNIQUE KEY `ux_gc_mandate_id` (`gc_mandate_id`),
  KEY `ix_gc_member` (`member_idmember`)
);

CREATE TABLE IF NOT EXISTS `webhook_log` (
  `idwebhook_log` INT NOT NULL AUTO_INCREMENT,
  `webhook_id` VARCHAR(100) NOT NULL,
  `event_id` VARCHAR(100) NOT NULL,
  `resource_type` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `resource_id` VARCHAR(100) DEFAULT NULL,
  `payload` LONGTEXT,
  `processed` TINYINT(1) NOT NULL DEFAULT 0,
  `idmember` INT DEFAULT NULL,
  `error_message` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`idwebhook_log`),
  UNIQUE KEY `ux_webhook_id` (`webhook_id`)
);

CREATE TABLE IF NOT EXISTS `webhook_queue` (
  `idwebhook_queue` INT NOT NULL AUTO_INCREMENT,
  `event_id` VARCHAR(100) NOT NULL,
  `resource_type` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `payload` LONGTEXT,
  `raw_payload` LONGTEXT,
  `status` ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `retry_count` INT NOT NULL DEFAULT 0,
  `max_retries` INT NOT NULL DEFAULT 3,
  `error_message` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processing_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `next_retry_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`idwebhook_queue`),
  UNIQUE KEY `ux_webhook_queue_event_id` (`event_id`),
  KEY `ix_webhook_queue_status` (`status`)
);

CREATE TABLE IF NOT EXISTS `osdata` (
  `idosdata` INT NOT NULL AUTO_INCREMENT,
  `postcode` VARCHAR(16) NOT NULL,
  `gpslat` DECIMAL(10,7) NOT NULL,
  `gpslng` DECIMAL(10,7) NOT NULL,
  PRIMARY KEY (`idosdata`),
  UNIQUE KEY `ux_osdata_postcode` (`postcode`)
);

CREATE OR REPLACE VIEW `vwMember` AS
SELECT
  m.idmember,
  m.membership_idmembership AS membershiptypeid,
  ms.name AS membershiptype,
  IFNULL(m.note, '') AS note,
  IFNULL(GROUP_CONCAT(CONCAT(
      CASE WHEN mn.honorific = '' OR mn.honorific IS NULL THEN '' ELSE CONCAT(mn.honorific, ' ') END,
      CASE WHEN mn.firstname = '' OR mn.firstname IS NULL THEN '' ELSE CONCAT(mn.firstname, ' ') END,
      IFNULL(mn.surname, '')
    ) SEPARATOR ' & '), '') AS name,
  IFNULL(m.businessname, '') AS businessname,
  CASE WHEN m.countryID <> 186 AND m.country2ID = 186 THEN m.addressfirstline2 ELSE m.addressfirstline END AS addressfirstline,
  CASE WHEN m.countryID <> 186 AND m.country2ID = 186 THEN m.addresssecondline2 ELSE m.addresssecondline END AS addresssecondline,
  CASE WHEN m.countryID <> 186 AND m.country2ID = 186 THEN m.city2 ELSE m.city END AS city,
  CASE WHEN m.countryID <> 186 AND m.country2ID = 186 THEN m.postcode2 ELSE m.postcode END AS postcode,
  CASE WHEN m.countryID <> 186 AND m.country2ID = 186 THEN c2.name ELSE c1.name END AS country,
  m.updatedate,
  m.expirydate,
  m.reminderdate,
  m.deletedate,
  IFNULL(m.membership_fee, ms.membershipfee) AS membershipfee,
  m.email1,
  m.email2
FROM member m
JOIN membershipstatus ms ON ms.idmembership = m.membership_idmembership
LEFT JOIN membername mn ON mn.member_idmember = m.idmember
LEFT JOIN country c1 ON c1.id = m.countryID
LEFT JOIN country c2 ON c2.id = m.country2ID
GROUP BY m.idmember;
