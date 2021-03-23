    # Run first
    DROP TEMPORARY TABLE IF EXISTS `_FormerMembers`;    
    #Run second
    CREATE TEMPORARY TABLE IF NOT EXISTS `_FormerMembers` AS ( 
                        SELECT `idmember`, deletedate, joindate, expirydate,
                        reminderdate, updatedate, membership_idmembership as idmembership,
                        MAX(`date`) as lasttransactiondate, 0 as lasttransactionid,
                        '                     ' as paymentmethod
                        FROM member m
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                        GROUP BY m.idmember
    );    
    #Run third
    UPDATE _FormerMembers M, transaction T
		SET M.lasttransactionid = T.idtransaction,
			M.paymentmethod = LEFT(T.paymentmethod,20)
		WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;
    
	# Only apply one of the filers

    #Filter 1
    DELETE FROM _FormerMembers
    WHERE deletedate < '1980-01-01' OR deletedate > '2019-08-11' OR deletedate IS NULL;
    
	#Filter 2
	DELETE FROM _FormerMembers
    WHERE idmembership != 9;
    


    # Test results
	SELECT m.idmember, m.expirydate,m.joindate,m.reminderdate,m.updatedate,m.deletedate,
                        v.`Name` as `name`, v.businessname, v.Note as `note`, 
                        addressfirstline,addresssecondline,city,postcode,country,
                        gdpr_email,gdpr_tel,gdpr_address,gdpr_sm,
                        v.idmembership, v.membershiptype,
                        t.paymentmethod, m.lasttransactiondate,
                        v.email1, v.email2
                        FROM _FormerMembers m
                        JOIN vwMember v ON m.idmember = v.idmember
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                            AND m.lasttransactionid = t.`idtransaction`;

    DELETE MN
    FROM membername MN
    JOIN _FormerMembers M ON MN.member_idmember = M.idmember;
    
    INSERT INTO `membername` (`honorific`, `firstname`, `surname`, `member_idmember`) 
    SELECT '','', 'Anonymized',idmember FROM _FormerMembers;
    
    UPDATE member M, _FormerMembers FM
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
                    M.username='admin'                  
                 WHERE
                    M.idmember = FM.idmember;

	
    
    