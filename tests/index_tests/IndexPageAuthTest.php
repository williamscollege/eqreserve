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
        
        $this->click('log In');

        $this->assertFalse($this->setField('username','foo')); //$value
        $this->assertFalse($this->setField('password','bar')); //$value
        $this->assertPattern('/You are logged in as \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
        $this->assertNoPattern('/log in failed/i');

        $this->assertEltByIdHasAttrOfValue('logout_btn','value',new PatternExpectation('/log\s?out/i'));
    }

    function testIndexFailLoggingIn() {
        $this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER.'foo');
        $this->setField('password', TESTINGPASSWORD.'foo');
        
        $this->click('log In');

        $this->assertPattern('/log in failed/i');
    }

    function testIndexLoggingOut() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('log In');
        $this->assertPattern('/You are logged in as \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
        $this->assertEltByIdHasAttrOfValue('logout_btn','value',new PatternExpectation('/log\s?out/i'));

        $this->click('log out');

        $this->assertField('username'); //$value
        $this->assertField('password'); //$value
    }
    
    function testGetEQGroups() {
    	$this->get('http://localhost/eqreserve/');
    	$this->setField('username', TESTINGUSER);
    	$this->setField('password', TESTINGPASSWORD);
    	$this->click('log In');
		$this->assertPattern('/List of your equipment groups:/');
		$this->assertPattern('/<li>Science: Spectometers \[role: 2\]<\/li>/');
    }
}
