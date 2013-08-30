<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

Mock::generate('Auth_Base');

class TestOfUser extends WMSUnitTestCaseDB {
	
	public $auth;
	
	function setUp() {
        createAllTestData($this->DB);

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
        removeAllTestData($this->DB);
	}

	function testUserAtributesExist() {
		$this->assertEqual(count(User::$fields),11);
		$this->assertTrue(in_array('user_id',User::$fields));
		$this->assertTrue(in_array('username',User::$fields));
		$this->assertTrue(in_array('fname',User::$fields));
        $this->assertTrue(in_array('lname',User::$fields));
        $this->assertTrue(in_array('sortname',User::$fields));
		$this->assertTrue(in_array('email',User::$fields));
		$this->assertTrue(in_array('advisor',User::$fields));
		$this->assertTrue(in_array('notes',User::$fields));
		$this->assertTrue(in_array('flag_is_system_admin',User::$fields));
		$this->assertTrue(in_array('flag_is_banned',User::$fields));
		$this->assertTrue(in_array('flag_delete',User::$fields));
	}

    //// static methods

    function testCmp(){
        $u1 = new User(['user_id'=>50,'fname'=>'fred','lname'=>'jones', 'DB'=>$this->DB]);
        $u2 = new User(['user_id'=>51,'fname'=>'fred','lname'=>'albertson', 'DB'=>$this->DB]);
        $u3 = new User(['user_id'=>52,'fname'=>'al','lname'=>'ji', 'DB'=>$this->DB]);
        $u4 = new User(['user_id'=>53,'fname'=>'bab','lname'=>'ji', 'DB'=>$this->DB]);

        $this->assertEqual(User::cmp($u1,$u2),1);
        $this->assertEqual(User::cmp($u1,$u1),0);
        $this->assertEqual(User::cmp($u2,$u1),-1);

        $this->assertEqual(User::cmp($u3,$u4),-1);
    }


    //// DB interaction tests

	function testUserDBInsert(){
		$u = new User(['user_id'=>50,'fname'=>'fred','DB'=>$this->DB]);


		$u->updateDb();


		$u2 = User::getOneFromDb(['user_id'=>50], $this->DB);

		$this->assertTrue($u2->matchesDb);
		$this->assertEqual($u2->fname, 'fred');
	}

	function testUserRetrievedFromDb() {
		$u = new User(['user_id'=>1101,'DB'=>$this->DB]);
		$this->assertNull($u->username);
		
		$u->refreshFromDb();
        $this->assertEqual($u->username,Auth_Base::$TEST_USERNAME);
	}	

    //// instance methods

