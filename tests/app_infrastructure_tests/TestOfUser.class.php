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
		$rmTestUserSql = "DELETE FROM ".User::$dbTable." WHERE user_id = 1";
		$rmTestUserStmt = $this->DB->prepare($rmTestUserSql);
		$rmTestUserStmt->execute();
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

/*
	function testUser() {
	}	
*/
	function testUserRetrievedFromDb() {
		$u = new User(['user_id'=>1,'DB'=>$this->DB]);
		$this->assertNull($u->username);
		
		$u->refreshFromDb();
        $this->assertEqual($u->username,Auth_Base::$TEST_USERNAME);
	}	

	function testUserUpdatesDbWhenValidAuthDataIsDifferent() {
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


	function testUserUpdatesDbWhenAuthDataIsInvalid() {
		$u = User::loadOneFromDb(['user_id'=>1],$this->DB);
		$this->auth->fname = '';		

		$status = $u->updateDbFromAuth($this->auth);

		// should let caller/program know there's a problem
		$this->assertFalse($status);
	}	

    function testNewUserRecordCreatedWhenAuthDataIsForNewUser() {
        $u = User::loadOneFromDb(['user_id'=>1],$this->DB);
        $this->auth->fname = '';        

        $status = $u->updateDbFromAuth($this->auth);

        // should let caller/program know there's a problem
        $this->assertFalse($status);
    }   
/*
store and output on user account page:
	comm_prefs
	eq_groups and permissions
	inst_groups: add permissions to SESSION variable
*/

}
?>