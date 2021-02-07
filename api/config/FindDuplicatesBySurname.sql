SELECT m.idmember,mn1.member_idmember, mn.*, mn1.* /*member_idmember*/
FROM member m
JOIN membername mn ON m.idmember = mn.member_idmember
JOIN membername mn1 ON mn.surname = mn1.surname AND mn.member_idmember != mn1.member_idmember
JOIN member m2 ON mn1.member_idmember = m2.idmember
WHERE m.deletedate IS NULL AND m2.deletedate IS NULL
ORDER BY mn.surname


