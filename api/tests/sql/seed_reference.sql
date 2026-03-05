INSERT INTO country (`id`,`name`,`ISO3166`) VALUES
(2,'Zimbabwe','ZW'),
(50,'France','FR'),
(186,'United Kingdom','GB'),
(187,'United States','US')
ON DUPLICATE KEY UPDATE name=VALUES(name), ISO3166=VALUES(ISO3166);

INSERT INTO bankaccount (`bankID`,`name`) VALUES
(1,'HSBC'),
(2,'NatWest'),
(3,'Paypal'),
(5,'Lloyds')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO paymenttype (`paymenttypeID`,`name`) VALUES
(1,'Cash'),
(2,'Cheque'),
(3,'Card'),
(5,'Standing Order'),
(6,'Direct Debit')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO membershipstatus (`idmembership`,`name`,`multiplier`,`membershipfee`,`gocardlesslink`) VALUES
(2,'Individual Member',1,20.00,''),
(3,'Household Member',2,30.00,''),
(4,'Corporate Member',4,80.00,''),
(5,'Lifetime Member',1,0.00,''),
(6,'Honorary Member',1,0.00,''),
(7,'Pending Member',1,0.00,''),
(8,'Contributing Ex Member',1,0.00,''),
(9,'Former Member',1,0.00,''),
(10,'Temporary Member',1,5.00,'')
ON DUPLICATE KEY UPDATE name=VALUES(name), multiplier=VALUES(multiplier), membershipfee=VALUES(membershipfee), gocardlesslink=VALUES(gocardlesslink);

INSERT INTO member (`idmember`,`title`,`businessname`,`bankpayerref`,`note`,`addressfirstline`,`addresssecondline`,`city`,`county`,`postcode`,`countryID`,`area`,`email1`,`phone1`,`addressfirstline2`,`addresssecondline2`,`city2`,`county2`,`postcode2`,`country2ID`,`email2`,`phone2`,`membership_idmembership`,`expirydate`,`joindate`,`reminderdate`,`deletedate`,`repeatpayment`,`recurringpayment`,`username`,`gdpr_email`,`gdpr_tel`,`gdpr_address`,`gdpr_sm`,`postonhold`,`emailonhold`,`multiplier`,`membership_fee`,`gpslat1`,`gpslat2`,`gpslng1`,`gpslng2`) VALUES
(8,'','Test Biz A','','Seeded member one','1 Test Street','','London','','SW1A 1AA',186,'','member8@example.com','02000000008','','','','','',NULL,'','',2,'2030-01-01','2020-01-01',NULL,NULL,0,0,'seed',1,1,1,0,0,0,1,20.00,51.5000000,NULL,-0.1200000,NULL),
(119,'','Test Biz B','','Seeded member two','2 Test Street','','London','','SW1A 2AA',186,'','member119@example.com','02000000119','','','','','',NULL,'','',3,'2030-01-01','2020-01-01',NULL,NULL,0,0,'seed',1,1,1,0,0,0,2,30.00,51.5100000,NULL,-0.1300000,NULL),
(278,'','Test Biz C','','Seeded member three','3 Test Street','','London','','SW1A 3AA',186,'','member278@example.com','02000000278','','','','','',NULL,'','',2,'2030-01-01','2020-01-01',NULL,NULL,0,0,'seed',1,1,1,0,0,0,1,20.00,51.5200000,NULL,-0.1400000,NULL)
ON DUPLICATE KEY UPDATE businessname=VALUES(businessname), note=VALUES(note), email1=VALUES(email1), membership_idmembership=VALUES(membership_idmembership);

