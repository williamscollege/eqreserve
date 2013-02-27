<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/inst_group.class.php';


class TestOfInstGroup extends UnitTestCaseDB {
	
	
	function setUp() {
        $addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES (1,'".Auth_Base::$TEST_USERNAME."','".Auth_Base::$TEST_FNAME."','".Auth_Base::$TEST_LNAME."','".Auth_Base::$TEST_SORTNAME."','".Auth_Base::$TEST_EMAIL."','David Keiser-Clark','some important notes',0,0)";
		$addTestUserStmt = $this->DB->prepare($addTestUserSql);
		$addTestUserStmt->execute();

        $addTestInstGroupSql = "INSERT INTO ".InstGroup::$dbTable." VALUES (1,'".Auth_Base::$TEST_INST_GROUPS[0]."',0),(2,'".Auth_Base::$TEST_INST_GROUPS[1]."',0),(3,'".Auth_Base::$TEST_INST_GROUPS[2]."',1),(4,'".Auth_Base::$TEST_INST_GROUPS[3]."',0)";
        // normal, normal, deleted, normal
        $addTestInstGroupStmt = $this->DB->prepare($addTestInstGroupSql);
        $addTestInstGroupStmt->execute();

        $linkUserToInstGroupSql = "INSERT INTO link_users_inst_groups  VALUES (1,1,0),(1,2,1),(1,3,0)"; // normal, link deleted, group deleted
        $linkUserToInstGroupStmt = $this->DB->prepare($linkUserToInstGroupSql);
        $linkUserToInstGroupStmt->execute();
	}
	
	function tearDown() {
        $rmTestUserSql = "DELETE FROM ".User::$dbTable;
        $rmTestUserStmt = $this->DB->prepare($rmTestUserSql);
        $rmTestUserStmt->execute();

        $rmTestInstGroupSql = "DELETE FROM ".InstGroup::$dbTable;
        $rmTestInstGroupStmt = $this->DB->prepare($rmTestInstGroupSql);
        $rmTestInstGroupStmt->execute();

        $rmLinkUserInstGroupSql = "DELETE FROM link_users_inst_groups";
        $rmLinkUserInstGroupStmt = $this->DB->prepare($rmLinkUserInstGroupSql);
        $rmLinkUserInstGroupStmt->execute();
	}

    /////////////////////////////////////////////////////
    /////////////////////////////////////////////////////

    // class structure tests

	function testInstGroupAtributesExist() {
		$this->assertEqual(count(InstGroup::$fields),3);
        $this->assertTrue(in_array('inst_group_id',InstGroup::$fields));
        $this->assertTrue(in_array('name',InstGroup::$fields));
        $this->assertTrue(in_array('flag_delete',InstGroup::$fields));      
	}

    // DB interaction tests

    function testGetUserInstGroups() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);

        $groups = InstGroup::getInstGroupsForUser($u);

        $this->assertTrue(is_array($groups));
        $this->assertEqual(count($groups),1);        
        $this->assertEqual(get_class($groups[0]),'InstGroup');
        $this->assertEqual($groups[0]->name,Auth_Base::$TEST_INST_GROUPS[0]);

    }   
}
?>