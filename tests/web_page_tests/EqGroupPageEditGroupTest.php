<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

	class EqGroupPageEditGroupTest extends WMSWebTestCase {

		function setUp() {
			createTestData_Users($this->DB);
			createTestData_EqGroups($this->DB);
			createTestData_InstGroups($this->DB);
			createTestData_InstMemberships($this->DB);
			createTestData_Permissions($this->DB);
		}

		function tearDown() {
			removeTestData_Users($this->DB);
			removeTestData_EqGroups($this->DB);
			removeTestData_InstGroups($this->DB);
			removeTestData_InstMemberships($this->DB);
			removeTestData_Permissions($this->DB);
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

		function TestLacksQuerystringGroupIDValue() {
			$this->_loginAdmin();
			$this->assertResponse(200);

			$this->get('http://localhost/eqreserve/equipment_group.php');
			$this->assertPattern('/Equipment Groups/', 'Indicates redirect to home page.');
		}


		function TestBasicPageElementsManager() {
			$this->_loginAdmin();
			$this->assertResponse(200);

			$this->get('http://localhost/eqreserve/equipment_group.php?eid=201');

			//			$this->dump($this->getBrowser()->getContent());

			$this->assertText("Equipment Group");
			$this->assertText("Description");
			$this->assertText("Managed by");
			$this->assertText("Reservation Rules");

			$this->assertText("testEqGroup1");
			$this->assertText("on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals");
			$this->assertText("0,15,30,45 minutes");
			$this->assertText("1 hours");
			$this->assertText("15 minutes");

			$this->assertText("Reserve Equipment");
			$this->assertText("Add an Item");
			$this->assertText("Add a Subgroup");
			$this->assertText("View Reservations as List");
			$this->assertText("Delete this Equipment Group");

//			exit;
		}

		function TestBasicPageElementsUser() {
			$this->_loginUser();
			$this->assertResponse(200);

			$this->get('http://localhost/eqreserve/equipment_group.php?eid=201');

			$this->assertText("Equipment Group");
			$this->assertText("Description");
			$this->assertText("Managed by");
			$this->assertText("Reservation Rules");

			$this->assertText("testEqGroup1");
			$this->assertText("on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals");
			$this->assertText("0,15,30,45 minutes");
			$this->assertText("1 hours");
			$this->assertText("15 minutes");

			$this->assertText("Reserve Equipment");
			$this->assertText("Add an Item");
			$this->assertText("Add a Subgroup");
			$this->assertText("View Reservations as List");
			$this->assertText("Delete this Equipment Group");
		}

		function TestAdminAccessToGroup() {
			$this->_loginAdmin();
			$this->assertResponse(200);
			$this->assertText("testEqGroup8");

			$this->click('testEqGroup8');

			$this->assertText("testEqGroup8");
			$this->assertEltByIdHasAttrOfValue('groupName', 'value', 'testEqGroup8');
		}

		function TestNonAdminNoAccessToGroup() {
			$this->get('http://localhost/eqreserve/');
			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
			$this->assertResponse(200);

			$this->get('http://localhost/eqreserve/equipment_group.php?eid=208');

			$this->assertPattern("/FAILED/i");
		}

	}