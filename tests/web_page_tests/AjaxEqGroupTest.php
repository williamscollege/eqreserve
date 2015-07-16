<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

    class AjaxEqGroupTest extends WMSWebTestCase {

        function setUp() {
            createAllTestData($this->DB);
        }

        function tearDown() {
            removeAllTestData($this->DB);
        }

        //############################################################

        function signIn() {
            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');
            $this->setField('username', TESTINGUSER);
            $this->setField('password', TESTINGPASSWORD);
            $this->click('Sign in');
        }

		//############################################################

		// TODO: create tests for base eq group actions (create, delete, update)

		//----------------------------------------------------------
		// basic access checking

		function testEqGroupAjaxNoAccessWhenNotLoggedIn() {
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=null');

			$this->assertPattern('/not authenticated/i');
		}

		function testEqGroupAjaxInvalidAction() {
			$this->signIn();
			$base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$this->assertTrue($base_eg->matchesDb);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?ajaxVal_GroupID=201&ajaxVal_Action=blah');

			$this->assertPattern('/"status":"failure"/');
		}

		function testEqGroupAjaxManagerGetsAccess() {
			$this->signIn();

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=null');

			$this->assertPattern('/"status":"success"/');
			$eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$this->assertTrue($eg->matchesDb);
		}

		function testEqGroupAjaxNonManagerGetsNoAccess() {
			$this->signIn();

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=202&ajaxVal_Action=null');

			$this->assertPattern('/"status":"failure"/');
			$eg = EqGroup::getOneFromDb(['eq_group_id' => 202], $this->DB);
			$this->assertTrue($eg->matchesDb);
		}

		function testEqGroupAjaxSystemAdminGetsAccess() {
			$this->signIn();
			makeAuthedTestUserAdmin($this->DB);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=202&ajaxVal_Action=null');

			$this->assertPattern('/"status":"success"/');
			$eg = EqGroup::getOneFromDb(['eq_group_id' => 202], $this->DB);
			$this->assertTrue($eg->matchesDb);
		}

		//----------------------------------------------------------
		// remove permissions

		function testEqGroupAjaxRemoveConsumerAccess() {
			$this->signIn();

			// other user access
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=removePermission&permission_ids[]=718');
			$this->assertPattern('/"status":"success"/');
			$p = Permission::getOneFromDb(['permission_id' => 718], $this->DB);
			$this->assertFalse($p->matchesDb);

			// self, but have manager access so OK to remove consumer access
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=removePermission&permission_ids[]=702');
			$this->assertPattern('/"status":"success"/');
			$p = Permission::getOneFromDb(['permission_id' => 702], $this->DB);
			$this->assertFalse($p->matchesDb);

			// inst group access
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=removePermission&permission_ids[]=711');
			$this->assertPattern('/"status":"success"/');
			$p = Permission::getOneFromDb(['permission_id' => 711], $this->DB);
			$this->assertFalse($p->matchesDb);
		}

		function testEqGroupAjaxRemoveMultipleAccess() {
			$this->signIn();

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=removePermission&permission_ids[]=719&permission_ids[]=718&permission_ids[]=702&permission_ids[]=711');

			$this->assertPattern('/"status":"success"/');
			$p = Permission::getOneFromDb(['permission_id' => 719], $this->DB);
			$this->assertFalse($p->matchesDb);
			$p = Permission::getOneFromDb(['permission_id' => 718], $this->DB);
			$this->assertFalse($p->matchesDb);
			$p = Permission::getOneFromDb(['permission_id' => 702], $this->DB);
			$this->assertFalse($p->matchesDb);
			$p = Permission::getOneFromDb(['permission_id' => 711], $this->DB);
			$this->assertFalse($p->matchesDb);
		}

		function testEqGroupAjaxRemoveManagerAccess() {
			$this->signIn();

			// other user access
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=removePermission&permission_ids[]=719');
			$this->assertPattern('/"status":"success"/');
			$p = Permission::getOneFromDb(['permission_id' => 719], $this->DB);
			$this->assertFalse($p->matchesDb);
		}

		function testEqGroupAjaxNonAdminCannotRemoveTheirOwnManagerAccess() {
			$this->signIn();
			$base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$this->assertTrue($base_eg->matchesDb);

			// direct
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=203&ajaxVal_Action=removePermission&permission_ids[]=703');
			$this->assertPattern('/"status":"failure"/');
			$p = Permission::getOneFromDb(['permission_id' => 703], $this->DB);
			$this->assertTrue($p->matchesDb);

			// indirect, via inst group
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=removePermission&permission_ids[]=707');
			$this->assertPattern('/"status":"failure"/');
			$p = Permission::getOneFromDb(['permission_id' => 707], $this->DB);
			$this->assertTrue($p->matchesDb);
		}

		//----------------------------------------------------------
		// add permissions

		function testEqGroupAjaxAddManagerPermissionForUser() {
			$this->signIn();
			$base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$base_eg->loadManagers();
			$this->assertEqual(count($base_eg->managers), 2);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=addPermission&permission_type=manager&entity_type=user&entity_id=1101&username=cswtestinguser');

			$this->assertPattern('/"status":"success"/');
			//        $this->assertPattern('/"permission_id":""/');
			$this->assertPattern('/"entity_type":"user"/');
			$this->assertPattern('/"entity_id":"1101"/');
			$this->assertPattern('/"name":"Violet Bovine"/');
			$this->assertPattern('/"username":"' . Auth_Base::$TEST_USERNAME . '"/');
			$this->assertPattern('/"email":"' . Auth_Base::$TEST_EMAIL . '"/');
			$base_eg->permissions = [];
			$base_eg->loadManagers();
			$this->assertEqual(count($base_eg->managers), 3);
		}


		function testEqGroupAjaxAddManagerPermissionForInstGroup() {
			$this->signIn();
			$base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$base_eg->loadManagers();
			$this->assertEqual(count($base_eg->managers), 2);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=addPermission&permission_type=manager&entity_type=inst_group&entity_id=503&username=testInstGroup3');

			$this->assertPattern('/"status":"success"/');
			//        $this->assertPattern('/"permission_id":""/');
			$this->assertPattern('/"entity_type":"inst_group"/');
			$this->assertPattern('/"entity_id":"503"/');
			$this->assertPattern('/"name":"testInstGroup3"/');
			$base_eg->permissions = [];
			$base_eg->loadManagers();
			$this->assertEqual(count($base_eg->managers), 3);
		}


		function testEqGroupAjaxAddConsumerPermissionForUser() {
			$this->signIn();
			$base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$base_eg->loadConsumers();
			$this->assertEqual(count($base_eg->consumers), 3);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=addPermission&permission_type=consumer&entity_type=user&entity_id=1106&username=testUser6');

			$this->assertPattern('/"status":"success"/');
			//        $this->assertPattern('/"permission_id":""/');
			$this->assertPattern('/"entity_type":"user"/');
			$this->assertPattern('/"entity_id":"1106"/');
			$this->assertPattern('/"name":"tu6F tu6L"/');
			$this->assertPattern('/"username":"testUser6"/');
			$this->assertPattern('/"email":"tu6@inst.edu"/');
			$base_eg->permissions = [];
			$base_eg->loadConsumers();
			$this->assertEqual(count($base_eg->consumers), 4);
		}

		function testCommPrefAddedWhenUserGivenPermissionForAGroup() {
			$this->signIn();

			$cp = CommPref::getAllFromDb(['eq_group_id' => 201], $this->DB);
			$this->assertEqual(count($cp), 4);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=addPermission&permission_type=consumer&entity_type=user&entity_id=1107&username=testUser7');

			$cp = CommPref::getAllFromDb(['eq_group_id' => 201], $this->DB);
			$this->assertEqual(count($cp), 5);
		}

		function testEqGroupAjaxAddConsumerPermissionForInstGroup() {
			$this->signIn();
			$base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
			$base_eg->loadConsumers();
			$this->assertEqual(count($base_eg->consumers), 3);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_eq_group.php?eq_group=201&ajaxVal_Action=addPermission&permission_type=consumer&entity_type=inst_group&entity_id=503&username=testInstGroup3');

			$this->assertPattern('/"status":"success"/');
			//        $this->assertPattern('/"permission_id":""/');
			$this->assertPattern('/"entity_type":"inst_group"/');
			$this->assertPattern('/"entity_id":"503"/');
			$this->assertPattern('/"name":"testInstGroup3"/');
			$base_eg->permissions = [];
			$base_eg->loadConsumers();
			$this->assertEqual(count($base_eg->consumers), 4);
		}
	}