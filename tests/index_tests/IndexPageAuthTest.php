<?php

class IndexPageAuthTest extends WebTestCaseWMS {

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
        
        $this->click('Log In');

        $this->assertFalse($this->setField('username','foo')); //$value
        $this->assertFalse($this->setField('password','bar')); //$value
        $this->assertPattern('/You are logged in as '.TESTINGUSER.'/');

//        $this->assertEltByIdHasAttrOfValue('logout_btn','value','log out');
        $this->assertEltByIdHasAttrOfValue('logout_btn','value',new PatternExpectation('/log\s?out/i'));
    }

}
