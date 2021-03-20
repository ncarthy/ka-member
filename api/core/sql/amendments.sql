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

UPDATE `member` SET `repeatpayment` = 0, `username` = "ncarthy", updatedate=CURRENT_TIMESTAMP WHERE `repeatpayment` IS NULL;
ALTER TABLE `member` CHANGE `repeatpayment` `repeatpayment` INT(11) NOT NULL DEFAULT '0';
UPDATE `member` SET `recurringpayment` = 0, `username` = "ncarthy", updatedate=CURRENT_TIMESTAMP WHERE `recurringpayment` IS NULL;
ALTER TABLE `member` CHANGE `recurringpayment` `recurringpayment` INT(11) NOT NULL DEFAULT '0';
UPDATE `member` SET `username` = "ncarthy", updatedate=CURRENT_TIMESTAMP WHERE `username` IS NULL OR `username` = "";
ALTER TABLE `member` CHANGE `username` `username` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ncarthy';
ALTER TABLE `member` CHANGE `addressfirstline` `addressfirstline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addresssecondline` `addresssecondline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addressfirstline2` `addressfirstline2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `addresssecondline2` `addresssecondline2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `member` ADD `multiplier` DECIMAL(11,2) NULL AFTER `gdpr_sm`;
ALTER TABLE `member` ADD `membership_fee` DECIMAL(11,2) NULL AFTER `multiplier`;
ALTER TABLE `member` ADD `reminderdate` DATE NULL AFTER `joindate`;
ALTER TABLE `member` CHANGE `expirydate` `expirydate` DATE NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `joindate` `joindate` DATE NULL DEFAULT NULL;
ALTER TABLE `member` CHANGE `deletedate` `deletedate` DATE NULL DEFAULT NULL;
ALTER TABLE `member` ADD `postonhold` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'When 1 send no mail to member' AFTER `membership_fee`;
ALTER TABLE `member` CHANGE `country` `country` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'UK';
UPDATE `member` SET area = '' WHERE area = 'UK';
UPDATE `member` SET city = 'London', county = '' WHERE county = 'London';
UPDATE `member` SET country = 'UK' WHERE idmember IN (180,280);
UPDATE member SET addressfirstline = addresssecondline, addresssecondline='' WHERE addressfirstline = '' AND addresssecondline !='';

ALTER TABLE `membershipstatus` ADD `multiplier` DECIMAL(11,2) NOT NULL DEFAULT '1' AFTER `name`;
ALTER TABLE `membershipstatus` ADD `membershipfee` DECIMAL(11,2) NOT NULL DEFAULT '0' AFTER `multiplier`;
UPDATE `membershipstatus` SET `name` = 'Individual', `membershipfee` = 20 WHERE `membershipstatus`.`idmembership` = 2;
UPDATE `membershipstatus` SET `name` = 'Household',`multiplier`=2, `membershipfee` = 30 WHERE `membershipstatus`.`idmembership` = 3;
UPDATE `membershipstatus` SET `name` = 'Corporate',`multiplier`=4, `membershipfee` = 40 WHERE `membershipstatus`.`idmembership` = 4;
UPDATE `membershipstatus` SET `name` = 'Lifetime',`multiplier`=1.5, `membershipfee` = 500 WHERE `membershipstatus`.`idmembership` = 5;
UPDATE `membershipstatus` SET `name` = 'Honorary' WHERE `membershipstatus`.`idmembership` = 6;
UPDATE `membershipstatus` SET `name` = 'Pending' WHERE `membershipstatus`.`idmembership` = 7;
UPDATE `membershipstatus` SET `name` = 'Contributing Ex-member' WHERE `membershipstatus`.`idmembership` = 8;
UPDATE `membershipstatus` SET `name` = 'Former Member' WHERE `membershipstatus`.`idmembership` = 9;
DELETE FROM `membershipstatus` WHERE `membershipstatus`.`idmembership` = 1;

