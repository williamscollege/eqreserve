<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class ScheduleTest extends WMSWebTestCase {

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

    function testAccessSchedule() {
        $this->getToSchedulePage();

        $this->assertResponse(200);
        $this->assertText('2013/3/22 10:00-10:15 AM');
        $this->assertText('testItem1');
        $this->assertText('testSubgroup1');
        $this->assertText('testEqGroup1');

        $this->assertNoPattern('/FAILED/i');
    }

    function testManagerSchedule() {
        $this->signIn();
        $this->get('http://localhost/eqreserve/schedule.php?schedule=1006');

        $this->assertResponse(200);
        $this->assertNoPattern('/FAILED/i');
        $this->assertPattern('/management schedule/i');
    }

    function testBlockAccessSchedule() {
        $this->signIn();
        $this->get('http://localhost/eqreserve/schedule.php?schedule=1009');

        $this->assertResponse(200);
        $this->assertNoText('2013/3/22 3:00-3:45 PM');
        $this->assertPattern('/FAILED/i');
        $this->assertPattern('/You do not have access to that schedule/i');
    }

    function testAccessToAdjustManagerFlag() {
        $this->signIn();

        // indirect manager
        $this->get('http://localhost/eqreserve/schedule.php?schedule=1006');
        $this->assertNoPattern('/FAILED/i');
        $this->assertPattern('/id=\"sched-is-manager-btn\"/i');

        // not a manager
        $this->get('http://localhost/eqreserve/schedule.php?schedule=1010');
        $this->assertNoPattern('/FAILED/i');
        $this->assertNoPattern('/id=\"sched-is-manager-btn\"/i');
    }
}