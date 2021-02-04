CREATE TABLE `knightsb_membership`.`bankaccount` ( 
	`bankID` INT NOT NULL AUTO_INCREMENT , 
	`name` VARCHAR(255) NOT NULL , 
	PRIMARY KEY (`bankID`)
) ENGINE = InnoDB COMMENT = 'List of Bank accounts for transactions';

INSERT INTO `bankaccount` (`bankID`, `name`) VALUES ('1', 'Cash'), ('2', 'Natwest');
INSERT INTO `bankaccount` (`bankID`, `name`) VALUES ('3', 'HSBC'), ('4', 'PayPal');

ALTER TABLE `transaction` DROP `status`;
ALTER TABLE `transaction` ADD `bankID` INT NULL AFTER `member_idmember`;

UPDATE `member` SET `repeatpayment` = 0 WHERE `repeatpayment` IS NULL;
ALTER TABLE `member` CHANGE `repeatpayment` `repeatpayment` INT(11) NOT NULL DEFAULT '0';
UPDATE `member` SET `recurringpayment` = 0 WHERE `recurringpayment` IS NULL;
ALTER TABLE `member` CHANGE `recurringpayment` `recurringpayment` INT(11) NOT NULL DEFAULT '0';
UPDATE `member` SET `username` = "ncarthy" WHERE `username` IS NULL OR `username` = "";
ALTER TABLE `member` CHANGE `username` `username` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ncarthy';
ALTER TABLE `member` CHANGE `addressfirstline` `addressfirstline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addresssecondline` `addresssecondline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addressfirstline2` `addressfirstline2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addresssecondline2` `addresssecondline2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;