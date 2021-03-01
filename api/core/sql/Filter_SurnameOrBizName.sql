    /* Script written to test the logic behind the filter "surname or business name" */    
    /* This is a test of the filter  { "businessorsurname": "pa",     "removed": "any" } */
    /* This is the filter test used in Postman */
    
    # Run first
    DROP TEMPORARY TABLE IF EXISTS `_Members`;    
    #Run second
    CREATE TEMPORARY TABLE IF NOT EXISTS `_Members` ENGINE=MEMORY AS ( 
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
        
    #Apply filter. See `members_to_exclude` below
	DELETE M
    FROM _Members M    
    JOIN member m ON M.idmember = m.idmember
	LEFT JOIN membername mn ON m.idmember = mn.member_idmember
	WHERE m.businessname NOT LIKE 'co%' AND (mn.surname NOT LIKE 'co%' OR mn.surname IS NULL);
      
SELECT * FROM    _Members M JOIN vwMember V ON M.idmember = V.idmember;     

/* That conculdes the representation of Postman test */

/* The SQL below was used to build and test the critical DELETE query */

        
# Create a list of members whose surname starts with 'pa' or whose business naem starts with 'pa'
DROP TEMPORARY TABLE IF EXISTS `_members1`;   
CREATE TEMPORARY TABLE IF NOT EXISTS `_members1` AS ( 
SELECT mn.member_idmember as id
FROM membername mn
WHERE mn.surname LIKE 'co%'
GROUP BY mn.member_idmember);
INSERT INTO `_members1`
SELECT m.idmember as id FROM member m
LEFT JOIN `_members1` m1 ON m.idmember = m1.id
WHERE m.businessname LIKE 'co%' AND m1.id IS NULL 
GROUP BY m.idmember
ORDER BY id;

# Create a list of members whose surname DOES NOT start with 'pa' or whose business name DOES NOT starts with 'pa'
# This will be used in the filter
DROP TEMPORARY TABLE IF EXISTS `_members_to_exclude`;   
CREATE TEMPORARY TABLE IF NOT EXISTS `_members_to_exclude` AS ( 
SELECT m.idmember as id FROM member m
LEFT JOIN membername mn ON m.idmember = mn.member_idmember
WHERE m.businessname NOT LIKE 'pa%' AND (mn.surname NOT LIKE 'pa%' OR mn.surname IS NULL)
GROUP BY m.idmember);

# Check that defintiely not excluded any valid members
# Should be empty set
SELECT m1.id as m1,m2.id as m2
FROM `_members_to_exclude` m2
LEFT JOIN `_members1` m1 ON m2.id = m1.id
WHERE m1.id IS NOT NULL;

# Check that did not exclude some invalid members
# Should be empty set
SELECT * FROM vwMember m1
LEFT JOIN `_members_to_exclude` m2 ON m1.idmember = m2.id
LEFT JOIN `_members1` m3 ON m1.idmember = m3.id
WHERE m2.id IS NULL AND m3.id IS NULL;

#Check that the set of (all members - excluded members) is equal to `members1`
# Should be empty set
SELECT *
FROM vwMember v
LEFT JOIN `_members_to_exclude` m2 ON v.idmember = m2.id
LEFT JOIN `_members1` m3 ON v.idmember = m3.id
WHERE m2.id IS NULL AND m3.id IS NULL;

SELECT * FROM  `_members1` GROUP BY id;