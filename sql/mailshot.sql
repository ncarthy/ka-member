SELECT m.idmember, 
	IFNULL(GROUP_CONCAT( CONCAT(CASE
						WHEN `mn`.`honorific` = '' THEN ''
						ELSE CONCAT(`mn`.`honorific`, ' ')
					END,
					CASE
						WHEN `mn`.`firstname` = '' THEN ''
						ELSE CONCAT(`mn`.`firstname`, ' ')
					END,
					`mn`.`surname`) SEPARATOR ' & '),
			'') AS `name`,
	IFNULL(`m`.`title`,'') AS `title`,        
	IFNULL(`m`.`businessname`,'') AS `businessname`,
	CASE
		WHEN
			`m`.`countryID` <> 186
				AND `m`.`country2ID` = 186
		THEN
			`m`.`addressfirstline2`
		ELSE `m`.`addressfirstline`
	END AS `addressfirstline`,
	CASE
		WHEN
			`m`.`countryID` <> 186
				AND `m`.`country2ID` = 186
		THEN
			`m`.`addresssecondline2`
		ELSE `m`.`addresssecondline`
	END AS `addresssecondline`,
	CASE
		WHEN
			`m`.`countryID` <> 186
				AND `m`.`country2ID` = 186
		THEN
			`m`.`city2`
		ELSE `m`.`city`
	END AS `city`,
	CASE
		WHEN
			`m`.`countryID` <> 186
				AND `m`.`country2ID` = 186
		THEN
			`m`.`postcode2`
		ELSE `m`.`postcode`
	END AS `postcode`,
	CASE
		WHEN
			`m`.`countryID` <> 186
				AND `m`.`country2ID` = 186
		THEN
			`m`.`country2ID`
		ELSE `m`.`countryID`
	END AS `countryID`
	FROM `member` `m`
	LEFT JOIN membername `mn` ON `m`.`idmember` = mn.member_idmember
	WHERE `m`.postonhold = 0 AND 							# Not post on hold
		`m`.membership_idmembership NOT IN (7,8,9) AND      # Active member
		IFNULL(m.addressfirstline,'') != ''					# Valid Address
	GROUP BY m.idmember
	HAVING countryID = 186									# UK only
	ORDER BY postcode;