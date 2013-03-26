<?php
	#require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

	#Remove these later... included only for quicker testing
	require_once('../simpletest/autorun.php');
	require_once('../simpletest/WMS_web_tester.php');

	SimpleTest::prefer(new TextReporter());

	require_once('../../institution.cfg.php');
	require_once('../../lang.cfg.php');

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

		public function loginAdmin() {
			# update test user to have system admin role
			$u1                       = User::getOneFromDb(['username' => TESTINGUSER], $this->DB);
			$u1->flag_is_system_admin = TRUE;
			$u1->updateDb();

			$this->get('http://localhost/eqreserve/');
			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');

			$this->assertResponse(200);

			$this->assertLink('Admin Only');
			$this->assertText('Equipment Groups');
			$this->assertText('testEqGroup1');
			$this->assertText('testEqGroup2');
			//			exit;
		}

		function TestBasicPageElements() {
			self::loginAdmin();
			$this->assertText("Reservation Rules");
			$this->assertText("View Reservations as List");
			$this->assertText("Delete this Equipment Group");

		}

	}
