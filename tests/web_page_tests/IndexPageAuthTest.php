<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageAuthTest extends WMSWebTestCase {

	function tearDown() {
		removeTestData_Users($this->DB);
		removeTestData_EqGroups($this->DB);
		removeTestData_InstGroups($this->DB);
		removeTestData_InstMemberships($this->DB);
		removeTestData_Permissions($this->DB);
	}

	function testIndexNotLoggedIn() {
		$this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
		$this->assertField('username'); //$value
		$this->assertField('password'); //$value
	}

    function testIndexLoggingIn() {
        $this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        
        $this->click('Sign in');

        $this->assertFalse($this->setField('username','foo')); //$value
        $this->assertFalse($this->setField('password','bar')); //$value
        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
        $this->assertNoPattern('/Sign in failed/i');

        $this->assertEltByIdHasAttrOfValue('submit_signout','value',new PatternExpectation('/Sign\s?out/i'));
    }

    function testIndexFailLoggingIn() {
        $this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER.'foo');
        $this->setField('password', TESTINGPASSWORD.'foo');
        
        $this->click('Sign in');

        $this->assertPattern('/Sign in failed/i');
    }

    function testIndexLoggingOut() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
        $this->assertEltByIdHasAttrOfValue('submit_signout','value',new PatternExpectation('/Sign\s?out/i'));

        $this->click('Sign out');

        $this->assertField('username'); //$value
        $this->assertField('password'); //$value
    }

/*    
    function testGetEQGroups() {
    	$this->get('http://localhost/eqreserve/');
    	$this->setField('username', TESTINGUSER);
    	$this->setField('password', TESTINGPASSWORD);
    	$this->click('Sign in');
		$this->assertPattern('/Equipment Groups:/');
		$this->assertPattern('/<li>Science: Spectometers \[description:\]<\/li>/');
    }
*/
}
