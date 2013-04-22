<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxScheduleTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    //############################################################

    function signIn() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
    }

	function getToSchedulePage() {
        $this->signIn();
        $this->get('http://localhost/eqreserve/schedule.php?schedule=1001');
	}

    function testScheduleAjaxSave() {
        $this->getToSchedulePage();
        $this->fail();
    }

    function testScheduleAjaxDeleteAll() {
        $this->getToSchedulePage();
        $this->fail();
    }

    function testScheduleAjaxDeleteItem() {
        $this->getToSchedulePage();
        $this->fail();
    }

    function testScheduleAjaxDeleteLastItem() {
        $this->getToSchedulePage();
        $this->fail();
    }
}