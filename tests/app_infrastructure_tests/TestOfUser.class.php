<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/user.class.php';
require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

Mock::generate('Auth_Base');

class TestOfUser extends UnitTestCaseDB {
	
	public $auth;
	
	function setUp() {
        $addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES (1,'".Auth_Base::$TEST_USERNAME."','".Auth_Base::$TEST_FNAME."','".Auth_Base::$TEST_LNAME."','".Auth_Base::$TEST_SORTNAME."','".Auth_Base::$TEST_EMAIL."','David Keiser-Clark','some important notes',0,0)";
		$addTestUserStmt = $this->DB->prepare($addTestUserSql);
		$addTestUserStmt->execute();

        $addTestInstGroupSql = "INSERT INTO ".InstGroup::$dbTable." VALUES (1,'".Auth_Base::$TEST_INST_GROUPS[0]."',0),(2,'".Auth_Base::$TEST_INST_GROUPS[1]."',0),(3,'".Auth_Base::$TEST_INST_GROUPS[2]."',1),(4,'".Auth_Base::$TEST_INST_GROUPS[3]."',0)";
        // normal, normal, deleted, normal
        $addTestInstGroupStmt = $this->DB->prepare($addTestInstGroupSql);
        $addTestInstGroupStmt->execute();

        $linkUserToInstGroupSql = "INSERT INTO ".InstMembership::$dbTable."  VALUES (1,1,1,0),(2,1,2,1),(3,1,3,0)"; // normal, link deleted, group deleted
        $linkUserToInstGroupStmt = $this->DB->prepare($linkUserToInstGroupSql);
        $linkUserToInstGroupStmt->execute();

        # EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
        $addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES    (1,'Nanomajigs','The investigation of really small stuff','0,15,30,45',15,60,15,0),
                                                                                (2,'3D Printers','3dp descr','0,30',30,300,30,0),
                                                                                (3,'Spectrometers','spectrothingies','0,15,30,45',15,60,15,0)
                                                                            ";
        $addTestEqGroupsStmt = $this->DB->prepare($addTestEqGroupsSql);
        $addTestEqGroupsStmt->execute();

        // TODO: set up and check for indirect access via inst_group membership and permissions where entity_type == 'inst_group'
        # Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
        $addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES   
                                                                                    (1,1,'user',       3,1,0),
                                                                                    (2,1,'inst_group', 1,2,0),
                                                                                    (3,1,'inst_group', 2,3,0),
                                                                                    (4,1,'user',       3,3,0)
                                                                                    ";
        $addTestPermissionStmt = $this->DB->prepare($addTestPermissionSql);
        $addTestPermissionStmt->execute();

		$this->auth = new MockAuth_Base();
        $this->auth->username       = Auth_Base::$TEST_USERNAME;
        $this->auth->email          = Auth_Base::$TEST_EMAIL;
        $this->auth->fname          = Auth_Base::$TEST_FNAME;
        $this->auth->lname          = Auth_Base::$TEST_LNAME;
        $this->auth->sortname       = Auth_Base::$TEST_SORTNAME;
        $this->auth->inst_groups    = array_slice(Auth_Base::$TEST_INST_GROUPS,0);
        $this->auth->msg            = '';
        $this->auth->debug          = '';
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

        $rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
        $rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
        $rmTestEqGroupsStmt->execute();

        $rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
        $rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
        $rmTestPermissionStmt->execute();
	}

	function testUserAtributesExist() {
		$this->assertEqual(count(User::$fields),10);
		$this->assertTrue(in_array('user_id',User::$fields));
		$this->assertTrue(in_array('username',User::$fields));
		$this->assertTrue(in_array('fname',User::$fields));
        $this->assertTrue(in_array('lname',User::$fields));
        $this->assertTrue(in_array('sortname',User::$fields));
		$this->assertTrue(in_array('email',User::$fields));
		$this->assertTrue(in_array('advisor',User::$fields));
		$this->assertTrue(in_array('notes',User::$fields));
		$this->assertTrue(in_array('flag_is_banned',User::$fields));
		$this->assertTrue(in_array('flag_delete',User::$fields));		
	}

    // DB interaction tests

	function testUserRetrievedFromDb() {
		$u = new User(['user_id'=>1,'DB'=>$this->DB]);
		$this->assertNull($u->username);
		
		$u->refreshFromDb();
        $this->assertEqual($u->username,Auth_Base::$TEST_USERNAME);
	}	

