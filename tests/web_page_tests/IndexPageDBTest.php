<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageDBTest extends WMSWebTestCase {

	function setUp() {
		removeTestData_Users($this->DB);
		removeTestData_EqGroups($this->DB);
		removeTestData_InstGroups($this->DB);
		removeTestData_InstMemberships($this->DB);
		removeTestData_Permissions($this->DB);
	}

	function tearDown() {
		removeTestData_Users($this->DB);
		removeTestData_EqGroups($this->DB);
		removeTestData_InstGroups($this->DB);
		removeTestData_InstMemberships($this->DB);
		removeTestData_Permissions($this->DB);
	}

    function testInstGroupMembershipCreatedOnLogIn() {
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');
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