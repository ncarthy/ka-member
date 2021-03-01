SELECT m.idmember, m.membershiptype,m.Name, 
IFNULL(m.businessname,'') as BusinessName, m.Note,
m.updatedate, m.expirydate,  
m.reminderdate,
COUNT(*) as NumberTransactions, MAX(t.time) AS LastTransaction
FROM vwMember m
LEFT OUTER JOIN vwTransaction t ON m.idmember = t.idmember
WHERE m.idmembership IN (2,3,4,10) AND m.deletedate IS NULL
GROUP BY m.idmember
HAVING LastTransaction IS NULL OR LastTransaction < DATE_SUB(NOW(), INTERVAL 18 MONTH)
ORDER BY LastTransaction;