<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AcctMgtTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    //############################################################

	function getToAcctMgtPage() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
        $this->clickLink(TESTINGUSER);
	}

    function testAccessAcctMgt() {
        $this->getToAcctMgtPage();
        $this->assertResponse(200);
        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');

        $this->assertText(Auth_Base::$TEST_FNAME.' '.Auth_Base::$TEST_LNAME);
        $this->assertText(Auth_Base::$TEST_EMAIL);

        $this->assertText(Auth_Base::$TEST_INST_GROUPS[0]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[1]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[2]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[3]);

        $this->assertText('testEqGroup1');
        $this->assertText('testEqGroup2');
        $this->assertText('testEqGroup3');
        $this->assertText('testEqGroup6');
        $this->assertText('testEqGroup7');
    }

}
