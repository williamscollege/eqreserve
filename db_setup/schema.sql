/* 
SAVE:
	DB Creation and Maintanence Script

PROJECT:
	Equipment Reserve (eqreserve)

TODO:
	schedules
	all TODO items

NOTES:
	reservations are for items, not groups. if a manager reserves a group, she is really reserving all items (i.e. to take the group offline for a period of time)

FOR TESTING ONLY:
	DROP TABLE `eq_groups`;
	DROP TABLE `eq_subgroups`;
	DROP TABLE `eq_items`;
	DROP TABLE `users`;
	DROP TABLE `inst_groups`;
	DROP TABLE `inst_memberships`;
	DROP TABLE `comm_prefs`;
	DROP TABLE `roles`;
	DROP TABLE `permissions`;
	DROP TABLE `schedules`;
	DROP TABLE `reservations`;
	DROP TABLE `time_blocks`;
	DROP TABLE `queued_messages`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to run this script against
# ----------------------------
-- USE eqreserve;
USE eqreservetest;


CREATE TABLE IF NOT EXISTS `eq_groups` (
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


CREATE TABLE IF NOT EXISTS `eq_subgroups` (
    `eq_subgroup_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eq_group_id` INT NOT NULL,
    `name` VARCHAR(255) NULL,
    `descr` TEXT NULL,
    `ordering` SMALLINT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0,
    `flag_is_multi_select` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: eq_groups.eq_group_id */


CREATE TABLE IF NOT EXISTS `eq_items` (
    `eq_item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eq_subgroup_id` INT NOT NULL,
    `name` VARCHAR(255) NULL,
    `descr` TEXT NULL,
    `ordering` SMALLINT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: eq_subgroups.eq_subgroup_id */


CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NULL,
    `fname` VARCHAR(255) NULL,
    `lname` VARCHAR(255) NULL,
    `sortname` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `advisor` VARCHAR(255) NULL,
    `notes` TEXT NULL,
	`flag_is_system_admin` BIT(1) NOT NULL DEFAULT 0,
	`flag_is_banned` BIT(1) NOT NULL DEFAULT 0,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='linked to and derived from remote auth source info';


CREATE TABLE IF NOT EXISTS `inst_groups` (
    `inst_group_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='inst_groups are linked to and derived from LDAP info;';
/* name: faculty, staff, student, org unit, classes/courses, none, etc. ("none" is implied by a lack of an entry in the link_user_groups table) */


CREATE TABLE IF NOT EXISTS `inst_memberships` (
    `inst_membership_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `inst_group_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='link between users and inst groups';
/* FK: users.user_id */


CREATE TABLE IF NOT EXISTS `comm_prefs` (
    `comm_pref_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `eq_group_id` INT NOT NULL,
    `flag_alert_on_upcoming_reservation` BIT(1) NOT NULL DEFAULT 0,
    `flag_contact_on_reserve_create` BIT(1) NOT NULL DEFAULT 0,
    `flag_contact_on_reserve_cancel` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='allows a user to set their communication preferences with respect to a group';
/* FK: users.user_id */


CREATE TABLE IF NOT EXISTS `roles` (
    `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`priority` INT NOT NULL,    
	`name` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='determines allowable actions within the eqreserve system';
/* name: admin, manager, consumer ("none" is implied by a lack of an entry in the permissions table) */
/* priority: Highest admin role is priority = 1; lowest anonymous/guest priority is > 1 */
 

CREATE TABLE IF NOT EXISTS `permissions` (
    `permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `entity_id`  INT NOT NULL,
	`entity_type` VARCHAR(255) NULL,
    `role_id` INT NOT NULL,
    `eq_group_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='entity_id - foreign key into either the user table or the inst_groups table; entity_type : user, inst_group;';
/* This is an Entity Table (single inheritance table), meaning it is a linking table that links dependant upon the value of entity_type */
/* FK: entity_id: this is the FK that will link this roles record with either the users.user_id OR inst_groups.inst_group_id  */
/* entity_type: user, inst_group  */
/* FK: roles.role_id */
/* FK: eq_groups.eq_group_id */


CREATE TABLE IF NOT EXISTS `schedules` (
    `schedule_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(255) NULL,
    `user_id` INT NOT NULL,
    `notes` TEXT NULL,
    `frequency_type` VARCHAR(255) NULL, /* no_repeat, weekly, monthly */
    `repeat_interval` INT DEFAULT 1, /* repeat for 1 to 30 times (weekly or monthly) */
    `which_days` VARCHAR(255) NOT NULL DEFAULT 'none', /* none OR weekly: mon,wed,fri OR monthly: 1,4,9,16,30 */
	`timeblock_start_time` TIME NULL, /*  */
	`timeblock_duration` VARCHAR(255) NULL, /* supports standard PHP DateInterval formats */
	`start_on_date` DATE NULL, /*  */
    `end_on_date` DATE NULL, /*  */
	`summary` TEXT NULL, /*  */
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='collects multiple time blocks into a related group';
/* type: consumer, manager */
/* frequency_type: no_repeat, weekly, monthly */


CREATE TABLE IF NOT EXISTS `reservations` (
    `reservation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `eq_item_id` INT NOT NULL,
    `schedule_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='links an eq_item to blocks of time (i.e. a time block group)';
/* FK: eq_items.eq_item_id */


CREATE TABLE IF NOT EXISTS `time_blocks` (
    `time_block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `schedule_id` INT NOT NULL,
    `start_datetime` DATETIME NULL,
    `end_datetime` DATETIME NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';
/* FK: schedules.schedule_id */

CREATE TABLE IF NOT EXISTS `queued_messages` (
  `queued_message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `delivery_type` VARCHAR(16) NULL, /*email (future may support other types such as sms/text) */
  `flag_is_delivered` BIT(1) NOT NULL DEFAULT 0,
  `hold_until_datetime` DATETIME NULL,
  `target` VARCHAR(255) NULL, /*email address, or perhaps phone number or other contact address/target */
  `summary` VARCHAR(255) NULL, /* short version / description; used as subject for email messages */
  `body` TEXT NULL,
  `action_datetime` DATETIME NULL,
  `action_status` VARCHAR(16) NULL, /* SUCCESS|FAILURE */
  `action_notes` TEXT NULL, /* any more detailed messages/notes about the action */
  `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='';


#####################
# The Absolute Minimalist Approach to Initial Data Population
#####################

# Required constant values for roles table
INSERT INTO 
	roles
VALUES
	(1,1,'Manager',0),
	(2,2,'User',0)
