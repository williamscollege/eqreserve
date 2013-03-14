<?php
	require_once dirname(__FILE__) . '/../../classes/user.class.php';
	require_once dirname(__FILE__) . '/../../classes/permission.class.php';
	require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';


class IndexPageDBTest extends WebTestCaseWMS {

	function setUp() {
		$rmTestUserSql = "DELETE FROM ".User::$dbTable;
		$rmTestUserStmt = $this->DB->prepare($rmTestUserSql);
		$rmTestUserStmt->execute();

		$rmTestInstGroupSql = "DELETE FROM ".InstGroup::$dbTable;
		$rmTestInstGroupStmt = $this->DB->prepare($rmTestInstGroupSql);
		$rmTestInstGroupStmt->execute();

		$rmLinkUserInstGroupSql = "DELETE FROM ".InstMembership::$dbTable;
		$rmLinkUserInstGroupStmt = $this->DB->prepare($rmLinkUserInstGroupSql);
		$rmLinkUserInstGroupStmt->execute();

		$rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
		$rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
		$rmTestEqGroupsStmt->execute();

		$rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
		$rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
		$rmTestPermissionStmt->execute();
	}

    function testInstGroupMembershipCreatedOnLogIn() {
        $this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);


        $this->click('Sign in');

//		$this->dump($this->getBrowser()->getContent());

		$this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');

		$u = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
		$this->assertTrue($u->matchesDb);

        $test = InstMembership::getAllFromDb(['user_id'=>$u->user_id],$this->DB);
		$this->assertEqual(count(Auth_Base::$TEST_INST_GROUPS),count($test));
		//exit;
    }

}
