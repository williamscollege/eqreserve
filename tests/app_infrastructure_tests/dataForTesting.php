<?php

require_once dirname(__FILE__) . '/../../classes/comm_pref.class.php';
require_once dirname(__FILE__) . '/../../classes/eq_group.class.php';
require_once dirname(__FILE__) . '/../../classes/eq_item.class.php';
require_once dirname(__FILE__) . '/../../classes/eq_subgroup.class.php';
require_once dirname(__FILE__) . '/../../classes/inst_group.class.php';
require_once dirname(__FILE__) . '/../../classes/inst_membership.class.php';
require_once dirname(__FILE__) . '/../../classes/permission.class.php';
//require_once dirname(__FILE__) . '/../../classes/reservation.class.php';
require_once dirname(__FILE__) . '/../../classes/role.class.php';
require_once dirname(__FILE__) . '/../../classes/time_block.class.php';
require_once dirname(__FILE__) . '/../../classes/time_block_group.class.php';
require_once dirname(__FILE__) . '/../../classes/user.class.php';

/*
This file contains a series of methods for creating known test data in a target database
*/


function createTestData_CommPrefs($dbConn) {
    // 100 series ids
}

function createTestData_EqGroups($dbConn) {
    # EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
    $addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES
        (201,'testEqGroup1','on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals','0,15,30,45',15,60,15,0),
        (202,'testEqGroup2','on the 1/2 hour with 30 minute min and 5 hour max by 30 minute intervals','0,30',30,300,30,0),
        (203,'testEqGroup3','on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals','0,15,30,45',15,60,15,0),
        (204,'testEqGroup4','on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals','0,15,30,45',15,60,15,0),
        (205,'testEqGroup5','deleted eq group','0,15,30,45',15,60,15,1),
        (206,'testEqGroup6','on the 1/4 hour with 15 minute min and 30 hour max by 15 minute intervals','0,15,30,45',15,1800,15,0),
        (207,'testEqGroup7','on the 1/4 hour with 15 minute min and 30 hour max by 15 minute intervals','0,15,30,45',15,1800,15,0),
        (208,'testEqGroup8','on the 1/4 hour with 15 minute min and 30 hour max by 15 minute intervals','0,15,30,45',15,1800,15,0)
    ";
    $addTestEqGroupsStmt = $dbConn->prepare($addTestEqGroupsSql);
    $addTestEqGroupsStmt->execute();
}

function createTestData_EqSubgroups($dbConn) {
    # EqSubgroup: eq_subgroup_id', 'eq_group_id', 'name','descr','ordering','flag_delete'
    $addTestEqSubgroupsSql  = "INSERT INTO " . EqSubgroup::$dbTable . " VALUES 
        (301,201,'testSubgroup1','normal',1,0),
        (302,201,'testSubgroup2','normal',2,0),
        (303,201,'testSubgroup3','same priority as prev',2,0),
        (304,201,'testSubgroup4','normal',3,0),
        (305,201,'testSubgroup5','deleted',4,1),
        (306,202,'testSubgroup1','same name, different group',1,0),
        (307,205,'testSubgroup6','group is deleted',1,0)
    ";
    $addTestEqSubgroupsStmt = $dbConn->prepare($addTestEqSubgroupsSql);
    $addTestEqSubgroupsStmt->execute();
}

function createTestData_EqItems($dbConn) {
    # EqItem: 'eq_item_id', 'eq_subgroup_id', 'name','descr','ordering','flag_delete'
    $addTestEqItemsSql  = "INSERT INTO " . EqItem::$dbTable . " VALUES 
        (401,301,'testItem1','normal',1,0),
        (402,301,'testItem2','normal',2,0),
        (403,301,'testItem3','same priority as prev',2,0),
        (404,301,'testItem4','normal',3,0),
        (405,301,'testItem5','deleted',4,1),
        (406,302,'testItem1','same name, different subgroup',1,0),
        (407,305,'testItem6','subgroup is deleted',1,0),
        (408,307,'testItem7','group is deleted',1,0)
    ";
    $addTestEqItemsStmt = $dbConn->prepare($addTestEqItemsSql);
    $addTestEqItemsStmt->execute();
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
}

