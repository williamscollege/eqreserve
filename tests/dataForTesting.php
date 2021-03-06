<?php
	require_once dirname(__FILE__) . '/../classes/auth_base.class.php';
	require_once dirname(__FILE__) . '/../classes/auth_LDAP.class.php';

	require_once dirname(__FILE__) . '/../classes/comm_pref.class.php';
	require_once dirname(__FILE__) . '/../classes/eq_group.class.php';
	require_once dirname(__FILE__) . '/../classes/eq_item.class.php';
	require_once dirname(__FILE__) . '/../classes/eq_subgroup.class.php';
	require_once dirname(__FILE__) . '/../classes/inst_group.class.php';
	require_once dirname(__FILE__) . '/../classes/inst_membership.class.php';
	require_once dirname(__FILE__) . '/../classes/permission.class.php';
    require_once dirname(__FILE__) . '/../classes/queued_message.class.php';
	require_once dirname(__FILE__) . '/../classes/reservation.class.php';
	require_once dirname(__FILE__) . '/../classes/role.class.php';
	require_once dirname(__FILE__) . '/../classes/time_block.class.php';
	require_once dirname(__FILE__) . '/../classes/schedule.class.php';
	require_once dirname(__FILE__) . '/../classes/user.class.php';

	/*
	This file contains a series of methods for creating known test data in a target database
	*/

	function createTestData_CommPrefs($dbConn) {
		// 100 series ids
		// comm_prefs: comm_pref_id, user_id, eq_group_id,
		//             flag_alert_on_upcoming_reservation, flag_contact_on_reserve_create, flag_contact_on_reserve_cancel
		// ORIGINAL DATA
		//        (101,1101,201,0,0,0),
		//        (102,1101,202,1,0,0),
		//        (103,1101,203,0,1,0),
		//        (105,1101,207,0,0,1),
		//        (106,1102,207,1,0,0),
		//        (107,1106,201,1,1,1),
		//        (108,1102,202,1,0,0)
		$addTestCommPrefsSql  = "INSERT INTO " . CommPref::$dbTable . " VALUES
		(101,1101,201,0,0,0),
		(102,1101,202,1,0,0),
		(103,1101,203,0,1,0),
		(105,1101,207,0,0,1),
		(106,1101,206,1,1,1),
		(107,1102,207,1,0,0),
		(108,1102,202,1,0,0),
		(109,1102,201,1,1,1),
		(110,1102,204,1,1,1),
		(111,1105,201,1,1,1),
		(112,1105,202,1,1,1),
		(113,1105,203,1,1,1),
		(114,1105,204,1,1,1),
		(115,1105,206,1,1,1),
		(116,1105,207,1,1,1),
		(117,1105,208,1,1,1),
		(118,1106,201,1,1,1)
     ";
		$addTestCommPrefsStmt = $dbConn->prepare($addTestCommPrefsSql);
		$addTestCommPrefsStmt->execute();
		if ($addTestCommPrefsStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test CommPrefs data to the DB\n";
			print_r($addTestCommPrefsStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_EqGroups($dbConn) {
		# EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
		$addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES
        (201,'testEqGroup1','on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals',NULL,'0,15,30,45',15,60,15,0),
        (202,'testEqGroup2','on the 1/2 hour with 30 minute min and 5 hour max by 30 minute intervals',NULL,'0,30',30,300,30,0),
        (203,'testEqGroup3','on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals',NULL,'0,15,30,45',15,60,15,0),
        (204,'testEqGroup4','on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals',NULL,'0,15,30,45',15,60,15,0),
        (205,'testEqGroup5','deleted eq group',NULL,'0,15,30,45',15,60,15,1),
        (206,'testEqGroup6','on the 1/4 hour with 15 minute min and 30 hour max by 15 minute intervals',NULL,'0,15,30,45',15,1800,15,0),
        (207,'testEqGroup7','on the 1/4 hour with 15 minute min and 30 hour max by 15 minute intervals',NULL,'0,15,30,45',15,1800,15,0),
        (208,'testEqGroup8','no one has access; on the 1/4 hour with 15 minute min and 30 hour max by 15 minute intervals',NULL,'0,15,30,45',15,1800,15,0)
    ";
		$addTestEqGroupsStmt = $dbConn->prepare($addTestEqGroupsSql);
		$addTestEqGroupsStmt->execute();
		if ($addTestEqGroupsStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test EqGroups data to the DB\n";
			print_r($addTestEqGroupsStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_EqSubgroups($dbConn) {
		# EqSubgroup: eq_subgroup_id', 'eq_group_id', 'name','descr','ordering','flag_delete'
		$addTestEqSubgroupsSql  = "INSERT INTO " . EqSubgroup::$dbTable . " VALUES
        (301,201,'testSubgroup1','normal',NULL,1,0,0),
        (302,201,'testSubgroup2','normal',NULL,2,1,0),
        (303,201,'testSubgroup3','normal and same priority as prev',NULL,2,0,0),
        (304,201,'testSubgroup4','normal',NULL,3,1,0),
        (305,201,'testSubgroup5','deleted',NULL,4,0,1),
        (306,202,'testSubgroup1','normal and same name, different group',NULL,1,1,0),
        (307,205,'testSubgroup6','group is deleted',NULL,1,0,0),
        (308,207,'testSubgroup7','normal',NULL,50,0,0),
        (309,203,'testSubgroup1','normal and same name, different group',NULL,1,1,0),
        (310,207,'testSubgroup8','normal',NULL,51,1,0)
    ";
		$addTestEqSubgroupsStmt = $dbConn->prepare($addTestEqSubgroupsSql);
		$addTestEqSubgroupsStmt->execute();
		if ($addTestEqSubgroupsStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test EqSubgroups data to the DB\n";
			print_r($addTestEqSubgroupsStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_EqItems($dbConn) {
		# EqItem: 'eq_item_id', 'eq_subgroup_id', 'name','descr','image_file_name','flag_image_to_be_uploaded','ordering','flag_delete'
		$addTestEqItemsSql  = "INSERT INTO " . EqItem::$dbTable . " VALUES
        (401,301,'testItem1','normal',NULL,'',0,1,0),
        (402,301,'testItem2','normal',NULL,'',0,2,0),
        (403,301,'testItem3','same priority as prev',NULL,'',0,2,0),
        (404,301,'testItem4','normal',NULL,'',0,3,0),
        (405,301,'testItem5','deleted',NULL,'',0,4,1),
        (406,302,'testItem1','same name, different subgroup',NULL,'',0,1,0),
        (407,305,'testItem6','subgroup is deleted',NULL,'',0,1,0),
        (408,307,'testItem7','group is deleted',NULL,'',0,1,0),
        (409,306,'testItem8','normal',NULL,'',0,10,0),
        (410,308,'testItem9','normal',NULL,'',0,20,0),
        (411,306,'testItem10','normal',NULL,'',0,12,0),
        (412,302,'testItem11','another item, different subgroup',NULL,'',0,25,0),
        (413,309,'testItem12','another item, different subgroup',NULL,'',0,1,0),
        (414,310,'testItem13','normal',NULL,'',0,21,0),
        (415,309,'testItem14','another item, same subgroup',NULL,'',0,2,0)
    ";
		$addTestEqItemsStmt = $dbConn->prepare($addTestEqItemsSql);
		$addTestEqItemsStmt->execute();
		if ($addTestEqItemsStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test EqItems data to the DB\n";
            print_r($addTestEqItemsStmt->errorInfo());
            echo "\n".$addTestEqItemsSql."\n";
			debug_print_backtrace();
			exit;
		}
	}


	function createTestData_InstGroups($dbConn) {
		# InstGroup: inst_group_id, name, flag_delete
		$addTestInstGroupsSql  = "INSERT INTO " . InstGroup::$dbTable . " VALUES
        (501,'testInstGroup1',0), # user 1 a member of this, user 3 (deleted) a member of this
        (502,'testInstGroup2',0), # user 2 a member of this
        (503,'testInstGroup3',0), # no one a member of this
        (504,'testInstGroup4',1), # user 1 a member of this
        (505,'testInstGroup5',0)  # user 1 has a deleted membership for this
    ";
		$addTestInstGroupsStmt = $dbConn->prepare($addTestInstGroupsSql);
		$addTestInstGroupsStmt->execute();
		if ($addTestInstGroupsStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test InstGroups data to the DB\n";
			print_r($addTestInstGroupsStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_InstMemberships($dbConn) {
		// 600 series ids
		# inst_memberships: inst_membership_id,user_id,inst_group_id,flag_delete
		$insertTestInstMembershipSql  = "INSERT INTO " . InstMembership::$dbTable . " VALUES
        (601,1101,501,0),
        (602,1101,504,0), # deleted inst group
        (603,1101,505,1), # deleted membership
        (604,1102,502,0),
        (605,1103,501,0) # deleted user
    ";
		$insertTestInstMembershipStmt = $dbConn->prepare($insertTestInstMembershipSql);
		$insertTestInstMembershipStmt->execute();
		if ($insertTestInstMembershipStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test InstMemberships data to the DB\n";
			print_r($insertTestInstMembershipStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Permissions($dbConn) {
		# Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
		# NOTE: no one has access to eqg8
		# NOTE: user1 no direct access to eqg 6
		# NOTE: user1 is member of instgroup1
		# NOTE: user1 is NOT member of instgroup2
		# NOTE: user2 is a member of inst group 2
		# NOTE: user3 is deleted
		# NOTE: instgroup4 is deleted
		# NOTE: over all-
		#    user 1101 has access to 201(m), 202, 203(m), 206, 207
		$addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES
        (701,1101,'user',     2,202,0), # user1 user access eqg2
        (702,1101,'user',     2,201,0), # user1 user access eqg1 (flipped to test ordering functions)
        (703,1101,'user',     1,203,0), # user1 manager access eqg3
        (704,1101,'user',     2,204,1), # user1 deleted access eqg4
        (705,1101,'user',     2,205,0), # user1 user access deleted eqg5
        (706,1101,'user',     2,207,0), # user1 user access to eqg 7
        (707,501,'inst_group',1,201,0), # ig1 has manager access to eqg1 (overrides user1 eqg1 user access)
        (708,501,'inst_group',2,202,0), # ig1 has user access to eqg2 (dual user access on user1 eqg2)
        (709,501,'inst_group',2,203,0), # ig1 has user access to eqg3 (overridden by user1 eqg2 manager access)
        (710,501,'inst_group',2,206,0), # ig1 has user access to eqg6 (gives user1 indirect user access)
        (711,502,'inst_group',2,201,0), # ig2 has user access to eqg1
        (712,502,'inst_group',2,204,0), # ig2 has user access to eqg4
        (713,502,'inst_group',2,207,1), # ig2 has deleted access to eqg7
        (714,1103,'user',     2,201,0), # deleted user3
        (715,504,'inst_group',2,206,0), # deleted instgroup4
        (716,1102,'user',     2,207,0), # user2 user access to eqg 7
        (717,1102,'user',     1,202,0), # user2 manager access to eqg 2
        (718,1102,'user',     2,201,0), # user2 user access to eqg 1
        (719,1106,'user',     1,201,0)  # user6 manager access to eqg 1
    ";
		$addTestPermissionStmt = $dbConn->prepare($addTestPermissionSql);
		$addTestPermissionStmt->execute();
		if ($addTestPermissionStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Permissions data to the DB\n";
			print_r($addTestPermissionStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Schedules($dbConn) {
		// 1000 series ids
		// schedule: schedule_id, type, user_id, notes, frequency_type, repeat_interval, which_days, timeblock_start_time, timeblock_duration, start_on_date, end_on_date, summary, flag_delete
		$addTestScheduleSql  = "INSERT INTO " . Schedule::$dbTable . " VALUES
        (1001,'consumer',1101,'notes1 with 1 block','no_repeat',1,'none','10:00:00','15M','2013-03-22','2013-03-22','Once 1 time',0),         # single time block in the schedule, 1 item
        (1002,'consumer',1101,'notes2 normal with 3 blocks','weekly',1,'tue','10:00:00','30M','2013-03-26','2013-04-09','Every 1 weeks at 10:00 AM for 30 minutes on (Tuesday), until 2013-04-09',0), # three time blocks in the schedule, 1 item
        (1003,'consumer',1101,'notes3 deleted time block','no_repeat',1,'none','2013-03-22 19:00:00','60M','2013-03-22','2013-03-22','Once 1 time',0),           # single deleted time block in the schedule
        (1004,'consumer',1101,'notes4 deleted schedule','no_repeat',1,'none','18:00:00','60M','2013-03-22','2013-03-22','Once 1 time',1),              # schedule is deleted
        (1005,'consumer',1101,'notes5 deleted reservation','no_repeat',1,'none','16:00:00','60M','2013-03-22','2013-03-22','Once 1 time',0),  # reservations is deleted
        (1006,'manager', 1101,'notes6 manager','no_repeat',1,'none','18:00:00','60M','2013-03-25','2013-03-25','Once 1 time',0),              # manager reservation, 1 item
        (1007,'consumer',1102,'notes7 other user','no_repeat',1,'none','18:00:00','60M','2013-03-25','2013-03-25','Once 1 time',0),           # other user single time block in the schedule
        (1008,'manager', 1102,'notes8 other user manager','no_repeat',1,'none','18:00:00','60M','2013-03-25','2013-03-25','Once 1 time',0),   # other user single time block in the schedule
        (1009,'consumer',1103,'notes9 2 items','no_repeat',1,'none','18:00:00','60M','2013-03-26','2013-03-26','Once 1 time',0),              # single time block in the schedule, 2 items reserved
        (1010,'consumer',1101,'notes10 2 items not managed eq group','no_repeat',1,'none','13:00:00','60M','2013-04-18','2013-04-18','Once 1 time',0),  # single time block in the schedule, 1 items reserved, eq group not managed
    	(1011,'manager', 1101,'notes11 1 item managed eq group','no_repeat',1,'none','11:00:00','15M','2013-07-09','2013-07-09','Once 1 time',0)  # manager reservation, 1 item
    ";
		$addTestScheduleStmt = $dbConn->prepare($addTestScheduleSql);
		$addTestScheduleStmt->execute();
		if ($addTestScheduleStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Schedules data to the DB\n";
			print_r($addTestScheduleStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Reservations($dbConn) {
		// 800 series ids
		// reservation: reservation_id, eq_item_id, schedule_id, flag_delete
		$addTestReservationSql  = "INSERT INTO " . Reservation::$dbTable . " VALUES
        (801,401,1001,0), # single time block in the group, 1 item
        (802,402,1002,0), # three time blocks in the group, 1 item
        (803,403,1003,0), # single deleted time block in the group
        (804,404,1004,0), # schedule is deleted
        (805,406,1005,1), # reservations is deleted
        (806,401,1006,0), # user 1 manager reservation, 1 item
        (807,410,1007,0), # other user consumer 1 item
        (808,409,1008,0), # other user manager 1 item
        (809,401,1009,0), # single time block in the group, 2 items reserved
        (810,402,1009,0), #
        (811,409,1010,0), # primary user manager 2 items, non-managed group
        (812,411,1010,0), # primary user manager 2 items, non-managed group
        (813,413,1011,0)  # user 1 manager reservation, 1 item
    ";
		$addTestReservationStmt = $dbConn->prepare($addTestReservationSql);
		$addTestReservationStmt->execute();
		if ($addTestReservationStmt->errorInfo()[0] != '0000') {
			echo "<pre>$addTestReservationSql\nerror adding test Reservations data to the DB\n";
			print_r($addTestReservationStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	// NOTE: no role test data - the values in that table are fixed
	//      role_id 1, priority 1,'Manager', not deleted
	//      role_id 2, priority 2,'User', not deleted

	function createTestData_TimeBlocks($dbConn) {
		// time_block: time_block_id, schedule_id, start_datetime, end_datetime, flag_delete
		$addTestTimeBlockSql  = "INSERT INTO " . TimeBlock::$dbTable . " VALUES
        (901,1001,'2013-03-22 10:00:00','2013-03-22 10:15:00',0), # single time block in the group
        (902,1002,'2013-03-26 10:00:00','2013-03-26 10:30:00',0), # three time blocks in the group
        (903,1002,'2013-04-02 10:00:00','2013-04-02 10:30:00',0),
        (904,1002,'2013-04-09 10:00:00','2013-04-09 10:30:00',0),
        (905,1003,'2013-03-22 19:00:00','2013-03-22 20:00:00',1), # time block deleted
        (906,1004,'2013-03-22 18:00:00','2013-03-22 19:00:00',0), # time block group deleted
        (907,1005,'2013-03-22 16:00:00','2013-03-22 17:00:00',0), # reservation deleted
        (908,1006,'2013-03-25 18:00:00','2013-03-25 19:00:00',0), # manager reservation, 1 item
        (909,1007,'2013-03-25 18:00:00','2013-03-25 19:00:00',0), # other user single time block in the group
        (910,1008,'2013-03-25 18:00:00','2013-03-25 19:00:00',0), # other user single time block in the group
        (911,1009,'2013-03-26 18:00:00','2013-03-26 19:00:00',0), # single time block in the group, 2 items reserved
        (912,1010,'2013-04-18 13:00:00','2013-04-18 17:00:00',0), # single time block in the not-managed group, 1 item reserved
        (913,1011,'2013-07-09 11:00:00','2013-07-09 11:15:00',0) # manager reservation, 1 item
    ";
		$addTestTimeBlockStmt = $dbConn->prepare($addTestTimeBlockSql);
		$addTestTimeBlockStmt->execute();
		if ($addTestTimeBlockStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test TimeBlocks data to the DB\n";
			print_r($addTestTimeBlockStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Users($dbConn) {
		// 1100 series ids
		# user: user_id, username, fname, lname, sortname, email, advisor, notes, flag_is_system_admin, flag_is_banned, flag_delete
		$addTestUserSql  = "INSERT INTO " . User::$dbTable . " VALUES
        (1101,'" . Auth_Base::$TEST_USERNAME . "','" . Auth_Base::$TEST_FNAME . "','" . Auth_Base::$TEST_LNAME . "','" . Auth_Base::$TEST_SORTNAME . "','" . Auth_Base::$TEST_EMAIL . "','David Keiser-Clark','some important notes',0,0,0),
        (1102,'testUser2','tu2F','tu2L','tu2L, tu2F','tu2@inst.edu','tu2Advisor','tu2 notes',0,0,0),
        (1103,'testUser3deleted','tu3F','tu3L','tu3L, tu3F','tu3@inst.edu','tu3Advisor','tu3 notes',0,0,1),
        (1104,'testUser4banned','tu4F','tu4L','tu4L, tu4F','tu4@inst.edu','tu4Advisor','tu4 notes',0,1,0),
        (1105,'testUser5SystemAdmin','tu5F','tu5L','tu5L, tu5F','tu5@inst.edu','tu5Advisor','tu5 notes',1,0,0),
        (1106,'testUser6','tu6F','tu6L','tu6L, tu6F','tu6@inst.edu','tu6Advisor','tu6 notes',0,0,0),
        (1107,'testUser7','tu7F','tu7L','tu7L, tu7F','tu7@inst.edu','tu7Advisor','tu7 notes',0,0,0)
    ";
		$addTestUserStmt = $dbConn->prepare($addTestUserSql);
		$addTestUserStmt->execute();
		if ($addTestUserStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Users data to the DB\n";
			print_r($addTestUserStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

    function createTestData_QueuedMessages($dbConn) {
        // 1200 series ids
        // queued_message: queued_message_id, delivery_type, flag_is_delivered, hold_until_datetime, target, summary, body, action_datetime, action_status, action_notes, flag_delete
        $addTestQueuedMessageSql  = "INSERT INTO " . QueuedMessage::$dbTable . " VALUES
            (1201, 'email', 0, '', '" . Auth_Base::$TEST_EMAIL . "', 'TEST: basic email message', 'TEST BODY: basic email message basic email message', '', '', '', 0),
            (1202, 'email', 1, '', '" . Auth_Base::$TEST_EMAIL . "', 'TEST: already delivered email message', 'TEST BODY: already delivered email message already delivered email message', NOW() - INTERVAL 1 DAY, 'SUCCESS', CONCAT('SUCCESS: delivered at ',DATE_FORMAT(NOW() - INTERVAL 1 DAY,'%Y-%m-%d %H:%i:%s')), 0),
            (1203, 'email', 0, '', '" . Auth_Base::$TEST_EMAIL . "', 'TEST: previous delivery failed email message', 'TEST BODY: previous delivery failed email message previous delivery failed email message', NOW() - INTERVAL 1 DAY, 'FAILURE', CONCAT('FAILURE: intentional fail at ',DATE_FORMAT(NOW() - INTERVAL 1 DAY,'%Y-%m-%d %H:%i:%s')), 0),
            (1204, 'email', 0, NOW() + INTERVAL 1 DAY, '" . Auth_Base::$TEST_EMAIL . "', 'TEST: hold until this time tomorrow email message', 'TEST BODY: hold until this time tomorrow email message hold until this time tomorrow email message', '', '', '', 0),
            (1205, 'email', 0, '', '" . Auth_Base::$TEST_EMAIL . "', 'TEST: deleted message', 'TEST BODY: deleted message deleted message', '', '', '', 1),
            (1206, 'email', 0, '', '', 'TEST: missing target message', 'TEST BODY: missing target message missing target message', '', '', '', 0),
            (1207, 'email', 0, '', 'fred', 'TEST: bad/invalid target message', 'TEST BODY: bad/invalid target message bad/invalid target message', '', '', '', 0),
            (1208, 'email', 0, '', '" . Auth_Base::$TEST_EMAIL . "', '', 'TEST BODY: empty summary message empty summary message', '', '', '', 0),
            (1209, 'email', 0, '', '" . Auth_Base::$TEST_EMAIL . "', 'TEST: empty body message', '', '', '', '', 0),
            (1210, 'fred', 0, '', '" . Auth_Base::$TEST_EMAIL . "', 'TEST: unsupported message type', 'TEST BODY: unsupported message type unsupported message type', '', '', '', 0)
        ";
        $addTestQueuedMessageStmt = $dbConn->prepare($addTestQueuedMessageSql);
        $addTestQueuedMessageStmt->execute();
        if ($addTestQueuedMessageStmt->errorInfo()[0] != '0000') {
            echo "<pre>$addTestQueuedMessageSql\nerror adding test QueuedMessages data to the DB\n";
            print_r($addTestQueuedMessageStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

	function makeAuthedTestUserAdmin($dbConn) {
		$u1                       = User::getOneFromDb(['username' => TESTINGUSER], $dbConn);
		$u1->flag_is_system_admin = TRUE;
		$u1->updateDb();
	}

	function createAllTestData($dbConn) {
		createTestData_CommPrefs($dbConn);
		createTestData_EqGroups($dbConn);
		createTestData_EqSubgroups($dbConn);
		createTestData_EqItems($dbConn);
		createTestData_InstGroups($dbConn);
		createTestData_InstMemberships($dbConn);
		createTestData_Permissions($dbConn);
		createTestData_Reservations($dbConn);
		createTestData_TimeBlocks($dbConn);
		createTestData_Schedules($dbConn);
		createTestData_Users($dbConn);
        createTestData_QueuedMessages($dbConn);
	}

	//------------

	function _removeTestDataFromTable($dbConn, $tableName) {
		$sql = "DELETE FROM $tableName";
		//echo "<pre>" . $sql . "\n</pre>";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

	function removeTestData_CommPrefs($dbConn) {
		_removeTestDataFromTable($dbConn, CommPref::$dbTable);
	}

	function removeTestData_EqGroups($dbConn) {
		_removeTestDataFromTable($dbConn, EqGroup::$dbTable);
	}

	function removeTestData_EqSubgroups($dbConn) {
		_removeTestDataFromTable($dbConn, EqSubgroup::$dbTable);
	}

	function removeTestData_EqItems($dbConn) {
		_removeTestDataFromTable($dbConn, EqItem::$dbTable);
	}


	function removeTestData_InstGroups($dbConn) {
		_removeTestDataFromTable($dbConn, InstGroup::$dbTable);
	}

	function removeTestData_InstMemberships($dbConn) {
		_removeTestDataFromTable($dbConn, InstMembership::$dbTable);
	}

	function removeTestData_Permissions($dbConn) {
		_removeTestDataFromTable($dbConn, Permission::$dbTable);
	}

	function removeTestData_Reservations($dbConn) {
		_removeTestDataFromTable($dbConn, Reservation::$dbTable);
	}

	function removeTestData_TimeBlocks($dbConn) {
		_removeTestDataFromTable($dbConn, TimeBlock::$dbTable);
	}

	function removeTestData_Schedules($dbConn) {
		_removeTestDataFromTable($dbConn, Schedule::$dbTable);
	}

	function removeTestData_Users($dbConn) {
		_removeTestDataFromTable($dbConn, User::$dbTable);
	}

    function removeTestData_QueuedMessages($dbConn) {
		_removeTestDataFromTable($dbConn, QueuedMessage::$dbTable);
	}


	function removeAllTestData($dbConn) {
		removeTestData_CommPrefs($dbConn);
		removeTestData_EqGroups($dbConn);
		removeTestData_EqSubgroups($dbConn);
		removeTestData_EqItems($dbConn);
		removeTestData_InstGroups($dbConn);
		removeTestData_InstMemberships($dbConn);
		removeTestData_Permissions($dbConn);
		removeTestData_Reservations($dbConn);
		removeTestData_TimeBlocks($dbConn);
		removeTestData_Schedules($dbConn);
		removeTestData_Users($dbConn);
        removeTestData_QueuedMessages($dbConn);
	}