INSERT INTO member (`idmember`,`title`,`businessname`,`bankpayerref`,`note`,`addressfirstline`,`addresssecondline`,`city`,`county`,`postcode`,`countryID`,`area`,`email1`,`phone1`,`addressfirstline2`,`addresssecondline2`,`city2`,`county2`,`postcode2`,`country2ID`,`email2`,`phone2`,`membership_idmembership`,`expirydate`,`joindate`,`reminderdate`,`updatedate`,`deletedate`,`repeatpayment`,`recurringpayment`,`username`,`gdpr_email`,`gdpr_tel`,`gdpr_address`,`gdpr_sm`,`postonhold`,`emailonhold`,`multiplier`,`membership_fee`,`gpslat1`,`gpslat2`,`gpslng1`,`gpslng2`) VALUES
(410,'','Bonha Trading','','Filter fixture - bonha / jeffest','10 Market Road','','London','','W1A 1AA',186,'','jeffest@example.com','02000000410','','','','','',NULL,'','',5,'2020-12-31','2019-01-01','2020-06-15','2021-01-10 09:00:00',NULL,0,0,'seed',1,1,1,0,0,0,1,0.00,51.5300000,NULL,-0.1500000,NULL),
(411,'','','','Filter fixture - cox no-email','11 River Lane','','London','','W1A 1AB',186,'','','','','','','','',NULL,'','',6,'2021-01-31','2019-01-01','2020-06-20','2021-01-12 09:00:00',NULL,0,0,'seed',1,1,1,0,0,0,1,0.00,51.5310000,NULL,-0.1510000,NULL),
(412,'','','','Filter fixture - deleted september','September Cottage','','London','','W1A 1AC',186,'','','','','','','','',NULL,'','',9,'2020-12-01','2018-01-01','2020-06-05','2021-01-15 09:00:00','2020-07-15',0,0,'seed',1,1,1,0,0,0,1,0.00,51.5320000,NULL,-0.1520000,NULL),
(413,'','','','Filter fixture - bha','13 Cedar Road','','London','','W1A 1AD',186,'','bha@example.com','','','','','','',NULL,'','',2,'2021-06-30','2019-01-01','2020-06-25','2021-01-20 09:00:00',NULL,0,0,'seed',1,1,1,0,0,0,1,20.00,51.5330000,NULL,-0.1530000,NULL),
(414,'','Car Services Ltd','','Filter fixture - car business','14 Hill Street','','London','','W1A 1AE',186,'','carbiz@example.com','','','','','','',NULL,'','',2,'2021-05-31','2019-01-01','2020-06-27','2021-01-22 09:00:00',NULL,0,0,'seed',1,1,1,0,0,0,1,20.00,51.5340000,NULL,-0.1540000,NULL)
ON DUPLICATE KEY UPDATE businessname=VALUES(businessname), note=VALUES(note), email1=VALUES(email1), membership_idmembership=VALUES(membership_idmembership), expirydate=VALUES(expirydate), reminderdate=VALUES(reminderdate), updatedate=VALUES(updatedate), deletedate=VALUES(deletedate);

INSERT INTO membername (`idmembername`,`honorific`,`firstname`,`surname`,`member_idmember`) VALUES
(6475,'Mr','Seed','Member',8),
(6594,'Ms','Seed','Name',119),
(7001,'Mr','Alex','Cox',411),
(7002,'Ms','Bea','Bha',413)
ON DUPLICATE KEY UPDATE honorific=VALUES(honorific), firstname=VALUES(firstname), surname=VALUES(surname), member_idmember=VALUES(member_idmember);

INSERT INTO `transaction` (`idtransaction`,`date`,`amount`,`paymenttypeID`,`member_idmember`,`bankID`,`note`) VALUES
(3150,'2021-01-01',50.00,3,8,3,'Initial seed transaction'),
(3151,'2021-02-01',30.00,2,119,1,'Second seed transaction'),
(3400,'2020-12-20',10.00,5,410,1,'Filter fixture standing order'),
(3401,'2021-01-18',20.00,1,411,1,'Filter fixture cash'),
(3402,'2021-02-01',15.00,2,412,1,'Filter fixture cheque'),
(3403,'2020-12-10',25.00,3,413,3,'Filter fixture date range'),
(3404,'2021-02-08',35.00,2,414,1,'Filter fixture cheque cutoff')
ON DUPLICATE KEY UPDATE amount=VALUES(amount), note=VALUES(note);

INSERT INTO gocardless_mandate (`idmandate`,`member_idmember`,`gc_mandate_id`,`gc_customer_id`,`gc_subscriptionid`) VALUES
(1,8,'MD000TEST123','CU000TEST456','SB000TEST789')
ON DUPLICATE KEY UPDATE member_idmember=VALUES(member_idmember), gc_customer_id=VALUES(gc_customer_id), gc_subscriptionid=VALUES(gc_subscriptionid);

INSERT INTO osdata (`postcode`,`gpslat`,`gpslng`) VALUES
('SW1A 1AA',51.5010090,-0.1415880),
('SW1A 2AA',51.5030000,-0.1200000),
('SW1A 3AA',51.5050000,-0.1000000)
ON DUPLICATE KEY UPDATE gpslat=VALUES(gpslat), gpslng=VALUES(gpslng);