INSERT INTO `membershipstatus` (`idmembership`, `name`, `multiplier`, `membershipfee`) VALUES ('10', 'Residence', 20,500);
UPDATE `member` SET `membership_idmembership` = '10', multiplier = 100, `username` = "ncarthy", updatedate=CURRENT_TIMESTAMP
	WHERE `member`.`idmember` = 418;

UPDATE transaction SET member_idmember = 197 WHERE member_idmember = 534;
UPDATE member SET expirydate = '2014-10-31', deletedate = '2014-10-31', 
	username= 'ncarthy', updatedate=CURRENT_TIMESTAMP WHERE idmember = 197;

# Complete removal of these member records
DELETE FROM transaction WHERE member_idmember IN (432,534,625,741,832,833,838,852,853,854,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,876,877,883,890,891,892,894,899,906);
DELETE FROM membername WHERE member_idmember IN (432,534,625,741,832,833,838,852,853,854,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,876,877,883,890,891,892,894,899,906);
DELETE FROM member WHERE idmember IN (432,534,625,741,832,833,838,852,853,854,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,876,877,883,890,891,892,894,899,906);

UPDATE member SET deletedate = expirydate, username= 'ncarthy', updatedate=CURRENT_TIMESTAMP WHERE idmember IN (111);
UPDATE member SET membership_idmembership=9,deletedate = '2019-02-27', expirydate = '2019-02-27', username= 'ncarthy'
	,updatedate=CURRENT_TIMESTAMP WHERE idmember IN (199);
UPDATE member SET joindate = '2015-02-01', expirydate = NULL, username= 'ncarthy', updatedate=CURRENT_TIMESTAMP 
	WHERE idmember IN (748);
UPDATE member SET email1 = 'rnsabrinas@yahoo.com', email2='tadshay@icloud.com',phone1='07568541552'
	,expirydate=NULL,deletedate=NULL,username= 'ncarthy', updatedate=CURRENT_TIMESTAMP, recurringpayment=0 WHERE idmember=90;
UPDATE member SET deletedate=expirydate,username= 'ncarthy', updatedate=CURRENT_TIMESTAMP WHERE idmember IN (121,326,625,835);
UPDATE membername SET honorific= 'Mr and Mrs' WHERE member_idmember=90;
UPDATE member SET note='NatWest SO. GDPR 4/18. Bank ref:  Casual Male, Ceased trading August 2019. Still paying by SO'
	,membership_idmembership=8,deletedate = NULL, expirydate = '2021-06-03', username= 'ncarthy'
    ,updatedate=CURRENT_TIMESTAMP WHERE idmember =100;
INSERT INTO `transaction` (`idtransaction`, `time`, `amount`, `paymentmethod`, `member_idmember`, `bankID`) VALUES (NULL, '2020-06-03', '40', 'SO', '100', NULL);
UPDATE `member` SET `deletedate` = updatedate WHERE `member`.`idmember` IN (157,517);
UPDATE `member` SET `username` = 'ncarthy', updatedate=CURRENT_TIMESTAMP WHERE `member`.`idmember` IN (157,914,517);
UPDATE `transaction` SET member_idmember = 377 WHERE member_idmember = 450;
DELETE FROM membername WHERE member_idmember = 450;
DELETE FROM member WHERE idmember = 450;
UPDATE `member` SET deletedate=updatedate, `membership_idmembership` = 9 WHERE `member`.`idmember` =377;
UPDATE `member` SET deletedate =updatedate, `username` = 'ncarthy', updatedate=CURRENT_TIMESTAMP WHERE membership_idmembership = 7 AND deletedate IS NOT NULL;
UPDATE `member` SET `country` = 'UK' WHERE `postcode` LIKE 'GY%';
UPDATE `member` SET `country2` = 'UK' WHERE `postcode2` LIKE 'GY%';

UPDATE `member` SET `phone1` = '07833380717' WHERE `member`.`idmember` = 181;
UPDATE `member` SET `county` = '' WHERE `member`.`idmember` = 348;

