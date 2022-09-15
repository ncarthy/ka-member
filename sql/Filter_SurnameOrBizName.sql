    /* Script written to test the logic behind the filter "surname or business name" */    
    /* This is a test of the filter  { "businessorsurname": "pa",     "removed": "any" } */
    /* This is the filter test used in Postman */
    
    # Run first
    DROP TEMPORARY TABLE IF EXISTS `_Members`;    
    #Run second
    CREATE TEMPORARY TABLE IF NOT EXISTS `_Members` AS ( 
                        SELECT `idmember`, deletedate, joindate, expirydate,
                        reminderdate, updatedate, membership_idmembership as idmembership,
                        MAX(`date`) as lasttransactiondate, 0 as lasttransactionid,
                        0 as paymenttypeID, 0 as bankaccountID, m.postonhold
                        FROM member m
                        LEFT JOIN `transaction` t ON m.idmember = t.member_idmember
                        WHERE m.idmember > 880
                        GROUP BY m.idmember
    );    
    #Run third
	UPDATE _Members M, transaction T
                        SET M.lasttransactionid = T.idtransaction,
                            M.paymenttypeID = T.paymenttypeID,
                            M.bankaccountID = T.bankID
                        WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;
    

    #Run Fourth:
    DELETE FROM _Members 
    WHERE idmember NOT IN (SELECT DISTINCT(M.idmember) FROM _Members M
                    JOIN member m ON M.idmember = m.idmember
                    LEFT JOIN membername mn ON m.idmember = mn.member_idmember
                    WHERE m.businessname LIKE 'nad%' OR 
                        (mn.surname LIKE 'nad%' AND mn.surname IS NOT NULL));
                        



    # Test results
	SELECT temp.idmember, temp.expirydate,temp.joindate,temp.reminderdate,
                        temp.updatedate,temp.deletedate, temp.lasttransactiondate,
                        IFNULL(`m`.`membership_fee`,
                            `ms`.`membershipfee`) AS `membershipfee`,
                        IFNULL(GROUP_CONCAT( CONCAT(CASE
                                            WHEN `mn`.`honorific` = '' THEN ''
                                            ELSE CONCAT(`mn`.`honorific`, ' ')
                                        END,
                                        CASE
                                            WHEN `mn`.`firstname` = '' THEN ''
                                            ELSE CONCAT(`mn`.`firstname`, ' ')
                                        END,
                                        `mn`.`surname`) SEPARATOR ' & '),
                                '') AS `name`,
                        `m`.`businessname` AS `businessname`,
                        CONCAT(`m`.`note`, ' ') AS `note`,
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
                        END AS `country`,
                        m.gdpr_email,m.gdpr_tel,m.gdpr_address,m.gdpr_sm,
                        `m`.`membership_idmembership` AS `idmembership`,
                        `ms`.`name` AS `membershiptype`,
                        IFNULL(pt.name,'') as paymenttype, IFNULL(b.name,'') as bankaccount,
                        m.email1, m.email2
                        FROM _Members temp
                        INNER JOIN `member` `m` ON temp.idmember = m.idmember
                        INNER JOIN `membershipstatus` `ms` ON (`m`.`membership_idmembership` = `ms`.`idmembership`)
                        LEFT JOIN `paymenttype` `pt` ON  `temp`.`paymenttypeID` = `pt`.`paymenttypeID`
                        LEFT JOIN `bankaccount` `b` ON  `temp`.`bankaccountID` = `b`.`bankID`
                        LEFT JOIN `country` `c1` ON (`m`.`countryID` = `c1`.`id`)
                        LEFT JOIN `country` `c2` ON (`m`.`country2ID` = `c2`.`id`)
                        LEFT JOIN membername `mn` ON `m`.`idmember` = mn.member_idmember
                        GROUP BY temp.idmember;
    
     

/* That conculdes the representation of Postman test */