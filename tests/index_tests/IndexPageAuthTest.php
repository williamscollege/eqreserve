<?php

class IndexPageAuthTest extends WebTestCase {

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
        $this->assertPattern('/You are logged in as/');
    }

}
