SELECT m.idmember, m.membershiptype,m.Name, 
IFNULL(m.businessname,'') as BusinessName, m.Note,
        `m`.`addressfirstline` AS `Address1`,
        `m`.`addresssecondline` AS `Address2`,
        `m`.`city` as `City`,
        `m`.`postcode` AS `Postcode`,
        m.country
FROM vwMember m
LEFT OUTER JOIN vwTransaction t ON m.idmember = t.idmember
WHERE m.idmembership IN (5,6) AND m.deletedate IS NULL
GROUP BY m.idmember;