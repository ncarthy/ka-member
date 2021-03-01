CREATE TEMPORARY TABLE IF NOT EXISTS _Transa AS (      
                        SELECT idmember,t.idmembership, t.membershiptype,t.`name`, membershipfee
                            IFNULL(t.businessname,'') as businessname, t.note as `note`,
                            `t`.`address1`, `t`.`address2`, `t`.`city`,
                            `t`.`postcode`, t.country, t.updatedate, t.expirydate,  
                            t.reminderdate,
                            SUM(amount) as amount, Max(`date`) as `date`,
                            CASE WHEN SUM(amount)>=0 AND idmembership=8 THEN 1 ELSE 0 END as `CEM`,
                            CASE WHEN SUM(amount)>=0 AND SUM(amount) < membershipfee AND idmembership NOT IN(5,6,8) THEN 1 ELSE 0 END as `Discount`,
                            CASE WHEN SUM(amount)>0 AND idmembership IN (5,6) THEN 1 ELSE 0 END as `HonLife`,
                            CASE WHEN SUM(amount) = membershipfee THEN 1 ELSE 0 END as `Correct`,
                            CASE WHEN COUNT(t.idtransaction) > 1 THEN 1 ELSE 0 END as `Duplicate`
                        FROM vwTransaction t
                        WHERE `date` >=  '2020-02-10'
                        AND `date` <= '2021-02-09'
                        GROUP BY idmember,membershiptype,Name,businessname
                    );
                    
SELECT * FROM _Transa WHERE Duplicate = 1 ORDER BY `date`;                    
                    
DROP TEMPORARY TABLE IF EXISTS _Transa;