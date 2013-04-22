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

//        $this->assertEltByIdHasAttrOfValue('accountName','value',Auth_Base::$TEST_FNAME.' '.Auth_Base::$TEST_LNAME);
//        $this->assertEltByIdHasAttrOfValue('accountUsername','value',Auth_Base::$TEST_USERNAME);
//        $this->assertEltByIdHasAttrOfValue('accountEmail','value',Auth_Base::$TEST_EMAIL);
        $this->assertText(Auth_Base::$TEST_FNAME.' '.Auth_Base::$TEST_LNAME);
        $this->assertText(Auth_Base::$TEST_USERNAME);
        $this->assertText(Auth_Base::$TEST_EMAIL);

        $this->assertText(Auth_Base::$TEST_INST_GROUPS[0]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[1]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[2]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[3]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[4]);

        $this->assertText('testEqGroup1');
        $this->assertText('testEqGroup2');
        $this->assertText('testEqGroup3');
        $this->assertText('testEqGroup6');
        $this->assertText('testEqGroup7');
    }

    function testAcctMgtCommPrefs() {
        $this->getToAcctMgtPage();
        $this->assertResponse(200);
        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');

        $this->assertEltByIdHasAttrOfValue('reminder_comm_pref_101','name','reminder_comm_pref_101');
        $this->assertEltByIdHasAttrOfValue('alert_create_comm_pref_101','name','alert_create_comm_pref_101');
        $this->assertEltByIdHasAttrOfValue('alert_cancel_comm_pref_101','name','alert_cancel_comm_pref_101');

        $this->assertEltByIdHasAttrOfValue('reminder_comm_pref_102','name','reminder_comm_pref_102');
        $this->assertEltByIdHasAttrOfValue('reminder_comm_pref_102','checked','checked');
        $this->assertNoPattern('/alert_create_comm_pref_102/');
        $this->assertNoPattern('/alert_cancel_comm_pref_102/');

//        $this->assertNoText('Equipment Groups');

        $this->assertEltByIdHasAttrOfValue('reminder_comm_pref_103','name','reminder_comm_pref_103');
        $this->assertEltByIdHasAttrOfValue('alert_create_comm_pref_103','name','alert_create_comm_pref_103');
        $this->assertEltByIdHasAttrOfValue('alert_create_comm_pref_103','checked','checked');
        $this->assertEltByIdHasAttrOfValue('alert_cancel_comm_pref_103','name','alert_cancel_comm_pref_103');

//        $this->assertEltByIdHasAttrOfValue('reminder_comm_pref_101','name','reminder_comm_pref_101')
//        $this->assertEltByIdHasAttrOfValue('alert_create_comm_pref_101','name','alert_create_comm_pref_101')
//        $this->assertEltByIdHasAttrOfValue('alert_cancel_comm_pref_101','name','alert_cancel_comm_pref_101')
        $this->assertNoPattern('/failed/i');

        $this->assertEltByIdHasAttrOfValue('reminder_comm_pref_105','name','reminder_comm_pref_105');
        $this->assertNoPattern('/alert_create_comm_pref_105/');
        $this->assertNoPattern('/alert_cancel_comm_pref_105/');
//        $this->assertNoEltByIdHasAttrOfValue('alert_cancel_comm_pref_105','checked','checked');
    }
}