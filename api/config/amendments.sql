CREATE TABLE `knightsb_membership`.`bankaccount` ( 
	`bankID` INT NOT NULL AUTO_INCREMENT , 
	`name` VARCHAR(255) NOT NULL , 
	PRIMARY KEY (`bankID`)
) ENGINE = InnoDB COMMENT = 'List of Bank accounts for transactions';