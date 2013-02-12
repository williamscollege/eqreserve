<?php

class IndexPageLoadTest extends WebTestCase {
    function testIndexPageLoad() {
        $this->get('http://localhost/eqreserve/');
        $this->assertResponse(200);
    }

    function testIndexPageLoadsErrorAndWarningFree() {
        $this->get('http://localhost/eqreserve/');
        $this->assertNoPattern('/error/i');
        $this->assertNoPattern('/warning/i');
        
    }

	function testIndexLoginForm() {
		$this->get('http://localhost/eqreserve/');
		$this->assertField('username'); //$value
		$this->assertField('password'); //$value
	}

}
