<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/user.class.php';

Mock::generate('Auth_Base');

class TestOfUser extends UnitTestCaseDB {
	
	public $auth;
	
	function setUp() {
		$addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES (1,'vbovine','Violet','Bovine','cwarren@williams.edu','David Keiser-Clark','some important notes',0,0)";
		$addTestUserStmt = $this->DB->prepare($addTestUserSql);
		$addTestUserStmt->execute();

		$this->auth = new MockAuth_Base();
		$this->auth->msg			= '';
		$this->auth->position		= 'STUDENT';
		$this->auth->mail			= 'cwarren@williams.edu';
		$this->auth->lname			= 'Cowsaurius';
		$this->auth->fname			= 'Villian';
		$this->auth->name			= 'Villian Cowsaurius';
		$this->auth->sortname		= 'Cowsaurius Villian';
		$this->auth->debug			= '';
		$this->auth->inst_groups	= [44000000, 'Jesup-STC', 'STUDENT'];
	}
	
	function tearDown() {
		$rmTestUserSql = "DELETE FROM ".User::$dbTable." WHERE user_id = 1";
		$rmTestUserStmt = $this->DB->prepare($rmTestUserSql);
		$rmTestUserStmt->execute();
	}

	function testUserAtributesExist() {
		$this->assertEqual(count(User::$fields),9);
		$this->assertTrue(in_array('user_id',User::$fields));
		$this->assertTrue(in_array('username',User::$fields));
		$this->assertTrue(in_array('fname',User::$fields));
		$this->assertTrue(in_array('lname',User::$fields));
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
		$this->assertEqual($u->username,'vbovine');
	}	

	function testUserUpdatesDbWhenValidAuthDataIsDifferent() {
		$u = User::loadOneFromDb(['user_id'=>1],$this->DB);
		$this->assertEqual($u->username,'vbovine');
		$this->assertTrue($u->matchesDb);
		$this->assertNotEqual($u->lname,$this->auth->lname);
		
		$u->updateDbFromAuth($this->auth);

		$this->assertEqual($u->lname,$this->auth->lname);
		$this->assertTrue($u->matchesDb);
		
		$u2 = User::loadOneFromDb(['user_id'=>1],$this->DB);
		$this->assertEqual($u2->username,'vbovine');
		$this->assertEqual($u2->lname,$this->auth->lname);
	}	


	function testUserUpdatesDbWhenAuthDataIsInvalid() {
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