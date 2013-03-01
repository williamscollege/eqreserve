<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/user.class.php';

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

        $linkUserToInstGroupSql = "INSERT INTO link_users_inst_groups  VALUES (1,1,0),(1,2,1),(1,3,0)"; // normal, link deleted, group deleted
        $linkUserToInstGroupStmt = $this->DB->prepare($linkUserToInstGroupSql);
        $linkUserToInstGroupStmt->execute();

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

        $rmLinkUserInstGroupSql = "DELETE FROM link_users_inst_groups";
        $rmLinkUserInstGroupStmt = $this->DB->prepare($rmLinkUserInstGroupSql);
        $rmLinkUserInstGroupStmt->execute();
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

    /// auth-related tests

	function testUserUpdatesBaseDbWhenValidAuthDataIsDifferent() {
		$u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $this->assertEqual($u->username,Auth_Base::$TEST_USERNAME);
		$this->assertTrue($u->matchesDb);

        $this->auth->lname = 'Newlastname';
		$this->assertNotEqual($u->lname,$this->auth->lname);
		
		$u->updateDbFromAuth($this->auth);

		$this->assertEqual($u->lname,$this->auth->lname);
		$this->assertTrue($u->matchesDb);
		
		$u2 = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $this->assertEqual($u2->username,Auth_Base::$TEST_USERNAME);
		$this->assertEqual($u2->lname,$this->auth->lname);
	}	

    function ASIDE_testUserInstGroupsAddedDbWhenValidAuthDataIsDifferent() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),count(Auth_Base::$TEST_INST_GROUPS));
        
        $u->updateDbFromAuth($this->auth);

        $this->assertEqual(count($u->inst_groups),count(Auth_Base::$TEST_INST_GROUPS));

        for ($i=0;$i<count(Auth_Base::$TEST_INST_GROUPS);$i++) {
            $this->assertTrue($u->inst_groups[$i]->matchesDb);
            $this->assertFalse($u->inst_groups[$i]->flag_delete);
            $this->assertEqual($u->inst_groups[$i]->name,Auth_Base::$TEST_INST_GROUPS[$i]);
        }
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