USE `knightsb_membership`;
START TRANSACTION;
CREATE TABLE   IF NOT EXISTS `knightsb_membership`.`bankaccount`  ( 
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

ALTER TABLE `membershipstatus` ADD `multiplier` INT NULL DEFAULT '1' AFTER `name`;
UPDATE `membershipstatus` SET `name` = 'Individual' WHERE `membershipstatus`.`idmembership` = 2;
UPDATE `membershipstatus` SET `name` = 'Household',`multiplier`=2 WHERE `membershipstatus`.`idmembership` = 3;
UPDATE `membershipstatus` SET `name` = 'Corporate',`multiplier`=4 WHERE `membershipstatus`.`idmembership` = 4;
UPDATE `membershipstatus` SET `name` = 'Lifetime',`multiplier`=1.5 WHERE `membershipstatus`.`idmembership` = 5;
UPDATE `membershipstatus` SET `name` = 'Honorary' WHERE `membershipstatus`.`idmembership` = 6;
UPDATE `membershipstatus` SET `name` = 'Honorary Life' WHERE `membershipstatus`.`idmembership` = 7;
UPDATE `membershipstatus` SET `name` = 'Contributing Ex-member' WHERE `membershipstatus`.`idmembership` = 8;
UPDATE `membershipstatus` SET `name` = 'Former Member' WHERE `membershipstatus`.`idmembership` = 9;





COMMIT;
