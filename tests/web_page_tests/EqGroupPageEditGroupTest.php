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
			$u1                       = User::getOneFromDb(['username' => TESTINGUSER], $this->DB);
			$u1->flag_is_system_admin = TRUE;
			$u1->updateDb();

			$this->get('http://localhost/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		function TestBasicPageElements() {
			$this->_loginAdmin();
			$this->assertResponse(200);

			$this->get('http://localhost/eqreserve/equipment_group.php');

//			$this->dump($this->getBrowser()->getContent());

			$this->assertText("Equipment Group");
			$this->assertText("Managed by:");
			$this->assertText("Reservation Rules");
			$this->assertText("Reserve Some Equipment");
			$this->assertText("Add an Item");
			$this->assertText("Add a Subgroup");
			$this->assertText("View Reservations as List");
			$this->assertText("Delete this Equipment Group");

//			exit;
		}

	}
