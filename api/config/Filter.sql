    # Run first
    DROP TEMPORARY TABLE IF EXISTS `_Members`;    
    #Run second
    CREATE TEMPORARY TABLE IF NOT EXISTS `_Members` AS ( 
                        SELECT `idmember`, deletedate, joindate, expirydate,
                        reminderdate, updatedate, membership_idmembership as idmembership,
                        MAX(`date`) as lasttransactiondate, 0 as lasttransactionid,
                        '                     ' as paymentmethod
                        FROM member m
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                        GROUP BY m.idmember
    );    
    #Run third
    UPDATE _Members M, transaction T
		SET M.lasttransactionid = T.idtransaction,
			M.paymentmethod = LEFT(T.paymentmethod,20)
		WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;
    
	# Only apply one of the filers

    #Filter 1
    DELETE M
    FROM _Members M
    LEFT JOIN member M2 ON M.idmember = M2.idmember
    WHERE MN.member_idmember IS NULL OR MN.surname NOT LIKE '%pau%';
    
	#Filter 2
    DELETE 
		FROM _Members
		WHERE paymentmethod IS NULL OR 
			paymentmethod = '                     ' OR 
			paymentmethod NOT LIKE 'cash%';
    


    # Test results
	SELECT m.idmember, m.expirydate,m.joindate,m.reminderdate,m.updatedate,m.deletedate,
                        v.`Name` as `name`, v.businessname, v.Note as `note`, 
                        addressfirstline,addresssecondline,city,postcode,country,
                        gdpr_email,gdpr_tel,gdpr_address,gdpr_sm,
                        v.idmembership, v.membershiptype,
                        t.paymentmethod, m.lasttransactiondate,
                        v.email1, v.email2
                        FROM _Members m
                        JOIN vwMember v ON m.idmember = v.idmember
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                            AND m.lasttransactionid = t.`idtransaction`;



	SELECT * FROM _Members WHERE paymentmethod LIKE 'cash%';
    
    