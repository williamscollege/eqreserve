<?php

class IndexPageAuthTest extends WebTestCase {

	function testIndexNotLoggedIn() {
		$this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
		$this->assertField('username'); //$value
		$this->assertField('password'); //$value
	}

    function testIndexLoggedIn() {
        $this->get('http://localhost/eqreserve/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', 'Me');
        $this->setField('password', 'Secret');
        $this->click('Log in');        $this->assertFalse($this->assertField('username')); //$value
        $this->assertFalse($this->assertField('password')); //$value
    }

}