    function testUserInstGroupsLoaded() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);

        $u->loadInstGroups();

        $this->assertTrue(is_array($u->inst_groups));
        $this->assertEqual(count($u->inst_groups),1);        
        $this->assertEqual(get_class($u->inst_groups[0]),'InstGroup');
        $this->assertEqual($u->inst_groups[0]->name,Auth_Base::$TEST_INST_GROUPS[0]);
    }   

    function testUserEqGroupsLoaded() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();        


        // testing this
        $u->loadEqGroups();


        usort($u->eq_groups,'EqGroup::cmpAlphabetical');

        $this->assertTrue(is_array($u->eq_groups));
        $this->assertEqual(count($u->eq_groups),3);
        $this->assertEqual($u->eq_groups[0]->name,'3D Printers');
        $this->assertEqual($u->eq_groups[1]->name,'Nanomajigs');
        $this->assertEqual($u->eq_groups[2]->name,'Spectrometers');

        $this->assertEqual($u->eq_groups[0]->permission->entity_type,'inst_group');
        $this->assertEqual($u->eq_groups[1]->permission->entity_type,'user');
        
    }   

    /// auth-related tests

	function testUserUpdatesBaseDbWhenValidAuthDataIsDifferent() {
		$u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual($u->username,Auth_Base::$TEST_USERNAME);
		$this->assertTrue($u->matchesDb);

        $this->auth->lname = 'Newlastname';
        $this->auth->inst_groups = array_map(function($e){return $e->name;},$u->inst_groups);
		$this->assertNotEqual($u->lname,$this->auth->lname);
		

		$u->updateDbFromAuth($this->auth);


		$this->assertEqual($u->lname,$this->auth->lname);
		$this->assertTrue($u->matchesDb);
		
		$u2 = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $this->assertEqual($u2->username,Auth_Base::$TEST_USERNAME);
		$this->assertEqual($u2->lname,$this->auth->lname);
	}	

    function ASIDEtestUserInstGroupsAddedDbWhenValidAuthDataIsDifferent() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->auth->inst_groups = [Auth_Base::$TEST_INST_GROUPS[0],Auth_Base::$TEST_INST_GROUPS[1]];

        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),2);
        $ag0 = InstGroup::loadOneFromDb(['name'=>$this->auth->inst_groups[0]],$this->DB);
        $ag1 = InstGroup::loadOneFromDb(['name'=>$this->auth->inst_groups[1]],$this->DB);
        $this->assertTrue($ag0->matchesDb);
        $this->assertFalse($ag0->flag_delete);
        $this->assertTrue($ag1->matchesDb);
        $this->assertFalse($ag1->flag_delete);

        $this->assertEqual($u->inst_groups[0]->name,$this->auth->inst_groups[0]);


        $u->updateDbFromAuth($this->auth);

        $this->assertEqual(count($u->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,Auth_Base::$TEST_INST_GROUPS[0]);
        $this->assertTrue($u->inst_groups[0]->matchesDb);
        $this->assertEqual($u->inst_groups[1]->name,Auth_Base::$TEST_INST_GROUPS[1]);
        $this->assertTrue($u->inst_groups[1]->matchesDb);
    }   

    function testUserInstGroupsUndeletedDbWhenValidAuthDataIsDifferent() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();

        $this->auth->inst_groups = [Auth_Base::$TEST_INST_GROUPS[0],Auth_Base::$TEST_INST_GROUPS[2]];

        $deletedGroup = InstGroup::loadOneFromDb(['name'=>$this->auth->inst_groups[1]],$this->DB);

        $this->assertTrue($deletedGroup->matchesDb);
        $this->assertTrue($deletedGroup->flag_delete);
        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,$this->auth->inst_groups[0]);


        $u->updateDbFromAuth($this->auth);

        $this->assertEqual(count($u->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,Auth_Base::$TEST_INST_GROUPS[0]);
        $this->assertTrue($u->inst_groups[0]->matchesDb);
        $this->assertEqual($u->inst_groups[1]->name,Auth_Base::$TEST_INST_GROUPS[2]);
        $this->assertTrue($u->inst_groups[1]->matchesDb);
        $this->assertFalse($u->inst_groups[1]->flag_delete);
    }

    function testUserInstGroupsRemovedDbWhenValidAuthDataIsDifferent() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->auth->inst_groups = [];

        $this->assertEqual(count($u->inst_groups),1);


        $u->updateDbFromAuth($this->auth);


        $this->assertEqual(count($u->inst_groups),0);

    }   

    function testUserDeletedMembershipReactivatedOnAuth() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();

        $this->auth->inst_groups = [Auth_Base::$TEST_INST_GROUPS[0],Auth_Base::$TEST_INST_GROUPS[1]];

        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,$this->auth->inst_groups[0]);


        $u->updateDbFromAuth($this->auth);


        $this->assertEqual(count($u->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,Auth_Base::$TEST_INST_GROUPS[0]);
        $this->assertTrue($u->inst_groups[0]->matchesDb);
        $this->assertEqual($u->inst_groups[1]->name,Auth_Base::$TEST_INST_GROUPS[1]);
        $this->assertTrue($u->inst_groups[1]->matchesDb);
        $this->assertFalse($u->inst_groups[1]->flag_delete);
    }   
    

	function testUserUpdatesBaseDbWhenAuthDataIsInvalid() {
		$u = User::loadOneFromDb(['user_id'=>1],$this->DB);
		$this->auth->fname = '';		

		$status = $u->updateDbFromAuth($this->auth);

		// should let caller/program know there's a problem
		$this->assertFalse($status);
	}	

    function testNewUserBaseRecordCreatedWhenAuthDataIsForNewUser() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $this->auth->fname = '';        

        $status = $u->updateDbFromAuth($this->auth);

        // should let caller/program know there's a problem
        $this->assertFalse($status);
    }
   

}
?>