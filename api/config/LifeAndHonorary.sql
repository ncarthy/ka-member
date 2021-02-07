SELECT m.idmember, ms.name, CONCAT(' ' , m.note) as note,
CONCAT(mn.honorific,' ',mn.firstname,' ',mn.surname) as `Name`, 
        `m`.`addressfirstline` AS `Address1`,
        `m`.`addresssecondline` AS `Address2`,
        `m`.`city` as `City`,
        `m`.`postcode` AS `Postcode`,
COUNT(t.idtransaction) as NumberTransactions, MAX(t.time) AS LastTransaction
FROM member m
JOIN membershipstatus ms ON m.membership_idmembership = ms.idmembership
JOIN membername mn ON m.idmember = mn.member_idmember
LEFT OUTER JOIN transaction t ON m.idmember = t.member_idmember
WHERE ms.idmembership IN (5,6) AND deletedate IS NULL
GROUP BY m.idmember
ORDER BY ms.name, LastTransaction 
;