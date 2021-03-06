<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';


class TestOfInstGroup extends WMSUnitTestCaseDB {
	
	
	function setUp() {
        createAllTestData($this->DB);
	}
	
	function tearDown() {
        removeAllTestData($this->DB);
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
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);

        $groups = InstGroup::getInstGroupsForUser($u);

        $this->assertTrue(is_array($groups));
        $this->assertEqual(count($groups),1);        
        $this->assertEqual(get_class($groups[0]),'InstGroup');
        $this->assertEqual($groups[0]->name,'testInstGroup1');
    }

    // DB interaction tests - object instance tests

    function testLinkUserNew() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);

        $g = InstGroup::getOneFromDb(['inst_group_id'=>503],$this->DB);
        $this->assertNotEqual($u->inst_groups[0]->inst_group_id,$g->inst_group_id);


        $g->linkUser($u);


        $this->assertEqual(count($u->inst_groups),2);

        $new_g = $u->inst_groups[0];
        if ($new_g->inst_group_id != 4) {
            $new_g = $u->inst_groups[1];
        }
        $this->assertEqual($new_g->inst_group_id,503);
    } 

    function testLinkUserReactivate() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);

        $g = InstGroup::getOneFromDb(['inst_group_id'=>505],$this->DB);
        $this->assertNotEqual($u->inst_groups[0]->inst_group_id,$g->inst_gorup_id);

        $m = InstMembership::getOneFromDb(['inst_membership_id'=>603,'flag_delete'=>true],$this->DB);
        $this->assertTrue($m->flag_delete);
        $this->assertEqual($m->user_id,$u->user_id);
        $this->assertEqual($m->inst_group_id,$g->inst_group_id);


        $g->linkUser($u);


        $this->assertEqual(count($u->inst_groups),2);

        $new_g = $u->inst_groups[0];
        if ($new_g->inst_group_id != 2) {
            $new_g = $u->inst_groups[1];
        }
        $this->assertEqual($new_g->inst_group_id,505);

        $m = InstMembership::getOneFromDb(['inst_membership_id'=>603],$this->DB);
        $this->assertFalse($m->flag_delete);
        $this->assertEqual($m->user_id,$u->user_id);
        $this->assertEqual($m->inst_group_id,$g->inst_group_id);
    } 

    function testUnlinkUser() {
        // remove the connection between this group and a given user
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);

        $u->inst_groups[0]->unlinkUser($u);

        $this->assertEqual(count($u->inst_groups),0);
    } 

    function testGetUsers() {
        // get a list of all the user objects associated with this group

        // a user in a group and a deleted user   
        $g = InstGroup::getOneFromDb(['inst_group_id'=>501],$this->DB);

        $users = $g->getAllUsers();

        $this->assertEqual(count($users),1);
        $this->assertEqual($users[0]->user_id,1101);

        ///////

        // a group with no users
        $g = InstGroup::getOneFromDb(['inst_group_id'=>503],$this->DB);

        $users = $g->getAllUsers();

        $this->assertEqual(count($users),0);

        ///////

        // a group with a deleted membership
        $g = InstGroup::getOneFromDb(['inst_group_id'=>505],$this->DB);

        $users = $g->getAllUsers();

        $this->assertEqual(count($users),0);

    }

    function testInstGroupEqGroupsLoaded() {
        $g = InstGroup::getOneFromDb(['inst_group_id'=>501],$this->DB);


        // testing this
        $g->loadEqGroups();


        usort($g->eq_groups,'EqGroup::cmpAlphabetical');

        $this->assertTrue(is_array($g->eq_groups));
        $this->assertEqual(count($g->eq_groups),4);
        $this->assertEqual($g->eq_groups[0]->name,'testEqGroup1');
        $this->assertEqual($g->eq_groups[1]->name,'testEqGroup2');
        $this->assertEqual($g->eq_groups[2]->name,'testEqGroup3');
        $this->assertEqual($g->eq_groups[3]->name,'testEqGroup6');
    }

    function testToListItemLinked() {
        $g = InstGroup::getOneFromDb(['inst_group_id'=>501],$this->DB);

        $this->assertEqual($g->toListItemLinked(),
                           '<li><a href="inst_group.php?inst_group=501" title="testInstGroup1">testInstGroup1</a></li>');
    }

}
?>