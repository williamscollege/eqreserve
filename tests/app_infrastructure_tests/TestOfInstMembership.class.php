<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/inst_membership.class.php';


class TestOfInstMembership extends UnitTestCaseDB {
	
	
	function setUp() {
        $addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES (1,'".Auth_Base::$TEST_USERNAME."','".Auth_Base::$TEST_FNAME."','".Auth_Base::$TEST_LNAME."','".Auth_Base::$TEST_SORTNAME."','".Auth_Base::$TEST_EMAIL."','David Keiser-Clark','some important notes',0,0,0)";
		$addTestUserStmt = $this->DB->prepare($addTestUserSql);
		$addTestUserStmt->execute();

        $addTestInstGroupSql = "INSERT INTO ".InstGroup::$dbTable." VALUES (1,'".Auth_Base::$TEST_INST_GROUPS[0]."',0),(2,'".Auth_Base::$TEST_INST_GROUPS[1]."',0),(3,'".Auth_Base::$TEST_INST_GROUPS[2]."',1),(4,'".Auth_Base::$TEST_INST_GROUPS[3]."',0)";
        // normal, normal, deleted, normal
        $addTestInstGroupStmt = $this->DB->prepare($addTestInstGroupSql);
        $addTestInstGroupStmt->execute();

        $linkUserToInstGroupSql = "INSERT INTO ".InstMembership::$dbTable."  VALUES (1,1,1,0),(2,1,2,1),(3,1,3,0)"; // normal, link deleted, group deleted
        $linkUserToInstGroupStmt = $this->DB->prepare($linkUserToInstGroupSql);
        $linkUserToInstGroupStmt->execute();
	}
	
	function tearDown() {
        $rmTestUserSql = "DELETE FROM ".User::$dbTable;
        $rmTestUserStmt = $this->DB->prepare($rmTestUserSql);
        $rmTestUserStmt->execute();

        $rmTestInstGroupSql = "DELETE FROM ".InstGroup::$dbTable;
        $rmTestInstGroupStmt = $this->DB->prepare($rmTestInstGroupSql);
        $rmTestInstGroupStmt->execute();

        $rmLinkUserInstGroupSql = "DELETE FROM ".InstMembership::$dbTable;
        $rmLinkUserInstGroupStmt = $this->DB->prepare($rmLinkUserInstGroupSql);
        $rmLinkUserInstGroupStmt->execute();
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