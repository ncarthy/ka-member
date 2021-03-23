CREATE TEMPORARY TABLE IF NOT EXISTS _Dupes AS (      
						SELECT `t`.`member_idmember` as `idmember`, Min(idtransaction) as first_transaction
						FROM `transaction` t   
                        WHERE `date` >=  '2020-02-10'
                        AND `date` <= '2021-02-09'
                        GROUP BY `idmember`
                        HAVING Count(*) > 1                                                
                        );
                        
SELECT t.idmember,membershiptype,Name,businessname, CASE WHEN idtransaction = first_transaction THEN membershipfee ELSE 0 END as membershipfee, amount,`date` 
FROM vwTransaction t
JOIN _Dupes d ON t.idmember = d.idmember
WHERE `date` >=  '2020-02-10' AND `date` <= '2021-02-09'                   
ORDER BY t.idmember, `date`;

DROP TEMPORARY TABLE IF EXISTS  _Dupes;