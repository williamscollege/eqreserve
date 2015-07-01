<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class InstGroupTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    //############################################################

	function getToInstGroupPage() {
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/inst_group.php?inst_group=501');
	}

    function testAccessInstGroup() {
        $this->getToInstGroupPage();

        $this->assertResponse(200);
        $this->assertText('testInstGroup1');
        $this->assertNoPattern('/FAILED/i');
    }

    function testBlockAccessInstGroup() {
        $this->getToInstGroupPage();
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/inst_group.php?inst_group=502');

        $this->assertResponse(200);
        $this->assertNoText('testInstGroup1');
        $this->assertPattern('/FAILED/i');
    }

}