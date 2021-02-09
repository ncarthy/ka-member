SELECT m.idmember,m.idmembership, m.membershiptype,m.Name as `name`, 
                        IFNULL(m.businessname,'') as businessname, m.note as `note`,
                                `m`.`addressfirstline` AS `address1`,
                                `m`.`addresssecondline` AS `address2`,
                                `m`.`city`,
                                `m`.`postcode`,
                                m.country,
                                Count(t.idtransaction) as count
                        FROM vwMember m
                        LEFT OUTER JOIN vwTransaction t ON m.idmember = t.idmember AND DATE_SUB(NOW(), INTERVAL 12 MONTH) < `t`.`time`
                        WHERE m.idmembership IN (5,6) AND m.deletedate IS NULL
                        GROUP BY m.idmember
                        ORDER BY membershiptype 
                        ;