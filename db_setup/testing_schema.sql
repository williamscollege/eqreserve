/* 
PROJECT:
	Equipment Reserve (eqreserve)

SQL DESCR:
    This creates a table that is used by the testing suites to verify database connection functionality and to test the DB linking class
    
NOTE:
    This is for development only! You do not need this on the production server (though it shouldn't hurt anything to have it there)

*/

# Select which database you wish to run this script against
# USE eqreserve;
USE eqreservetest;

CREATE TABLE IF NOT EXISTS `dblinktest` (
    `dblinktest_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `charfield` VARCHAR(255) NULL,
    `intfield` INT NOT NULL,
    `flagfield` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='used for testing based DB-link class';


