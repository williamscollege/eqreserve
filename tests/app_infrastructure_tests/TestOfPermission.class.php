<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/permission.class.php';


	class TestOfPermission extends UnitTestCaseDB
	{

		function setUp() {
		}

		function tearDown() {
		}


		function testPermissionDBInsert(){
			$test = new Permission(['permission_id'=>50,'entity_id'=>50,'entity_type'=>'user','role_id'=>50,'eq_group_id'=>50,'DB'=>$this->DB]);


			$test->updateDb();


			$test2 = Permission::getOneFromDb(['permission_id'=>50], $this->DB);

			$this->assertTrue($test2->matchesDb);
		}

	}

?>