#Sanity Check 1: no deleted members except in 8,9
# > They should be moved to 8 or 9 or (pending) removed
SELECT * FROM vwMember
WHERE deletedate IS NOT NULL AND idmembership NOT IN (8,9);

#Sanity Check 2: no C ex-members (category 8) who haven't paid in the last 3 years
# > They should be moved to former members and anonymised
# Use filter set to membershiptypeid = 8 & lasttransacitondateend = Today -3years & 
#	lasttransacitondateend = 1-Jan-2000 & removed = any & notsurname = anonymized
DROP TEMPORARY TABLE IF EXISTS `_MostRecentTrade`;   
CREATE TEMPORARY TABLE IF NOT EXISTS `_MostRecentTrade` AS ( 
SELECT idmember,MAX(t.date) as lasttransactiondate, 0 as lasttransactionid
FROM member m
JOIN transaction t ON m.idmember = t.member_idmember
WHERE m.membership_idmembership = 8
GROUP BY m.idmember);
UPDATE `_MostRecentTrade` M, transaction T
SET M.lasttransactionid = T.idtransaction
WHERE M.idmember = T.member_idmember AND M.lasttransactiondate = T.`date`;
DELETE FROM `_MostRecentTrade` WHERE lasttransactiondate > DATE_SUB(CURDATE(), INTERVAL 3 YEAR);
SELECT v.* FROM `_MostRecentTrade` r
JOIN vwMember v ON r.idmember = v.idmember
JOIN transaction t ON r.lasttransactionid = t.idtransaction
WHERE v.idmembership = 8 AND v.surname != 'Anonymized';

#Sanity Check 3: All Former members deleted more than 18months ago have been anonymized
# > They should be anonymized
# Use filter set to removed = 'y' and deletedate < Today-18mths & notsurname = anonymized
SELECT * FROM vwMember
WHERE idmembership = 9 AND deletedate < DATE_SUB(CURDATE(), INTERVAL 18 MONTH) 
	AND surname != 'Anonymized';
    
#Sanity Check4: lapsed members test: No members in (2,3,4,10) who haven't paid in the last
# 24 months.
# > Should be moved to former members and anonymized 
SELECT m.idmember, m.membershiptype,m.Name, 
                        IFNULL(m.businessname,'') as BusinessName, m.Note,
                        m.updatedate, m.expirydate,  
                        m.reminderdate,
                        COUNT(t.idtransaction) as `count`, 
                        MAX(t.`date`) AS `last`
                    FROM vwMember m
                    LEFT OUTER JOIN vwTransaction t ON m.idmember = t.idmember
                    WHERE m.idmembership IN (2,3,4,10) AND m.deletedate IS NULL
                    GROUP BY m.idmember
                    HAVING `last` IS NULL OR 
                        `last` < DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
                    ORDER BY `last`;