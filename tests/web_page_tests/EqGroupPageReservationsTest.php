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

			$this->get('http://localhost/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		public function _loginUser() {
			# user is a regular user (not admin!)
			$this->get('http://localhost/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		function TestOfDataExpectedForReservations() {
			$this->_loginUser();
			$this->assertResponse(200);

			$this->get('http://localhost/eqreserve/equipment_group.php?eid=201');
			$this->assertResponse(200);

			$this->assertText("testEqGroup1");

			$this->assertText("testSubgroup1");
			$this->assertText("testSubgroup2");
			$this->assertText("testSubgroup3");
			$this->assertText("testSubgroup4");

			$this->assertText("testItem1");
			$this->assertText("testItem2");
			$this->assertText("testItem3");
			$this->assertText("testItem4");
			$this->assertText("testItem1");

			$this->assertText("same priority as prev");
			$this->assertText("same name, different subgroup");

		}

	}