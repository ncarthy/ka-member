SELECT m.idmember,m.membership_idmembership,ms.name,
IFNULL(m.membership_fee,ms.membershipfee) as fee,
CONCAT(m.honorific,' ',m.firstname,' ',m.surname) as `Name`, businessname,
SUM(t.amount) as amount, CASE WHEN COUNT(*) > 1 THEN 1 ELSE 0 END as `duplicate`,
CASE WHEN SUM(t.amount)<0 THEN 1 ELSE 0 END as `Refund`,
CASE WHEN SUM(t.amount)>=0 AND m.membership_idmembership=8 THEN 1 ELSE 0 END as `CEM`,
CASE WHEN SUM(t.amount)>=0 AND t.amount < membershipfee AND m.membership_idmembership!=8 THEN 1 ELSE 0 END as `B/O`,
CASE WHEN SUM(t.amount)>0 AND m.membership_idmembership IN (5,6) THEN 1 ELSE 0 END as `Hon/Life`,
CASE WHEN SUM(amount) = membershipfee THEN 1 ELSE 0 END as `Correct`
FROM vwMember m
JOIN membershipstatus ms ON m.membership_idmembership = ms.idmembership
JOIN `transaction` t ON m.idmember = t.member_idmember
WHERE `time` >= '2020-01-01' AND `time` < '2021-01-01'
GROUP BY m.idmember
ORDER BY `duplicate` DESC,Name