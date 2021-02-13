SELECT Month(t.`date`) as `Month`,YEAR(t.`date`) as `Year`
    ,COUNT(t.idtransaction)-COUNT(t1.idtransaction)-COUNT(t2.idtransaction)  as ActiveCount
    ,SUM(t.amount)-SUM(IFNULL(t1.amount,0))-SUM(IFNULL(t2.amount,0)) as ActiveSum
	,COUNT(t1.idtransaction) as CEMcount,SUM(IFNULL(t1.amount,0)) as CEM
    ,COUNT(t2.idtransaction) as FormerCount,SUM(IFNULL(t2.amount,0)) as Former
    ,COUNT(t.idtransaction) as TotalCount, SUM(t.amount) as TotalSum
FROM transaction t
LEFT JOIN member m1 ON t.member_idmember = m1.idmember AND 8 = m1.membership_idmembership
LEFT JOIN transaction t1 ON m1.idmember = t1.member_idmember AND t.idtransaction = t1.idtransaction
LEFT JOIN member m2 ON t.member_idmember = m2.idmember AND 9 = m2.membership_idmembership
LEFT JOIN transaction t2 ON m2.idmember = t2.member_idmember AND t.idtransaction = t2.idtransaction
WHERE t.`date` > DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY Month(t.`date`)
ORDER BY t.`date` DESC;

SELECT SUM(amount), COUNT(t.idtransaction) as count FROM transaction t
JOIN vwMember v ON t.member_idmember = v.idmember
WHERE date > DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND v.idmembership = 8
ORDER BY `date`;

SELECT Month(t.`date`) as `Month`,YEAR(t.`date`) as `Year`,t.idtransaction, t.amount as sum
	,t1.idtransaction as t1_idtransaction,t1.amount as CEM, m1.*
FROM transaction t
LEFT JOIN member m1 ON t.member_idmember = m1.idmember AND 8 = m1.membership_idmembership
LEFT JOIN transaction t1 ON m1.idmember = t1.member_idmember AND t.idtransaction = t1.idtransaction
WHERE t.`date` > DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
#GROUP BY Month(t.`date`)
ORDER BY t.`date`;