SELECT m.idmember, ms.name, CONCAT(' ' , m.note) as note,
m.updatedate, m.expirydate, CONCAT(mn.honorific,' ',mn.firstname,' ',mn.surname) as name, 
COUNT(*) as NumberTransactions, MAX(t.time) AS LastTransaction
FROM member m
JOIN membershipstatus ms ON m.membership_idmembership = ms.idmembership
JOIN membername mn ON m.idmember = mn.member_idmember
LEFT OUTER JOIN transaction t ON m.idmember = t.member_idmember
WHERE ms.idmembership IN (2,3,4,10) AND deletedate IS NULL
GROUP BY m.idmember
HAVING LastTransaction IS NULL OR LastTransaction < DATE_SUB(NOW(), INTERVAL 18 MONTH)
ORDER BY LastTransaction 
;