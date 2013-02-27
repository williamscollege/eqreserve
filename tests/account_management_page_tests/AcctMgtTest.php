<?php

class AcctMgtTest extends WebTestCaseWMS {

	function getToAcctMgtPage() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('log In');
        $this->clickLink(TESTINGUSER);
	}

    function testAccessAcctMgt() {
        $this->getToAcctMgtPage();
        $this->assertResponse(200);
        $this->assertPattern('/You are logged in as \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
    }

}
