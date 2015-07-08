<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

	class EqGroupPageReservationsTest extends WMSWebTestCase {

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		//############################################################

		public function _loginAdmin() {
			# update test user to have system admin role
			makeAuthedTestUserAdmin($this->DB);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		public function _loginUser() {
			# user is a regular user (not admin!)
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

        function TestOfReservationRulesDisplayed(){
            $this->_loginUser();
            $this->assertResponse(200);

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
            $this->assertResponse(200);

            $this->assertText('Reservation Time Restrictions');
            $this->assertText('Can be reserved for 15 minutes min., 60 minutes max., starting on the 0,15,30,45 hour for 15 minute intervals');
        }

		function TestOfDataExpectedForReservations() {
			$this->_loginUser();
			$this->assertResponse(200);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
			$this->assertResponse(200);

//			$this->assertText("testEqGroup1");
//
//			$this->assertText("testSubgroup1");
//			$this->assertText("testSubgroup2");
//			$this->assertText("testSubgroup3");
//			$this->assertText("testSubgroup4");
//
//			$this->assertText("testItem1");
//			$this->assertText("testItem2");
//			$this->assertText("testItem3");
//			$this->assertText("testItem4");
//			$this->assertText("testItem1");
//
//			$this->assertText("same priority as prev");
//			$this->assertText("same name, different subgroup");

            $this->assertText('Existing Reservations');
            $this->assertText('2013/3/22 10:00-10:15 AM by you');
            $this->assertText('[2013/3/26 10:00-10:30 AM], [2013/4/2 10:00-10:30 AM], [2013/4/9 10:00-10:30 AM] by you');
            $this->assertText('(MANAGEMENT) 2013/3/25 6:00-7:00 PM by you');
            $this->assertText('2013/3/26 6:00-7:00 PM by user removed from system: tu3F tu3L');

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=202');
            $this->assertResponse(200);

            $this->assertText('Existing Reservations');
            $this->assertText('(MANAGEMENT) 2013/3/25 6:00-7:00 PM by tu2F tu2L');
            $this->assertText('testUser2');
            $this->assertText('tu2@inst.edu');
            $this->assertText('Advised by: tu2Advisor');
            $this->assertText('Member of: testInstGroup2');
            $this->assertText('tu2 notes');
		}

        // NOTE: can't figure out how to check that a given text is hidden / not visible
    }