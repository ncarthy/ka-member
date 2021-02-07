USE `knightsb_membership`;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `knightsb_membership`.`bankaccount`  ( 
	`bankID` INT NOT NULL AUTO_INCREMENT , 
	`name` VARCHAR(255) NOT NULL , 
	PRIMARY KEY (`bankID`)
) ENGINE = InnoDB COMMENT = 'List of Bank accounts for transactions';

INSERT INTO `bankaccount` (`bankID`, `name`) VALUES ('1', 'Cash'), ('2', 'Natwest');
INSERT INTO `bankaccount` (`bankID`, `name`) VALUES ('3', 'HSBC'), ('4', 'PayPal');

ALTER TABLE `transaction` DROP `status`;
ALTER TABLE `transaction` ADD `bankID` INT NULL AFTER `member_idmember`;

UPDATE `member` SET `repeatpayment` = 0, `username` = "admin", updatedate=CURRENT_TIMESTAMP WHERE `repeatpayment` IS NULL;
ALTER TABLE `member` CHANGE `repeatpayment` `repeatpayment` INT(11) NOT NULL DEFAULT '0';
UPDATE `member` SET `recurringpayment` = 0, `username` = "admin", updatedate=CURRENT_TIMESTAMP WHERE `recurringpayment` IS NULL;
ALTER TABLE `member` CHANGE `recurringpayment` `recurringpayment` INT(11) NOT NULL DEFAULT '0';
UPDATE `member` SET `username` = "admin", updatedate=CURRENT_TIMESTAMP WHERE `username` IS NULL OR `username` = "";
ALTER TABLE `member` CHANGE `username` `username` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'admin';
ALTER TABLE `member` CHANGE `addressfirstline` `addressfirstline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addresssecondline` `addresssecondline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addressfirstline2` `addressfirstline2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addresssecondline2` `addresssecondline2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` ADD `multiplier` DECIMAL(11,2) NULL AFTER `gdpr_sm`;

ALTER TABLE `membershipstatus` ADD `multiplier` DECIMAL(11,2) NOT NULL DEFAULT '1' AFTER `name`;
UPDATE `membershipstatus` SET `name` = 'Individual' WHERE `membershipstatus`.`idmembership` = 2;
UPDATE `membershipstatus` SET `name` = 'Household',`multiplier`=2 WHERE `membershipstatus`.`idmembership` = 3;
UPDATE `membershipstatus` SET `name` = 'Corporate',`multiplier`=4 WHERE `membershipstatus`.`idmembership` = 4;
UPDATE `membershipstatus` SET `name` = 'Lifetime',`multiplier`=1.5 WHERE `membershipstatus`.`idmembership` = 5;
UPDATE `membershipstatus` SET `name` = 'Honorary' WHERE `membershipstatus`.`idmembership` = 6;
UPDATE `membershipstatus` SET `name` = 'Pending' WHERE `membershipstatus`.`idmembership` = 7;
UPDATE `membershipstatus` SET `name` = 'Contributing Ex-member' WHERE `membershipstatus`.`idmembership` = 8;
UPDATE `membershipstatus` SET `name` = 'Former Member' WHERE `membershipstatus`.`idmembership` = 9;

INSERT INTO `membershipstatus` (`idmembership`, `name`, `multiplier`) VALUES ('10', 'Residence', '20');
UPDATE `member` SET `membership_idmembership` = '10', multiplier = 100, `username` = "admin", updatedate=CURRENT_TIMESTAMP
	WHERE `member`.`idmember` = 418;

UPDATE transaction SET member_idmember = 197 WHERE member_idmember = 534;
UPDATE member SET expirydate = '2014-10-31 05:00:00', deletedate = '2014-10-31 05:00:00', 
	username= 'admin', updatedate=CURRENT_TIMESTAMP WHERE idmember = 197;

DELETE FROM transaction WHERE member_idmember IN (432,534,625,741,832,833,852,853,854,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,876,883,892,894,899,906,911,912);
DELETE FROM membername WHERE member_idmember IN (432,534,625,741,832,833,852,853,854,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,876,883,892,894,899,906,911,912);
DELETE FROM member WHERE idmember IN (432,534,625,741,832,833,852,853,854,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,876,883,892,894,899,906,911,912);

