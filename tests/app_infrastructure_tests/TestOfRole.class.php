<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/role.class.php';


	class TestOfRole extends UnitTestCaseDB
	{

		function setUp() {
			# Role: role_id, name, descr
			$addTestRolesSql  = "INSERT INTO " . Role::$dbTable . " VALUES (1,'admin','system admins'),
																			(2,'manager','midlevels'),
																			(3,'consumer','peons')
																			";
			$addTestRolesStmt = $this->DB->prepare($addTestRolesSql);
			$addTestRolesStmt->execute();
		}

		function tearDown() {
			$rmTestRolesSql = "DELETE FROM ".Role::$dbTable;
			$rmTestRolesStmt = $this->DB->prepare($rmTestRolesSql);
			$rmTestRolesStmt->execute();
		}

		public function TestOfCmpRolesByID(){
			#$this->fail();
			$r1 = new Role(['role_id'=>1, 'DB'=>$this->DB]);
			$r2 = new Role(['role_id'=>2, 'DB'=>$this->DB]);

			$cmp = Role::cmpRolesByID($r1,$r2);

			$this->assertNotNull($cmp);

			# this should result in -1
			$this->assertEqual($cmp, -1);
			$this->assertNotEqual($cmp, 0);
			$this->assertNotEqual($cmp, 1);

			$cmp = Role::cmpRolesByID($r2,$r1);

			# this should result in 1
			$this->assertEqual($cmp, 1);
			$this->assertNotEqual($cmp, 0);
			$this->assertNotEqual($cmp, -1);
		}

	}

?>