UPDATE `member` SET `expirydate` = NULL,`username` = 'ncarthy', updatedate=CURRENT_TIMESTAMP 
	WHERE `membership_idmembership` IN (5,6) AND deletedate IS NULL;
UPDATE `member` SET `deletedate`= (CASE WHEN IFNULL(updatedate,0) > IFNULL(expirydate,0) THEN updatedate ELSE expirydate END) 
	,`username` = 'ncarthy', updatedate=CURRENT_TIMESTAMP 
	WHERE `deletedate` = 0 AND (expirydate IS NOT NULL OR updatedate IS NOT NULL);
UPDATE `member` SET `expirydate` = NULL,`username` = 'ncarthy', updatedate=CURRENT_TIMESTAMP  WHERE `expirydate` = 0 AND idmember < 876;

UPDATE membername SET firstname = TRIM(firstname) WHERE firstname LIKE '% ';
UPDATE membername SET surname = TRIM(surname) WHERE surname LIKE '% ';
UPDATE membername SET honorific = TRIM(honorific) WHERE honorific LIKE '% ';

#ALTER TABLE `knightsb_membership`.`membername` ADD UNIQUE `Unique_Name_IdMember` (`honorific`, `firstname`, `surname`, `member_idmember`);
ALTER TABLE `member` CHANGE `updatedate` `updatedate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `user` ADD `failedloginattempts` INT NOT NULL DEFAULT '0' COMMENT 'The number of failed logins. Resets to zero after success.' AFTER `name`;

DELETE FROM `transaction` WHERE idtransaction IN (6464,8464,9005);
ALTER TABLE `transaction` CHANGE `time` `date` DATE NOT NULL;

UPDATE member SET reminderdate='2020-06-10' WHERE idmember =53;
UPDATE member SET reminderdate='2019-08-31' WHERE idmember =132;

UPDATE member SET reminderdate='2021-02-08' WHERE idmember =169;
UPDATE member SET reminderdate='2021-02-09' WHERE idmember =246;
UPDATE member SET reminderdate='2021-02-09' WHERE idmember =303;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =313;
UPDATE member SET reminderdate='2021-02-08' WHERE idmember =364;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =400;
UPDATE member SET reminderdate='2021-02-08' WHERE idmember =407;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =419;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =445;
UPDATE member SET reminderdate='2021-02-08' WHERE idmember =569;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =578;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =592;
UPDATE member SET reminderdate='2021-02-10' WHERE idmember IN (614,693);
UPDATE member SET reminderdate='2020-06-12' WHERE idmember =834;
UPDATE member SET reminderdate='2020-06-10' WHERE idmember =845;

/* Totally remove  members who are not former members or pending members and have no transaction  */
    CREATE TEMPORARY TABLE IF NOT EXISTS `_RemovedNoTrans` AS ( 
		SELECT idmember  FROM `member` m
			LEFT JOIN transaction t ON m.idmember = t.member_idmember
			WHERE `membership_idmembership` NOT IN(7, 9) AND `deletedate` IS NOT NULL AND t.idtransaction IS NULL
			GROUP BY idmember
);
    DELETE MN
    FROM membername MN
    JOIN _RemovedNoTrans M ON MN.member_idmember = M.idmember;
	DELETE MN
    FROM member MN
    JOIN _RemovedNoTrans M ON MN.idmember = M.idmember;
/**********************************************************************************************/

/**********************************************************************************************/
/**********************************************************************************************/
CREATE TABLE `knightsb_membership`.`country` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'List of countries for member table';
INSERT INTO country(id,name)VALUES(1,"Afghanistan"),
(2,"Albania"),
(3,"Algeria"),
(4,"Andorra"),
(5,"Angola"),
(6,"Antigua and Barbuda"),
(7,"Argentina"),
(8,"Armenia"),
(9,"Australia"),
(10,"Austria"),
(11,"Azerbaijan"),
(12,"The Bahamas"),
(13,"Bahrain"),
(14,"Bangladesh"),
(15,"Barbados"),
(16,"Belarus"),
(17,"Belgium"),
(18,"Belize"),
(19,"Benin"),
(20,"Bhutan"),
(21,"Bolivia"),
(22,"Bosnia and Herzegovina"),
(23,"Botswana"),
(24,"Brazil"),
(25,"Brunei"),
(26,"Bulgaria"),
(27,"Burkina Faso"),
(28,"Burundi"),
(29,"Cambodia"),
(30,"Cameroon"),
(31,"Canada"),
(32,"Cape Verde"),
(33,"Central African Republic"),
(34,"Chad"),
(35,"Chile"),
(36,"China"),
(37,"Colombia"),
(38,"Comoros"),
(39,"Congo, Republic of the"),
(40,"Congo, Democratic Republic of the"),
(41,"Costa Rica"),
(42,"Cote d'Ivoire"),
(43,"Croatia"),
(44,"Cuba"),
(45,"Cyprus"),
(46,"Czech Republic"),
(47,"Denmark"),
(48,"Djibouti"),
(49,"Dominica"),
(50,"Dominican Republic"),
(51,"East Timor (Timor-Leste)"),
(52,"Ecuador"),
(53,"Egypt"),
(54,"El Salvador"),
(55,"Equatorial Guinea"),
(56,"Eritrea"),
(57,"Estonia"),
(58,"Ethiopia"),
(59,"Fiji"),
(60,"Finland"),
(61,"France"),
(62,"Gabon"),
(63,"The Gambia"),
(64,"Georgia"),
(65,"Germany"),
(66,"Ghana"),
(67,"Greece"),
(68,"Grenada"),
(69,"Guatemala"),
(70,"Guinea"),
(71,"Guinea-Bissau"),
(72,"Guyana"),
(73,"Haiti"),
(74,"Honduras"),
(75,"Hungary"),
(76,"Iceland"),
(77,"India"),
(78,"Indonesia"),
(79,"Iran"),
(80,"Iraq"),
(81,"Ireland"),
(82,"Israel"),
(83,"Italy"),
(84,"Jamaica"),
(85,"Japan"),
(86,"Jordan"),
(87,"Kazakhstan"),
(88,"Kenya"),
(89,"Kiribati"),
(90,"Korea, North"),
(91,"Korea, South"),
(92,"Kosovo"),
(93,"Kuwait"),
(94,"Kyrgyzstan"),
(95,"Laos"),
(96,"Latvia"),
(97,"Lebanon"),
(98,"Lesotho"),
(99,"Liberia"),
(100,"Libya"),
(101,"Liechtenstein"),
(102,"Lithuania"),
(103,"Luxembourg"),
(104,"Macedonia"),
(105,"Madagascar"),
(106,"Malawi"),
(107,"Malaysia"),
(108,"Maldives"),
(109,"Mali"),
(110,"Malta"),
(111,"Marshall Islands"),
(112,"Mauritania"),
(113,"Mauritius"),
(114,"Mexico"),
(115,"Micronesia, Federated States of"),
(116,"Moldova"),
(117,"Monaco"),
(118,"Mongolia"),
(119,"Montenegro"),
(120,"Morocco"),
(121,"Mozambique"),
(122,"Myanmar (Burma)"),
(123,"Namibia"),
(124,"Nauru"),
(125,"Nepal"),
(126,"Netherlands"),
(127,"New Zealand"),
(128,"Nicaragua"),
(129,"Niger"),
(130,"Nigeria"),
(131,"Norway"),
(132,"Oman"),
(133,"Pakistan"),
(134,"Palau"),
(135,"Panama"),
(136,"Papua New Guinea"),
(137,"Paraguay"),
(138,"Peru"),
(139,"Philippines"),
(140,"Poland"),
(141,"Portugal"),
(142,"Qatar"),
(143,"Romania"),
(144,"Russia"),
(145,"Rwanda"),
(146,"Saint Kitts and Nevis"),
(147,"Saint Lucia"),
(148,"Saint Vincent and the Grenadines"),
(149,"Samoa"),
(150,"San Marino"),
(151,"Sao Tome and Principe"),
(152,"Saudi Arabia"),
(153,"Senegal"),
(154,"Serbia"),
(155,"Seychelles"),
(156,"Sierra Leone"),
(157,"Singapore"),
(158,"Slovakia"),
(159,"Slovenia"),
(160,"Solomon Islands"),
(161,"Somalia"),
(162,"South Africa"),
(163,"South Sudan"),
(164,"Spain"),
(165,"Sri Lanka"),
(166,"Sudan"),
(167,"Suriname"),
(168,"Swaziland"),
(169,"Sweden"),
(170,"Switzerland"),
(171,"Syria"),
(172,"Taiwan"),
(173,"Tajikistan"),
(174,"Tanzania"),
(175,"Thailand"),
(176,"Togo"),
(177,"Tonga"),
(178,"Trinidad and Tobago"),
(179,"Tunisia"),
(180,"Turkey"),
(181,"Turkmenistan"),
(182,"Tuvalu"),
(183,"Uganda"),
(184,"Ukraine"),
(185,"United Arab Emirates"),
(186,"United Kingdom"),
(187,"United States of America"),
(188,"Uruguay"),
(189,"Uzbekistan"),
(190,"Vanuatu"),
(191,"Vatican City (Holy See)"),
(192,"Venezuela"),
(193,"Vietnam"),
(194,"Yemen"),
(195,"Zambia"),
(196,"Zimbabwe");



ALTER TABLE `member` ADD `countryID` INT NULL AFTER `country`;
ALTER TABLE `member` ADD `country2ID` INT NULL AFTER `country2`;
UPDATE `member` SET countryID = 186 WHERE country = 'UK';
UPDATE `member` SET countryID = 187 WHERE idmember IN (119,139);
UPDATE `member` SET countryID = 180 WHERE idmember = 71;
UPDATE `member` SET countryID = 36 WHERE idmember = 349;
UPDATE `member` SET countryID = 77 WHERE idmember = 460;
UPDATE `member` SET countryID = 65 WHERE idmember = 311;
UPDATE `member` SET country2ID = 186 WHERE country2 = 'UK';
ALTER TABLE `member` DROP `country`;
ALTER TABLE `member` DROP `country2`;


DROP VIEW IF EXISTS `vwMember`;
DROP VIEW IF EXISTS `vwMembers`;
CREATE VIEW IF NOT EXISTS  `vwMember` AS
    SELECT
		`m`.`idmember` AS `idmember`,
        `m`.`membership_idmembership` AS `idmembership`,
        `ms`.`name` AS `membershiptype`,
        IFNull(`m`.`membership_fee`,`ms`.`membershipfee`)  AS `membershipfee`,
        `mn1`.`honorific` AS `honorific`,
        `mn1`.`firstname` AS `firstname`,
        `mn1`.`surname` AS `surname`,
        IFNULL(CONCAT(CASE
                            WHEN `mn1`.`honorific` = '' THEN ''
                            ELSE CONCAT(`mn1`.`honorific`, ' ')
                        END,
                        `mn1`.`firstname`,
                        ' ',
                        `mn1`.`surname`,
                        CASE
                            WHEN `mn2`.`firstname` IS NULL THEN ''
                            ELSE CONCAT(' And ',
                                    CASE
                                        WHEN `mn1`.`honorific` = '' THEN ''
                                        ELSE CONCAT(`mn2`.`honorific`, ' ')
                                    END,
                                    `mn2`.`firstname`,
                                    ' ',
                                    `mn2`.`surname`)
                        END),
                '') AS `Name`,
        `m`.`businessname` AS `businessname`,
        CONCAT(`m`.`note`, ' ') as `Note`,
        CASE
            WHEN
                `m`.`countryID` != 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`addressfirstline2`
            ELSE `m`.`addressfirstline`
        END AS `addressfirstline`,
        CASE
            WHEN
                `m`.`countryID` != 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`addresssecondline2`
            ELSE `m`.`addresssecondline`
        END AS `addresssecondline`,
        CASE
            WHEN
                `m`.`countryID` != 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`city2`
            ELSE `m`.`city`
        END AS `city`,
        CASE
            WHEN
                `m`.`countryID` != 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`postcode2`
            ELSE `m`.`postcode`
        END AS `postcode`,
        CASE
            WHEN
                `m`.`countryID` != 186
                    AND `m`.`country2ID` = 186
            THEN
                `c2`.`name`
            ELSE `c1`.`name`            
        END AS `country`,
        m.updatedate, m.expirydate, m.deletedate, m.reminderdate,
        m.gdpr_email,gdpr_sm,gdpr_tel,gdpr_address,
        m.email1,m.email2
    FROM
        `member` `m`
        JOIN `membershipstatus` ms ON m.membership_idmembership = ms.idmembership
        LEFT JOIN `country` `c1` ON m.countryID = c1.id
        LEFT JOIN `country` `c2` ON m.country2ID = c2.id
        LEFT JOIN `vwNames` `v` ON `m`.`idmember` = `v`.`member_idmember`
        LEFT JOIN `membername` `mn1` ON `v`.`FirstName` = `mn1`.`idmembername`
        LEFT JOIN `membername` `mn2` ON `v`.`SecondName` = `mn2`.`idmembername`;
        
DROP VIEW IF EXISTS `vwTransaction`;
CREATE VIEW IF NOT EXISTS `vwTransaction` AS
    SELECT 
        `t`.`idtransaction` AS `idtransaction`,
        `m`.`idmember` AS `idmember`,
        `m`.`idmembership` AS `idmembership`,
        `m`.`membershiptype` AS `membershiptype`,
        `m`.`Name` AS `name`,
        IFNULL(`m`.`businessname`, '') AS `businessname`,
        `m`.`Note` AS `note`,
        `m`.`addressfirstline` AS `address1`,
        `m`.`addresssecondline` AS `address2`,
        `m`.`city` AS `city`,
        `m`.`postcode` AS `postcode`,
        `m`.`country` AS `country`,
        `m`.`updatedate` AS `updatedate`,
        `m`.`expirydate` AS `expirydate`,
        `m`.`reminderdate` AS `reminderdate`,
        `m`.membershipfee,
        `t`.`date` AS `date`,
        `t`.`paymentmethod` AS `paymentmethod`,
        `t`.`amount` AS `amount`
    FROM
        (`transaction` `t`
        JOIN `vwMember` `m` ON (`t`.`member_idmember` = `m`.`idmember`));

DROP VIEW IF EXISTS `vwUKMembers`;
CREATE VIEW IF NOT EXISTS `vwUKMembers` AS
    SELECT 
        `mn1`.`honorific` AS `honorific`,
        `mn1`.`firstname` AS `firstname`,
        `mn1`.`surname` AS `surname`,
        `m`.`businessname` AS `businessname`,
        CASE
            WHEN
                `m`.`countryID` <> 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`addressfirstline2`
            ELSE `m`.`addressfirstline`
        END AS `addressfirstline`,
        CASE
            WHEN
                `m`.`countryID` <> 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`addresssecondline2`
            ELSE `m`.`addresssecondline`
        END AS `addresssecondline`,
        CASE
            WHEN
                `m`.`countryID` <> 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`city2`
            ELSE `m`.`city`
        END AS `city`,
        CASE
            WHEN
                `m`.`countryID` <> 186
                    AND `m`.`country2ID` = 186
            THEN
                `m`.`postcode2`
            ELSE `m`.`postcode`
        END AS `postcode`,
        CASE
            WHEN
                `m`.`countryID` <> 186
                    AND `m`.`country2ID` = 186
            THEN
                `c2`.`name`
            ELSE `c1`.`name`
        END AS `country`
    FROM
        `member` `m`
		LEFT JOIN `country` `c1` ON `m`.`countryID` = `c1`.`id`
        LEFT JOIN `country` `c2` ON `m`.`country2ID` = `c2`.`id`
        LEFT JOIN `vwNames` `v` ON `m`.`idmember` = `v`.`member_idmember`
        LEFT JOIN `membername` `mn1` ON `v`.`FirstName` = `mn1`.`idmembername`
        LEFT JOIN `membername` `mn2` ON `v`.`SecondName` = `mn2`.`idmembername`
    WHERE
        `m`.`deletedate` IS NULL
            AND (`m`.`addressfirstline` <> ''
            OR `m`.`addressfirstline2` <> '');

DROP VIEW IF EXISTS `vwUKActiveMemberAddress` ;
CREATE VIEW IF NOT EXISTS `vwUKActiveMemberAddress` AS
    SELECT 
        IFNULL(CONCAT(CASE
                            WHEN `mn1`.`honorific` = '' THEN ''
                            ELSE CONCAT(`mn1`.`honorific`, ' ')
                        END,
                        `mn1`.`firstname`,
                        ' ',
                        `mn1`.`surname`,
                        CASE
                            WHEN `mn2`.`firstname` IS NULL THEN ''
                            ELSE CONCAT(' And ',
                                    CASE
                                        WHEN `mn1`.`honorific` = '' THEN ''
                                        ELSE CONCAT(`mn2`.`honorific`, ' ')
                                    END,
                                    `mn2`.`firstname`,
                                    ' ',
                                    `mn2`.`surname`)
                        END),
                '') AS `Name`,
        CASE
            WHEN `m`.`businessname` <> '' THEN `m`.`businessname`
            ELSE ''
        END AS `Position`,
        `m`.`addressfirstline` AS `Address1`,
        `m`.`addresssecondline` AS `Address2`,
        '' AS `Address3`,
        `m`.`city` AS `Address4`,
        `m`.`postcode` AS `Postcode`
    FROM
        (((`member` `m`
        LEFT JOIN `vwNames` `v` ON (`m`.`idmember` = `v`.`member_idmember`))
        LEFT JOIN `membername` `mn1` ON (`v`.`FirstName` = `mn1`.`idmembername`))
        LEFT JOIN `membername` `mn2` ON (`v`.`SecondName` = `mn2`.`idmembername`))
    WHERE
        `m`.`countryID` = 186
            AND `m`.`deletedate` IS NULL AND `m`.`postonhold` = 0;

ALTER TABLE `knightsb_membership`.`transaction` ADD INDEX (`member_idmember`);
UPDATE transaction SET paymentmethod = 'BO' WHERE paymentmethod LIKE 'B/O%' OR paymentmethod = '' OR paymentmethod LIKE 'BO%';
UPDATE transaction SET paymentmethod = 'Recurring' WHERE paymentmethod LIKE 'Paypal%' OR paymentmethod ='Recurring' OR paymentmethod ='Online';
UPDATE transaction SET paymentmethod = 'Cheque' WHERE paymentmethod LIKE 'BACs%' OR paymentmethod LIKE 'Cheque%' OR paymentmethod LIKE 'Life%' OR paymentmethod LIKE 'Bank%' OR paymentmethod = 'See 2011';
UPDATE transaction SET paymentmethod = 'SO' WHERE paymentmethod LIKE 'SO%';
UPDATE transaction SET paymentmethod = 'Cash' WHERE paymentmethod LIKE 'Cash%';

ALTER TABLE `transaction` CHANGE `paymentmethod` `paymentmethod` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `transaction` CHANGE `amount` `amount` DECIMAL(10,2) NOT NULL DEFAULT '0';

/* Anonymize members who are deleted but whose last transaction was more than 3 years ago */
	DROP TEMPORARY TABLE IF EXISTS `_RemovedWithTrans`;
    CREATE TEMPORARY TABLE IF NOT EXISTS `_RemovedWithTrans` AS ( 
		SELECT idmember,membership_idmembership  FROM `member` m
			LEFT JOIN transaction t ON m.idmember = t.member_idmember
			WHERE `membership_idmembership` NOT IN(7, 9) AND `deletedate` IS NOT NULL AND t.idtransaction IS NOT NULL
			GROUP BY idmember, membership_idmembership
            HAVING MAX(t.date) < '2018-01-01'
);
    DELETE MN
    FROM membername MN
    JOIN `_RemovedWithTrans` M ON MN.member_idmember = M.idmember;
    
    INSERT INTO `membername` (`honorific`, `firstname`, `surname`, `member_idmember`) 
    SELECT '','', 'Anonymized',idmember FROM `_RemovedWithTrans`;
    
    UPDATE member M, `_RemovedWithTrans` FM
                        SET 
                    M.note='',
                    M.addressfirstline='', 
                    M.addresssecondline='', 
                    M.city='', 
                    M.county='', 
                    M.postcode='', 
                    M.countryID=NULL, 
                    M.area='', 
                    M.email1='', 
                    M.phone1='', 
                    M.addressfirstline2='', 
                    M.addresssecondline2='', 
                    M.city2='', 
                    M.county2='', 
                    M.postcode2='', 
                    M.country2ID=NULL, 
                    M.email2='', 
                    M.phone2='', 
                    M.updatedate= NULL, 
                    M.username='ncarthy'                  
                 WHERE
                    M.idmember = FM.idmember;
/**********************************************************************************************/
	DROP TEMPORARY TABLE IF  EXISTS `_MoveToFormerMember_LastTransactionLongAgo`;
    CREATE TEMPORARY TABLE IF NOT EXISTS `_MoveToFormerMember_LastTransactionLongAgo` AS ( 
		SELECT idmember,membership_idmembership  FROM `member` m
			LEFT JOIN transaction t ON m.idmember = t.member_idmember
			WHERE `membership_idmembership` NOT IN(7, 8, 9) AND `deletedate` IS NOT NULL AND t.idtransaction IS NOT NULL
			GROUP BY idmember, membership_idmembership
            
);
    UPDATE member M, `_MoveToFormerMember_LastTransactionLongAgo` FM
                        SET 
                    M.membership_idmembership = 9,
                    M.updatedate= NULL, 
                    M.username='ncarthy'                  
                 WHERE
                    M.idmember = FM.idmember;

INSERT INTO user
SET username='test', isAdmin='0', name='Test User', suspended='0', failedloginattempts='0',new_pass='$2y$10$Annq5/qbt5w9VnaSj3qWKOElR5lj1KpjTshqKghW3v9xb5Wbbbovm';
INSERT INTO user
SET username='user', isAdmin='0', name='Normal User', suspended='0', failedloginattempts='0',new_pass='$2y$10$EjYfEuhGJsrwDfReJDk8Au2wJeIQDs0TuBZoLWq.pU4K7P2bFo8/W';
INSERT INTO user
SET username='admin', isAdmin='1', name='Admin User', suspended='0', failedloginattempts='0',new_pass='$2y$10$FJ8kSpWlrCbv18SIhVwK1.Thx9xzBEkVvqhjurlYk2n853KH9IW8G';

ALTER TABLE `user` DROP `password`;

CREATE TABLE `knightsb_membership`.`usertoken` ( 
`iduser` INT NOT NULL ,
`primaryKey` VARCHAR(36) NOT NULL , 
`secondaryKey` VARCHAR(36) NOT NULL , 
`status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'When 0 token is invalid', 
`issuedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , 
`expiresAt` DATETIME NOT NULL ) ENGINE = InnoDB COMMENT = 'Store of access/refresh token pairs';

ALTER TABLE usertoken
    ADD CONSTRAINT fk_usertoken_user_idx
    FOREIGN KEY (iduser)
    REFERENCES user(iduser);

ALTER TABLE membername
    ADD CONSTRAINT fk_membername_member_idx
    FOREIGN KEY (member_idmember)
    REFERENCES member(idmember);

COMMIT;

OPTIMIZE TABLE `member`;
OPTIMIZE TABLE `transaction`;
