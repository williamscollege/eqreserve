<?php

require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

class TestOfAuth_Base extends UnitTestCase {

    function testClassExists() {
        // no real test - the require at the top of the file is enough
        $this->assertTrue(1==1);
    }

    function testAuthenticateTestUser() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_Base();        
        $this->assertTrue($AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD));
    }

    function testAuthenticateNontestUserFails() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_Base();        
        $this->assertFalse($AUTH->authenticate(TESTINGUSER.'foo',TESTINGPASSWORD));
    }

    function testAuthenticateTestUserBaddPasswordFails() {
        // no real test - the require at the top of the file is enough
        $AUTH = new Auth_Base();        
        $this->assertFalse($AUTH->authenticate(TESTINGUSER,TESTINGPASSWORD.'foo'));
    }

}
?>