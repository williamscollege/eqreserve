<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/inst_group.class.php';


class TestOfInstGroup extends UnitTestCaseDB {
	
	
	function setUp() {
        $addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES (1,'".Auth_Base::$TEST_USERNAME."','".Auth_Base::$TEST_FNAME."','".Auth_Base::$TEST_LNAME."','".Auth_Base::$TEST_SORTNAME."','".Auth_Base::$TEST_EMAIL."','David Keiser-Clark','some important notes',0,0,0)";
		$addTestUserStmt = $this->DB->prepare($addTestUserSql);
		$addTestUserStmt->execute();

        $addTestInstGroupSql = "INSERT INTO ".InstGroup::$dbTable." VALUES (1,'".Auth_Base::$TEST_INST_GROUPS[0]."',0),(2,'".Auth_Base::$TEST_INST_GROUPS[1]."',0),(3,'".Auth_Base::$TEST_INST_GROUPS[2]."',1),(4,'".Auth_Base::$TEST_INST_GROUPS[3]."',0)";
        // normal, normal, deleted, normal
        $addTestInstGroupStmt = $this->DB->prepare($addTestInstGroupSql);
        $addTestInstGroupStmt->execute();

        $linkUserToInstGroupSql = "INSERT INTO ".InstMembership::$dbTable."  VALUES (1,1,1,0),(2,1,2,1),(3,1,3,0)"; // normal, link deleted, group deleted
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

        $rmLinkUserInstGroupSql = "DELETE FROM ".InstMembership::$dbTable;
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

	function testInstGroupDBInsert(){
		$test = new InstGroup(['inst_group_id'=>50,'DB'=>$this->DB]);


		$test->updateDb();


		$test2 = InstGroup::getOneFromDb(['inst_group_id'=>50], $this->DB);

		$this->assertTrue($test2->matchesDb);
	}

    // DB interaction tests - static method tests

    function testGetUserInstGroups() {
        $u = User::getOneFromDb(['user_id'=>1],$this->DB);

        $groups = InstGroup::getInstGroupsForUser($u);

        $this->assertTrue(is_array($groups));
        $this->assertEqual(count($groups),1);        
        $this->assertEqual(get_class($groups[0]),'InstGroup');
        $this->assertEqual($groups[0]->name,Auth_Base::$TEST_INST_GROUPS[0]);
    }

    // DB interaction tests - object instance tests

    function testLinkUserNew() {
        $u = User::getOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);

        $g = InstGroup::getOneFromDb(['inst_group_id'=>4],$this->DB);
        $this->assertNotEqual($u->inst_groups[0]->inst_group_id,$g->inst_gorup_id);

        $g->linkUser($u);

        $this->assertEqual(count($u->inst_groups),2);

        $new_g = $u->inst_groups[0];
        if ($new_g->inst_group_id != 4) {
            $new_g = $u->inst_groups[1];
        }
        $this->assertEqual($new_g->inst_group_id,4);
    } 

    function testLinkUserReactivate() {
        $u = User::getOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);

        $g = InstGroup::getOneFromDb(['inst_group_id'=>2],$this->DB);
        $this->assertNotEqual($u->inst_groups[0]->inst_group_id,$g->inst_gorup_id);

        $m = InstMembership::getOneFromDb(['inst_membership_id'=>2],$this->DB);
        $this->assertTrue($m->flag_delete);
        $this->assertEqual($m->user_id,$u->user_id);
        $this->assertEqual($m->inst_group_id,$g->inst_group_id);

        $g->linkUser($u);

        $this->assertEqual(count($u->inst_groups),2);

        $new_g = $u->inst_groups[0];
        if ($new_g->inst_group_id != 2) {
            $new_g = $u->inst_groups[1];
        }
        $this->assertEqual($new_g->inst_group_id,2);

        $m = InstMembership::getOneFromDb(['inst_membership_id'=>2],$this->DB);
        $this->assertFalse($m->flag_delete);
        $this->assertEqual($m->user_id,$u->user_id);
        $this->assertEqual($m->inst_group_id,$g->inst_group_id);
    } 

    function testUnlinkUser() {
        // remove the connection between this group and a given user
        $u = User::getOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);

        $u->inst_groups[0]->unlinkUser($u);

        $this->assertEqual(count($u->inst_groups),0);
    } 

    function testGetUsers() {
        // get a list of all the user objects associated with this group
        $g = InstGroup::getOneFromDb(['inst_group_id'=>1],$this->DB);

        $users = $g->getAllUsers();

        $this->assertEqual(count($users),1);
        $this->assertEqual($users[0]->user_id,1);

        ///////

        $g = InstGroup::getOneFromDb(['inst_group_id'=>2],$this->DB);

        $users = $g->getAllUsers();

        $this->assertEqual(count($users),0);

        ///////

        $g = InstGroup::getOneFromDb(['inst_group_id'=>4],$this->DB);

        $users = $g->getAllUsers();

        $this->assertEqual(count($users),0);
    } 

}
?>