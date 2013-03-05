<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/role.class.php';


	class TestOfRole extends UnitTestCaseDB
	{

		function setUp() {
		}

		function tearDown() {
		}

		public function TestOfCmpRolesByPriority(){
			$r1 = new Role(['priority'=>1, 'DB'=>$this->DB]);
			$r2 = new Role(['priority'=>2, 'DB'=>$this->DB]);

			$cmp = Role::cmpRoles($r1,$r2);
			$this->assertNotNull($cmp);
			$this->assertEqual($cmp, 1);

			$cmp = Role::cmpRoles($r2,$r1);
			$this->assertEqual($cmp, -1);
		}

		public function TestOfRolePriority(){
			$r1 = new Role(['role_id'=>1, 'priority'=> 1, 'DB'=>$this->DB]);
			$r2 = new Role(['role_id'=>2, 'priority'=> 2, 'DB'=>$this->DB]);
			$r3 = new Role(['role_id'=>3, 'priority'=> 3, 'DB'=>$this->DB]);
			$r4 = new Role(['role_id'=>4, 'priority'=> 4, 'DB'=>$this->DB]);

			$this->assertEqual($r1->role_id, 1);
			$this->assertEqual($r1->priority, 1);
			$this->assertEqual($r2->role_id, 2);
			$this->assertEqual($r2->priority, 2);
			$this->assertEqual($r3->role_id, 3);
			$this->assertEqual($r3->priority, 3);
			$this->assertEqual($r4->role_id, 4);
			$this->assertEqual($r4->priority, 4);
		}

	}

?>