function createTestData_InstMemberships($dbConn) {
    // 600 series ids
    # inst_memberships: inst_membership_id,user_id,inst_group_id,flag_delete
    $insertTestInstMembershipSql = "INSERT INTO ". InstMembership::$dbTable ." VALUES
        (601,1101,501,0),
        (602,1101,504,0), # deleted inst group
        (603,1101,505,1), # deleted membership
        (604,1102,502,0),
        (605,1103,501,0) # deleted user
    ";
    $insertTestInstMembershipStmt = $dbConn->prepare($insertTestInstMembershipSql);
    $insertTestInstMembershipStmt->execute();
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
    $addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES
        (701,1101,'user',     2,202,0), # user1 user access eqg2
        (702,1101,'user',     2,201,0), # user1 user access eqg1 (flipped to test ordering functions)
        (703,1101,'user',     1,203,0), # user1 manager access eqg3
        (704,1101,'user',     2,204,1), # user1 deleted access eqg4
        (705,1101,'user',     2,205,0), # user1 user access deleted eqg5
        (706,1101,'user',     2,207,0), # user1 user access to eqg 7
        (707,501,'inst_group',1,201,0), # ig1 has manager access to eqg1 (overrides user1 eqg6 user access)
        (708,501,'inst_group',2,202,0), # ig1 has user access to eqg2 (dual user access on user1 eqg2)
        (709,501,'inst_group',2,203,0), # ig1 has user access to eqg3 (overridden by user1 eqg2 manager access)
        (710,501,'inst_group',2,206,0), # ig1 has user access to eqg6 (gives user1 indirect user access)
        (711,502,'inst_group',2,201,0), # ig2 has user access to eqg1
        (712,502,'inst_group',2,204,0), # ig2 has user access to eqg4
        (713,502,'inst_group',2,207,1), # ig2 has deleted access to eqg7
        (714,1103,'user',     2,201,0), # deleted user3
        (715,504,'inst_group',2,206,0)  # deleted instgroup4
    ";
    $addTestPermissionStmt = $dbConn->prepare($addTestPermissionSql);
    $addTestPermissionStmt->execute();
}

function createTestData_Reservations($dbConn) {
    // 800 series ids
}

// NOTE: no role test data - the values in that table are fixed
//      role_id 1, priority 1,'Manager', not deleted
//      role_id 2, priority 2,'User', not deleted

function createTestData_TimeBlocks($dbConn) {
    // 900 series ids
}

function createTestData_TimeBlockGroups($dbConn) {
    // 1000 series ids
}

function createTestData_Users($dbConn) {
    // 1100 series ids
    # user: user_id, username, fname, lname, sortname, email, advisor, notes, flag_is_system_admin, flag_is_banned, flag_delete
    $addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES
        (1101,'".Auth_Base::$TEST_USERNAME."','".Auth_Base::$TEST_FNAME."','".Auth_Base::$TEST_LNAME."','".Auth_Base::$TEST_SORTNAME."','".Auth_Base::$TEST_EMAIL."','David Keiser-Clark','some important notes',0,0,0),
        (1102,'testUser2','tu2F','tu2L','tu2L, tu2F','tu2@inst.edu','tuAdvisor','tu2 notes',0,0,0),
        (1103,'testUser3deleted','tu3F','tu3L','tu3L, tu3F','tu3@inst.edu','tuAdvisor','tu3 notes',0,0,1),
        (1104,'testUser4banned','tu4F','tu4L','tu4L, tu4F','tu4@inst.edu','tuAdvisor','tu4 notes',0,1,0)
    ";
    $addTestUserStmt = $dbConn->prepare($addTestUserSql);
    $addTestUserStmt->execute();
}

//------------

function _removeTestDataFromTable($dbConn,$tableName) {
    $sql = "DELETE FROM $tableName";
    $stmt = $dbConn->prepare($sql);
    $stmt->execute();
}

function removeTestData_CommPrefs($dbConn) {
    _removeTestDataFromTable($dbConn,CommPref::$dbTable);
}

function removeTestData_EqGroups($dbConn) {
    _removeTestDataFromTable($dbConn,EqGroup::$dbTable);
}

function removeTestData_EqSubgroups($dbConn) {
    _removeTestDataFromTable($dbConn,EqSubgroup::$dbTable);
}

function removeTestData_EqItems($dbConn) {
    _removeTestDataFromTable($dbConn,EqItem::$dbTable);
}


function removeTestData_InstGroups($dbConn) {
    _removeTestDataFromTable($dbConn,InstGroup::$dbTable);
}

function removeTestData_InstMemberships($dbConn) {
    _removeTestDataFromTable($dbConn,InstMembership::$dbTable);
}

function removeTestData_Permissions($dbConn) {
    _removeTestDataFromTable($dbConn,Permission::$dbTable);
}

function removeTestData_Reservations($dbConn) {
    _removeTestDataFromTable($dbConn,Reservation::$dbTable);
}

function removeTestData_TimeBlocks($dbConn) {
    _removeTestDataFromTable($dbConn,TimeBlock::$dbTable);
}

function removeTestData_TimeBlockGroups($dbConn) {
    _removeTestDataFromTable($dbConn,TimeBlockGroup::$dbTable);
}

function removeTestData_Users($dbConn) {
    _removeTestDataFromTable($dbConn,User::$dbTable);
}
