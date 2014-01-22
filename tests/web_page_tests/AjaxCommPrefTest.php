<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

	class AjaxCommPrefTest extends WMSWebTestCase {

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		//############################################################

		function signIn() {
			$this->get('http://localhost/eqreserve/');
			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		function getToAcctMgtPage() {
			$this->get('http://localhost/eqreserve/');
			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
			$this->clickLink(TESTINGUSER);
		}

		//############################################################

		function testCommPrefAjaxUserCanNotAccessOthersCommPref() {
			$this->signIn();

			$this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=107&commPrefAction=setReminder&actionVal=false');

			$this->assertPattern('/"status":"failure"/');

			$s = CommPref::getOneFromDb(['comm_pref_id' => 107], $this->DB);
			$this->assertTrue($s->matchesDb);
			$this->assertTrue($s->flag_alert_on_upcoming_reservation);
		}

		function testCommPrefAjaxInvalidAction() {
			$initialCommPref = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertTrue($initialCommPref->matchesDb);
			$this->signIn();

			$this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=superpowers&actionVal=foo123bar');

			$this->assertPattern('/"status":"failure"/');
			$s = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertEqual($s->flag_alert_on_upcoming_reservation, $initialCommPref->flag_alert_on_upcoming_reservation);
			$this->assertEqual($s->flag_contact_on_reserve_create, $initialCommPref->flag_contact_on_reserve_create);
			$this->assertEqual($s->flag_contact_on_reserve_cancel, $initialCommPref->flag_contact_on_reserve_cancel);
		}

		function testCommPrefSetReminder() {
			$initialCommPref = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertTrue($initialCommPref->matchesDb);
			$this->signIn();

			$this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=setReminder&actionVal=1');

			$s = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertNotEqual($s->flag_alert_on_upcoming_reservation, $initialCommPref->flag_alert_on_upcoming_reservation);
			$this->assertEqual($s->flag_contact_on_reserve_create, $initialCommPref->flag_contact_on_reserve_create);
			$this->assertEqual($s->flag_contact_on_reserve_cancel, $initialCommPref->flag_contact_on_reserve_cancel);
		}

		function testCommPrefSetAlertCreate() {
			$initialCommPref = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertTrue($initialCommPref->matchesDb);
			$this->signIn();

			$this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=setAlertCreate&actionVal=1');

			$s = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertEqual($s->flag_alert_on_upcoming_reservation, $initialCommPref->flag_alert_on_upcoming_reservation);
			$this->assertNotEqual($s->flag_contact_on_reserve_create, $initialCommPref->flag_contact_on_reserve_create);
			$this->assertEqual($s->flag_contact_on_reserve_cancel, $initialCommPref->flag_contact_on_reserve_cancel);
		}

		function testCommPrefSetAlertCancel() {
			$initialCommPref = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertTrue($initialCommPref->matchesDb);
			$this->signIn();

			$this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=setAlertCancel&actionVal=1');

			$s = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);
			$this->assertEqual($s->flag_alert_on_upcoming_reservation, $initialCommPref->flag_alert_on_upcoming_reservation);
			$this->assertEqual($s->flag_contact_on_reserve_create, $initialCommPref->flag_contact_on_reserve_create);
			$this->assertNotEqual($s->flag_contact_on_reserve_cancel, $initialCommPref->flag_contact_on_reserve_cancel);
		}

		function testCommPrefAjaxAdminCanAccessOthersCommPref() {
			$u                       = User::getOneFromDb(['username' => TESTINGUSER], $this->DB);
			$u->flag_is_system_admin = TRUE;
			$u->updateDb();
			$this->assertTrue($u->matchesDb);
			$initialCommPref = CommPref::getOneFromDb(['comm_pref_id' => 107], $this->DB);
			$this->assertTrue($initialCommPref->flag_alert_on_upcoming_reservation);

			$this->getToAcctMgtPage();


			$this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=107&commPrefAction=setReminder&actionVal=false');


			$this->assertPattern('/"status":"success"/');
			$s = CommPref::getOneFromDb(['comm_pref_id' => 107], $this->DB);
			$this->assertTrue($s->matchesDb);
			$this->assertFalse($s->flag_alert_on_upcoming_reservation);
		}

		public function testAddVariousPermissionsAndCheckResultingCommPrefs() {
			// create test ig, eqg and user conditions
			$igX = new InstGroup(['inst_group_id' => 520, 'name' => 'testInstGroupX', 'flag_delete' => 0, 'DB' => $this->DB]);
			$igY = new InstGroup(['inst_group_id' => 521, 'name' => 'testInstGroupY', 'flag_delete' => 0, 'DB' => $this->DB]);
			$igZ = new InstGroup(['inst_group_id' => 522, 'name' => 'testInstGroupZ', 'flag_delete' => 0, 'DB' => $this->DB]);

			$eqgI = new EqGroup(['eq_group_id' => 220, 'name' => 'testEqGroupI', 'flag_delete' => 0, 'DB' => $this->DB]);
			$eqgJ = new EqGroup(['eq_group_id' => 221, 'name' => 'testEqGroupJ', 'flag_delete' => 0, 'DB' => $this->DB]);
			$eqgK = new EqGroup(['eq_group_id' => 222, 'name' => 'testEqGroupK', 'flag_delete' => 0, 'DB' => $this->DB]);

			$uA = User::getOneFromDb(['username' => Auth_Base::$TEST_USERNAME, 'flag_delete' => 0], $this->DB);

			$igX->updateDb();
			$igY->updateDb();
			$igZ->updateDb();
			$eqgI->updateDb();
			$eqgJ->updateDb();
			$eqgK->updateDb();

			// create test permissions
			$p1  = new Permission(['entity_id' => $igX->inst_group_id, 'entity_type' => 'inst_group', 'role_id' => 2, 'eq_group_id' => $eqgI->eq_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$p2  = new Permission(['entity_id' => $igY->inst_group_id, 'entity_type' => 'inst_group', 'role_id' => 2, 'eq_group_id' => $eqgK->eq_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$p3  = new Permission(['entity_id' => $igZ->inst_group_id, 'entity_type' => 'inst_group', 'role_id' => 2, 'eq_group_id' => $eqgJ->eq_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$p4  = new Permission(['entity_id' => $uA->user_id, 'entity_type' => 'user', 'role_id' => 2, 'eq_group_id' => $eqgJ->eq_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$im1 = new InstMembership(['user_id' => $uA->user_id, 'inst_group_id' => $igX->inst_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);

			$p1->updateDb();
			$p2->updateDb();
			$p3->updateDb();
			$p4->updateDb();
			$im1->updateDb();


			// Action Test #1
			// condition: create eq_group E
			$eqgE = new EqGroup(['eq_group_id' => 223, 'name' => 'testEqGroupE', 'flag_delete' => 0, 'DB' => $this->DB]);
			$eqgE->updateDb();
			// condition: inst_group X given access to eq_group E
			$p6 = new Permission(['entity_id' => $igX->inst_group_id, 'entity_type' => 'inst_group', 'role_id' => 2, 'eq_group_id' => $eqgE->eq_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$p6->updateDb();
			// test: user A has new comm_pref record for eq_group E
			$uA->updateCommPrefs();
			$cp1 = CommPref::getOneFromDb(['user_id' => $uA->user_id, 'eq_group_id' => $eqgE->eq_group_id], $this->DB);
			$this->assertEqual(count($cp1), 1);


			// Action Test #2
			// condition: user A joins inst_group Y
			$im2 = new InstMembership(['user_id' => $uA->user_id, 'inst_group_id' => $igY->inst_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$im2->updateDb();
			// test: user A has new comm_pref record for eq_group K
			$uA->updateCommPrefs();
			$cp2 = CommPref::getOneFromDb(['user_id' => $uA->user_id, 'eq_group_id' => $eqgK->eq_group_id], $this->DB);
			$this->assertEqual(count($cp2), 1);


			// Action Test #3
			// condition: preset user A's comm_pref for inst_group J to non-default
			$cp3                                     = CommPref::getOneFromDb(['user_id' => $uA->user_id, 'eq_group_id' => $eqgJ->eq_group_id], $this->DB);
			$cp3->flag_alert_on_upcoming_reservation = FALSE;
			$cp3->flag_contact_on_reserve_create     = FALSE;
			$cp3->flag_contact_on_reserve_cancel     = FALSE;
			$cp3->updateDb();
			// condition: user A joins inst_group Z
			$im3 = new InstMembership(['user_id' => $uA->user_id, 'inst_group_id' => $igZ->inst_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$im3->updateDb();
			// test: user A's existing comm_pref for inst_group J does NOT change
			$uA->updateCommPrefs();
			$cp4 = CommPref::getOneFromDb(['user_id' => $uA->user_id, 'eq_group_id' => $eqgJ->eq_group_id], $this->DB);
			$this->assertEqual($cp4->flag_alert_on_upcoming_reservation, FALSE);
			$this->assertEqual($cp4->flag_contact_on_reserve_create, FALSE);
			$this->assertEqual($cp4->flag_contact_on_reserve_cancel, FALSE);

			// Action Test #4
			// condition: create new testUserB
			$uB = new User(['username' => 'testUserB', 'email' => 'tuB@inst.edu', 'flag_delete' => 0, 'DB' => $this->DB]);
			$uB->updateDb();
			// condition: new user B member of inst_group X
			$im4 = new InstMembership(['user_id' => $uB->user_id, 'inst_group_id' => $igX->inst_group_id, 'flag_delete' => 0, 'DB' => $this->DB]);
			$im4->updateDb();
			// test: user B has comm_pref for eq_group I
			$uB->updateCommPrefs();
			$cp5 = CommPref::getOneFromDb(['user_id' => $uB->user_id, 'eq_group_id' => $eqgI->eq_group_id], $this->DB);
			$this->assertEqual(count($cp5), 1);
		}

	}