    function testUserInstGroupsLoaded() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);

        $u->loadInstGroups();

        $this->assertTrue(is_array($u->inst_groups));
        $this->assertEqual(count($u->inst_groups),1);        
        $this->assertEqual(get_class($u->inst_groups[0]),'InstGroup');
        $this->assertEqual($u->inst_groups[0]->name,'testInstGroup1');
    }   

    function testUserEqGroupsLoaded() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();        


        // testing this
        $u->loadEqGroups();


        usort($u->eq_groups,'EqGroup::cmpAlphabetical');

        $this->assertTrue(is_array($u->eq_groups));
        $this->assertEqual(count($u->eq_groups),5);
        $this->assertEqual($u->eq_groups[0]->name,'testEqGroup1');
        $this->assertEqual($u->eq_groups[1]->name,'testEqGroup2');
        $this->assertEqual($u->eq_groups[2]->name,'testEqGroup3');
        $this->assertEqual($u->eq_groups[3]->name,'testEqGroup6');
        $this->assertEqual($u->eq_groups[4]->name,'testEqGroup7');

        $this->assertEqual($u->eq_groups[0]->permission->entity_type,'inst_group');
        # $this->assertEqual($u->eq_groups[1]->permission->entity_type,'user'); // since this is dual, don't care where it came from
        $this->assertEqual($u->eq_groups[2]->permission->entity_type,'user');
        $this->assertEqual($u->eq_groups[3]->permission->entity_type,'inst_group');
        $this->assertEqual($u->eq_groups[4]->permission->entity_type,'user');
        
        $this->assertEqual($u->eq_groups[0]->permission->role_id, 1);
        $this->assertEqual($u->eq_groups[1]->permission->role_id, 2);
        $this->assertEqual($u->eq_groups[2]->permission->role_id, 1);
        $this->assertEqual($u->eq_groups[3]->permission->role_id, 2);
        $this->assertEqual($u->eq_groups[4]->permission->role_id, 2);
    }

    function testUserReservationsLoaded() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);

        // testing this
        $u->loadReservations();

        $this->assertTrue(is_array($u->reservations));
        $this->assertEqual(count($u->reservations),6);
    }

    public function testUserLoadCommPrefs(){
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $this->assertNull($u->comm_prefs);

        // testing this
        $u->loadCommPrefs();

        $this->assertTrue(is_array($u->comm_prefs));
        $this->assertEqual(count($u->comm_prefs),5); // expects 4 that exist in dataForTesting, plus 1 more that loadCommPrefs() will discover needs to be written

        $this->assertEqual($u->comm_prefs['201']->flag_alert_on_upcoming_reservation,0);
        $this->assertEqual($u->comm_prefs['201']->flag_contact_on_reserve_create,0);
        $this->assertEqual($u->comm_prefs['201']->flag_contact_on_reserve_cancel,0);

        $this->assertEqual($u->comm_prefs['202']->flag_alert_on_upcoming_reservation,1);
        $this->assertEqual($u->comm_prefs['202']->flag_contact_on_reserve_create,0);
        $this->assertEqual($u->comm_prefs['202']->flag_contact_on_reserve_cancel,0);

        $this->assertEqual($u->comm_prefs['203']->flag_alert_on_upcoming_reservation,0);
        $this->assertEqual($u->comm_prefs['203']->flag_contact_on_reserve_create,1);
        $this->assertEqual($u->comm_prefs['203']->flag_contact_on_reserve_cancel,0);

        $this->assertEqual($u->comm_prefs['207']->flag_alert_on_upcoming_reservation,0);
        $this->assertEqual($u->comm_prefs['207']->flag_contact_on_reserve_create,0);
        $this->assertEqual($u->comm_prefs['207']->flag_contact_on_reserve_cancel,1);

		# the function loadCommPrefs() will discover that this comm_pref is missing, and will create it
		$this->assertEqual($u->comm_prefs['206']->flag_alert_on_upcoming_reservation,0);
		$this->assertEqual($u->comm_prefs['206']->flag_contact_on_reserve_create,0);
		$this->assertEqual($u->comm_prefs['206']->flag_contact_on_reserve_cancel,0);
    }

    function testUserSchedulesLoaded() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);

        $u->loadSchedules();

        $this->assertTrue(is_array($u->schedules));
        $this->assertEqual(count($u->schedules),5);
    }

    function testUserCanManageEqGroup() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);

        $managed_indirect = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $managed_direct = EqGroup::getOneFromDb(['eq_group_id'=>203],$this->DB);
        $not_managed = EqGroup::getOneFromDb(['eq_group_id'=>202],$this->DB);


        $this->assertTrue($u->canManageEqGroup($managed_indirect));
        $this->assertTrue($u->canManageEqGroup($managed_direct));
        $this->assertFalse($u->canManageEqGroup($not_managed));

        $this->assertTrue($u->canManageEqGroup($managed_indirect->eq_group_id));
        $this->assertTrue($u->canManageEqGroup($managed_direct->eq_group_id));
        $this->assertFalse($u->canManageEqGroup($not_managed->eq_group_id));
    }

	function testUserCanUseEqGroup(){
		$u = User::getOneFromDb(['user_id'=>1101],$this->DB);

		$managed_indirect = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
		$managed_direct = EqGroup::getOneFromDb(['eq_group_id'=>203],$this->DB);
		$user_indirect = EqGroup::getOneFromDb(['eq_group_id'=>206],$this->DB);
		$user_direct = EqGroup::getOneFromDb(['eq_group_id'=>207],$this->DB);
		$not_user = EqGroup::getOneFromDb(['eq_group_id'=>208],$this->DB);


		$this->assertTrue($u->canUseEqGroup($managed_indirect));
		$this->assertTrue($u->canUseEqGroup($managed_direct));
		$this->assertTrue($u->canUseEqGroup($user_indirect));
		$this->assertTrue($u->canUseEqGroup($user_direct));
		$this->assertFalse($u->canUseEqGroup($not_user));

		$this->assertTrue($u->canUseEqGroup($managed_indirect->eq_group_id));
		$this->assertTrue($u->canUseEqGroup($managed_direct->eq_group_id));
		$this->assertTrue($u->canUseEqGroup($user_indirect->eq_group_id));
		$this->assertTrue($u->canUseEqGroup($user_direct->eq_group_id));
		$this->assertFalse($u->canUseEqGroup($not_user->eq_group_id));
	}

    //// auth-related tests

	function testUserUpdatesBaseDbWhenValidAuthDataIsDifferent() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
        $this->assertEqual($u->username,Auth_Base::$TEST_USERNAME);
		$this->assertTrue($u->matchesDb);

        $this->auth->lname = 'Newlastname';
        $this->auth->inst_groups = array_map(function($e){return $e->name;},$u->inst_groups);
		$this->assertNotEqual($u->lname,$this->auth->lname);
		

		$u->updateDbFromAuth($this->auth);


		$this->assertEqual($u->lname,$this->auth->lname);
		$this->assertTrue($u->matchesDb);
		
        $u2 = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $this->assertEqual($u2->username,Auth_Base::$TEST_USERNAME);
		$this->assertEqual($u2->lname,$this->auth->lname);
	}	

    function ASIDEtestUserInstGroupsAddedDbWhenValidAuthDataIsDifferent() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
        $this->auth->inst_groups = [Auth_Base::$TEST_INST_GROUPS[0],Auth_Base::$TEST_INST_GROUPS[1]];

        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),2);
        $ag0 = InstGroup::getOneFromDb(['name'=>$this->auth->inst_groups[0]],$this->DB);
        $ag1 = InstGroup::getOneFromDb(['name'=>$this->auth->inst_groups[1]],$this->DB);
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
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
    
        # these names come from dataForTesting.php
        $normalGroup = 'testInstGroup1';
        $initiallyDeletedGroup = 'testInstGroup4';

        $this->auth->inst_groups = [$normalGroup,$initiallyDeletedGroup];

        $deletedGroup = InstGroup::getOneFromDb(['name'=>$this->auth->inst_groups[1],'flag_delete'=>true],$this->DB);

        $this->assertTrue($deletedGroup->matchesDb);
        $this->assertTrue($deletedGroup->flag_delete);
        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,$this->auth->inst_groups[0]);


        $u->updateDbFromAuth($this->auth);


        $this->assertEqual(count($u->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,$normalGroup);
        $this->assertTrue($u->inst_groups[0]->matchesDb);
        $this->assertEqual($u->inst_groups[1]->name,$initiallyDeletedGroup);
        $this->assertTrue($u->inst_groups[1]->matchesDb);
        $this->assertFalse($u->inst_groups[1]->flag_delete);
    }

    function testUserInstGroupsRemovedDbWhenValidAuthDataIsDifferent() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();
        $this->auth->inst_groups = [];

        $this->assertEqual(count($u->inst_groups),1);


        $u->updateDbFromAuth($this->auth);


        $this->assertEqual(count($u->inst_groups),0);

    }   

    function testUserDeletedMembershipReactivatedOnAuth() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $u->loadInstGroups();

        # these names come from dataForTesting.php
        $normalGroup = 'testInstGroup1';
        $initiallyDeletedMembershipGroup = 'testInstGroup5';

        $this->auth->inst_groups = [$normalGroup,$initiallyDeletedMembershipGroup];

        $this->assertEqual(count($u->inst_groups),1);
        $this->assertEqual(count($this->auth->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,$this->auth->inst_groups[0]);


        $u->updateDbFromAuth($this->auth);


        $this->assertEqual(count($u->inst_groups),2);
        $this->assertEqual($u->inst_groups[0]->name,$normalGroup);
        $this->assertTrue($u->inst_groups[0]->matchesDb);
        $this->assertEqual($u->inst_groups[1]->name,$initiallyDeletedMembershipGroup);
        $this->assertTrue($u->inst_groups[1]->matchesDb);
        $this->assertFalse($u->inst_groups[1]->flag_delete);
    }   
    

	function testUserUpdatesBaseDbWhenAuthDataIsInvalid() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
		$this->auth->fname = '';		

		$status = $u->updateDbFromAuth($this->auth);

		// should let caller/program know there's a problem
		$this->assertFalse($status);
	}	

    function testNewUserBaseRecordCreatedWhenAuthDataIsForNewUser() {
        $u = User::getOneFromDb(['user_id'=>1101],$this->DB);
        $this->auth->fname = '';        

        $status = $u->updateDbFromAuth($this->auth);

        // should let caller/program know there's a problem
        $this->assertFalse($status);
    }

}
?>