UPDATE member SET deletedate = expirydate, username= 'admin', updatedate=CURRENT_TIMESTAMP WHERE idmember IN (111);
UPDATE member SET membership_idmembership=9,deletedate = '2019-02-27', expirydate = '2019-02-27', username= 'admin'
	,updatedate=CURRENT_TIMESTAMP WHERE idmember IN (199);
UPDATE member SET joindate = '2015-02-01', expirydate = NULL, username= 'admin', updatedate=CURRENT_TIMESTAMP 
	WHERE idmember IN (748);
UPDATE member SET email1 = 'rnsabrinas@yahoo.com', email2='tadshay@icloud.com',phone1='07568541552'
	,expirydate=NULL,deletedate=NULL,username= 'admin', updatedate=CURRENT_TIMESTAMP, recurringpayment=0 WHERE idmember=90;
UPDATE member SET deletedate=expirydate,username= 'admin', updatedate=CURRENT_TIMESTAMP WHERE idmember IN (121,326,625,835);
UPDATE membername SET honorific= 'Mr and Mrs' WHERE member_idmember=90;
UPDATE member SET note='NatWest SO. GDPR 4/18. Bank ref:  Casual Male, Ceased trading August 2019. Still paying by SO'
	,membership_idmembership=8,deletedate = NULL, expirydate = '2021-06-03', username= 'admin'
    ,updatedate=CURRENT_TIMESTAMP WHERE idmember =100;
INSERT INTO `transaction` (`idtransaction`, `time`, `amount`, `paymentmethod`, `member_idmember`, `bankID`) VALUES (NULL, '2020-06-03', '40', 'SO', '100', NULL);
UPDATE `member` SET `deletedate` = updatedate WHERE `member`.`idmember` IN (157,517);
UPDATE `member` SET `username` = 'admin', updatedate=CURRENT_TIMESTAMP WHERE `member`.`idmember` IN (157,914,517);
UPDATE `transaction` SET member_idmember = 377 WHERE member_idmember = 450;
DELETE FROM membername WHERE member_idmember = 450;
DELETE FROM member WHERE idmember = 450;
UPDATE `member` SET deletedate=updatedate, `membership_idmembership` = 9 WHERE `member`.`idmember` =377;
UPDATE `member` SET deletedate =updatedate, `username` = 'admin', updatedate=CURRENT_TIMESTAMP WHERE membership_idmembership = 7;

UPDATE `member` SET `phone1` = '07833380717' WHERE `member`.`idmember` = 181;
UPDATE `member` SET `county` = '' WHERE `member`.`idmember` = 348;

UPDATE `member` SET `expirydate` = NULL,`username` = 'admin', updatedate=CURRENT_TIMESTAMP 
	WHERE `membership_idmembership` IN (5,6) AND deletedate IS NULL;
UPDATE `member` SET `deletedate`= (CASE WHEN IFNULL(updatedate,0) > IFNULL(expirydate,0) THEN updatedate ELSE expirydate END) 
	,`username` = 'admin', updatedate=CURRENT_TIMESTAMP 
	WHERE `deletedate` = 0 AND (expirydate IS NOT NULL OR updatedate IS NOT NULL);
UPDATE `member` SET `expirydate` = NULL,`username` = 'admin', updatedate=CURRENT_TIMESTAMP  WHERE `expirydate` = 0;

UPDATE membername SET firstname = TRIM(firstname) WHERE firstname LIKE '% ';
UPDATE membername SET surname = TRIM(surname) WHERE surname LIKE '% ';
UPDATE membername SET honorific = TRIM(honorific) WHERE honorific LIKE '% ';

ALTER TABLE `knightsb_membership`.`membername` ADD UNIQUE `Unique_Name_IdMember` (`honorific`, `firstname`, `surname`, `member_idmember`);
ALTER TABLE `member` CHANGE `updatedate` `updatedate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

COMMIT;
