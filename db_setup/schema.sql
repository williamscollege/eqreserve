/* 
SAVE:
	DB Creation and Maintanence Script

PROJECT:
	Equipment Reserve (eqreserve)

TODO:
	time_block_groups
	all TODO items

NOTES:
	reservations are for items, not groups. if a manager reserves a group, she is really reserving all items (i.e. to take the group offline for a period of time)

FOR TESTING ONLY:
	USE eqreserve;
	DROP TABLE `eqreserve`.`eq_groups`;
	DROP TABLE `eqreserve`.`eq_subgroups`;
	DROP TABLE `eqreserve`.`eq_items`;
	DROP TABLE `eqreserve`.`users`;
	DROP TABLE `eqreserve`.`inst_groups`;
	DROP TABLE `eqreserve`.`link_users_inst_groups`;
	DROP TABLE `eqreserve`.`comm_prefs`;
	DROP TABLE `eqreserve`.`roles`;
	DROP TABLE `eqreserve`.`link_entities_roles_eq_groups`;
	DROP TABLE `eqreserve`.`link_items_time_block_groups`;
	DROP TABLE `eqreserve`.`time_block_groups`;
	DROP TABLE `eqreserve`.`time_blocks`;
*/

CREATE SCHEMA IF NOT EXISTS
eqreserve;

USE eqreserve;


CREATE TABLE IF NOT EXISTS `eqreserve`.`eq_groups` (
    `eq_group_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NULL,
    `descr` TEXT NULL,
    `start_minute` VARCHAR(255) NULL,
    `min_duration_minutes` SMALLINT NOT NULL DEFAULT 30,
    `max_duration_minutes` SMALLINT NOT NULL DEFAULT 120,
    `duration_chunk_minutes` SMALLINT NOT NULL DEFAULT 30,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='top-level organizational unit; permisions/roles are managed with respect to eq_groups;';
/* start_minute: comma separated list of minutes of the hour on which a time block may be created (e.g. 0,30) */


CREATE TABLE IF NOT EXISTS `eqreserve`.`eq_subgroups` (
    `eq_subgroup_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eq_group_id` INT NOT NULL,
    `name` VARCHAR(255) NULL,
    `descr` TEXT NULL,
    `ordering` SMALLINT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: eq_groups.eq_group_id */


CREATE TABLE IF NOT EXISTS `eqreserve`.`eq_items` (
    `eq_item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eq_subgroup_id` INT NOT NULL,
    `name` VARCHAR(255) NULL,
    `descr` TEXT NULL,
    `ordering` SMALLINT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: eq_subgroups.eq_subgroup_id */


CREATE TABLE IF NOT EXISTS `eqreserve`.`users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NULL,
    `fname` VARCHAR(255) NULL,
    `lname` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `advisor` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `flag_is_banned` BIT(1) NOT NULL DEFAULT 0,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='linked to and derived from LDAP info';


CREATE TABLE IF NOT EXISTS `eqreserve`.`inst_groups` (
    `inst_group_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='inst_groups are linked to and derived from LDAP info;';
/* name: faculty, staff, student, org unit, classes/courses, none, etc. ("none" is implied by a lack of an entry in the link_user_groups table) */


CREATE TABLE IF NOT EXISTS `eqreserve`.`link_users_inst_groups` (
    `user_id` INT NOT NULL,
    `inst_group_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: users.user_id */


CREATE TABLE IF NOT EXISTS `eqreserve`.`comm_prefs` (
    `comm_pref_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `eq_group_id` INT NOT NULL,
    `flag_alert_on_upcoming_reservation` BIT(1) NOT NULL DEFAULT 0,
    `flag_contact_on_reserve_create` BIT(1) NOT NULL DEFAULT 0,
    `flag_contact_on_reserve_cancel` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='allows a user to set their communication preferences with respect to a group';
/* FK: users.user_id */


CREATE TABLE IF NOT EXISTS `eqreserve`.`roles` (
    `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* name: admin, manager, consumer ("none" is implied by a lack of an entry in the link_entities_roles_eq_groups table) */


CREATE TABLE IF NOT EXISTS `eqreserve`.`link_entities_roles_eq_groups` (
    `entity_type` VARCHAR(255) NULL,
    `role_id` INT NOT NULL,
    `eq_group_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='entity_id - foreign key into either the user table or the groups table; entity_type : user, group;';
/* This is an Entity Table (single inheritance table), meaning it is a linking table that links dependant upon the value of entity_type */
/* entity_type: user, group  */
/* FK: roles.role_id */
/* FK: eq_groups.eq_group_id */


CREATE TABLE IF NOT EXISTS `eqreserve`.`link_items_time_block_groups` (
    `eq_item_id` INT NOT NULL,
    `time_block_group_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: eq_items.eq_item_id */


CREATE TABLE IF NOT EXISTS `eqreserve`.`time_block_groups` (
    `time_block_group_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(255) NULL,
    `user_id` INT NOT NULL,
    `notes` TEXT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* type: manager_reserve, consumer_reserve */


CREATE TABLE IF NOT EXISTS `eqreserve`.`time_blocks` (
    `time_block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `time_block_group_id` INT NOT NULL,
    `start_time` DATETIME NULL,
    `end_time` DATETIME NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: time_block_groups.time_block_group_id */



