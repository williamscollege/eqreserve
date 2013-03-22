<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

class TestOfInstMembership extends WMSUnitTestCaseDB {
	
	
	function setUp() {
        createTestData_InstGroups($this->DB);
        createTestData_Users($this->DB);
        createTestData_InstMemberships($this->DB);
	}
	
	function tearDown() {
        removeTestData_InstGroups($this->DB);
        removeTestData_Users($this->DB);
        removeTestData_InstMemberships($this->DB);
	}

    /////////////////////////////////////////////////////
    /////////////////////////////////////////////////////

    // class structure tests

	function testInstMembershipDBInsert(){
		$test = new InstMembership(['inst_membership_id'=>50,'inst_group_id'=>50,'user_id'=>50,'DB'=>$this->DB]);


		$test->updateDb();


		$test2 = InstMembership::getOneFromDb(['inst_membership_id'=>50], $this->DB);

		$this->assertTrue($test2->matchesDb);
	}

    // DB interaction tests - static method tests


}
?>